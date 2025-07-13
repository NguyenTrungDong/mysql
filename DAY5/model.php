<?php 
require_once 'db.php';
// function learnProcedure($pdo)
// {
//     $sql = "CREATE PROCEDURE IncreaseSalary 
//     (IN dept VARCHAR(50), IN increase_amount DECIMAL(10,2))
//     BEGIN
//         UPDATE employees
//         SET salary = salary + increase_amount
//         WHERE department = dept1
//     END
//     CALL IncreaseSalary('IT', 1000);
//     ";
//     $pdo->exec($sql);
// }
// function learnTrigger($pdo) //Trigger là tập hợp các câu lệnh sql tự động được thực thi (không thể gọi trực tiếp), khi một hành động cụ thể xảy ra (CRUD bảng nào đó, INSERT, UPDATE, DELETE)
// {   
//     $sql_1 = "CREATE TABLE employee_log (
//     log_id INT AUTO_INCREAMENT PRIMARY KEY,
//     employee_name VARCHAR(100),
//     action_time TIMESTAMP";
//     $sql = "CREATE TRIGGER after_employee_insert
//     AFTER INSERT ON employees
//     FOR EACH ROW 
//     BEGIN 
//     INSERT INTO employee_log (employee_name,action_time)
//     VALUES (NEW.name, NOW());
//     END
//     ";
//     $pdo->exec($sql);
// }

function createRoomsTable($pdo)
{
    $sql = "CREATE TABLE Rooms( 
    room_id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(10) UNIQUE,
    type VARCHAR(20) CHECK ( type IN('Standard','VIP','Suite')),
    status VARCHAR(20) CHECK ( status IN('Available', 'Occupied', 'Maintainance'),
    price INT CHECK( price >= 0)
    )";
    $pdo->exec($sql);
}
function createGuestsTable($pdo)
{
    $sql = "CREATE TABLE Guests(
    guest_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100),
    phone VARCHAR(20)
    )";
    $pdo->exec($sql);
}

function createBookingsTable($pdo)
{
    $sql = "CREATE TABLE Bookings(
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    guest_id INT,
    room_id INT,
    check_in DATE,
    check_out DATE,
    status VARCHAR(20) CHECK ( status IN('Pending','Confirmed','Cancelled')),
    FOREIGN KEY (guest_id) REFERENCES Guests(guest_id),
    FOREIGN KEY (room_id) REFERENCES Rooms(room_id)
    )";
}
function createMakeBookingProcedure($pdo)
{
    //  Procedure hay thủ tục trong sql có thể coi như là 1 hàm trong php,
    //  nó giúp tái sử dụng các câu lệnh sql đã khai báo trong procedure
    //  cách để gọi 1 procedure : CALL Procedure_name(parameter_1, parameter_2, v.v) // parameter : tham số chuyền vào procedure 
    //  để thực hiện kiểm tra các thủ tục hay procedure
    $sql = "CREATE PROCEDURE MakeBooking
    (IN p_guest_id INT,
    IN p_room_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE
    )
    BEGIN 
    DECLARE room_status INT;
    DECLARE booking_conflict INT;
    -- Đầu tiên là đi kiểm tra trạng thái (status) của bảng Rooms hay là phòng xem còn trống hay không
    -- Rồi sau đó lưu nó vào 1 cái biến của procedure để thực hiện các thủ tục sau này vì procedure giống như 1 hàm
    -- nên không thể sử dụng trực tiếp tên cột trong bảng để gán giá trị, nếu không giá trị sẽ bị biến thiên
    SELECT status INTO room_status FROM Rooms 
    WHERE room_id = p_room_id;
    IF room_status IS NULL THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Room do not exists'
    ELSEIF room_status != 'Available' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Room is not available';
    SELECT COUNT(*) INTO booking_conflict FROM Bookings WHERE p_booking_id = booking_id 
    AND status = 'Confirmed' -- Lấy số lượng bản ghi booking có status là đã confirmed
    AND (
    (p_check_in BETWEEN check_in AND check_out) OR -- thời gian  nhận phòng booking mới bị trong thời gian booking cũ
    (p_check_out BETWEEN check_in AND check_out) OR -- thời gian trả phòng booking  mới bị nằm trong thời gian booking cũ
    (check_in BETWEEN p_check_in AND p_check_out) OR -- thời gian nhận phòng booking cũ bị nằm trong thời gian booking mới
    (check_out BEETWEEN p_check_in AND p_check_out)  -- thời gian trả phòng booking cũ bị nằm trong thời gian booking mới
    );
    IF booking_conflict > 0 THEN -- số lượng > 0 tức bị trùng trong điều kiện 
    SIGNAL SQLSTATE '45000' -- mã 45000 là mã để người dùng tự thêm đoạn text thông báo lỗi
    SET 'MESSAGE_TEXT' = 'Room is already in use'; -- hiển thị lỗi
    END IF
    INSERT INTO Bookings(guest_id,room_id,check_in,check_out,status) -- thêm bản ghi booking mới nếu như không xảy ra lỗi hay bị vào điều kiện của if
    VALUES (p_guest_id,p_room_id,p_check_in,p_check_out,'Confirmed');

    UPDATE Rooms -- update trạngt thái của phòng sang occupied : đang được sử dụng
    SET status = 'Occupied' 
    WHERE room_id = p_room_id;

    END
    ";
    $pdo->exec($sql);
}



function createAfterBookingTrigger($pdo)
{
    $sql = "CREATE TRIGGER after_booking_cancel -- Tạo TRIGGER
    AFTER UPDATE ON Bookings  -- Điều kiện sau khi update bảng Bookings
    FOR EACH ROW -- FOR EACH ROW để thực hiện kiểm tra ở mọi hàng
    BEGIN -- bắt đầu truy vấn
    DECLARE active_bookings INT; -- khai báo biến để gán số lượng bản ghi có status là 'Confirmed'
IF NEW.status = 'Cancelled' AND OlD.status != 'Cancelled' THEN -- Nếu như update sang trạng thái 'Cancelled' và trạng thái cũ khác 'cancelled' để tránh thực hiện trigger với các bản ghi cũ không cần thiết'
    SELECT COUNT(*) INTO active_bookings FROM Bookings -- gán số lượng cho biến active_bookings
    WHERE room_id = NEW.room_id
    AND status = 'Confirmed'
    AND check_in >= CURDATE(); -- kiểm tra các bản ghi trong tương lai
    IF active_bookings = 0 THEN -- nếu như không có phòng nào đang sử dụng thì đổi trạng thái phòng sang 'Available'
    UPDATE Rooms 
    SET status = 'Available'
    WHERE room_id = NEW.room_id;
    END IF;
END IF;
    END ";
$pdo->exec($sql);
}

function createInvoicesTable($pdo)
{
    $sql = "CREATE TABLE Invoices
    (
        invoice_id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT,
        total_amount INT,
        generated_date DATE CURENT_TIMESTAMP,

        FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id)

    )";
    $pdo->exec($sql);
}
function createGenerateInvoiceProcedure($pdo)
{
    $sql = "CREATE PROCEDURE GenerateInvoice
    ( IN p_booking_id INT)
    DECLARE v_check_out DATE;
    DECLARE v_check_in DATE;
    DECLARE v_room_id INT;
    DECLARE v_room_price DECIMAL(10,2);
    DECLARE v_nights DATE;
    DECLARE v_total DECIMAL(10,2);

    SELECT b.check_out, b.check_in, b.booking_id, r.room_id
    INTO v_check_in, v_check_out, v
    FROM Bookings b JOIN Rooms r ON b.room_id = r.room_id
    WHERE b.booking_id = p_booking_id;

    -- Kiểm tra booking_id hợp lệ
    IF v_check_in IS NULL THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Id phòng không hợp lệ'
    -- Tính số đêm
    SET v_nights = DATEDIFF(v_check_out, v_check_in);
    -- Tính tổng tiền
    SET v_total = v_nights * v_room_price;

    -- Thêm bản ghi vào Invoices 
    INSERT INTO Invoices (booking_id, total_amount, generated_date)
    VALUES (p_booking_id, v_total_amount, CURDATE());
    END
    ";
    $pdo->exec($sql);
}
// function createGenerateInvoiceProcedure($pdo)
// {
//     $sql = "CREATE PROCEDURE GenerateInvoice (
//     IN p_booking_id INT
// )
// BEGIN
//     DECLARE v_check_in DATE;
//     DECLARE v_check_out DATE;
//     DECLARE v_room_price INT;
//     DECLARE v_nights INT;
//     DECLARE v_total_amount INT;

//     -- Lấy thông tin từ Bookings và Rooms
//     SELECT b.check_in, b.check_out, r.price
//     INTO v_check_in, v_check_out, v_room_price
//     FROM Bookings b
//     JOIN Rooms r ON b.room_id = r.room_id
//     WHERE b.booking_id = p_booking_id;

//     -- Kiểm tra booking_id hợp lệ
//     IF v_check_in IS NULL THEN
//         SIGNAL SQLSTATE '45000'
//         SET MESSAGE_TEXT = 'Invalid booking ID';
//     END IF;

//     -- Tính số đêm
//     SET v_nights = DATEDIFF(v_check_out, v_check_in);

//     -- Tính tổng tiền
//     SET v_total_amount = v_nights * v_room_price;

//     -- Thêm bản ghi vào Invoices
//     INSERT INTO Invoices (booking_id, total_amount, generated_date)
//     VALUES (p_booking_id, v_total_amount, CURDATE());
// END ";
// $pdo->exec($sql);
// }