<?php
include_once 'db.php';
// Hàm hiển thị kết quả dưới dạng bảng HTML
function displayResult($title, $stmt) {
    echo "<h2>$title</h2>";
    echo "<table border='1'>";
    $firstRow = true;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
        if ($firstRow) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $firstRow = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Yêu cầu 1: Tính tổng doanh thu theo danh mục sản phẩm
function queryRevenueByCategory($pdo) {
                //Lấy ra tổng doanh thu và tên danh mục sản phẩm
    $sql = "SELECT p.category, SUM(oi.quantity * p.price) AS total_revenue  
            FROM Orders o
            INNER JOIN OrderItems oi ON o.order_id = oi.order_id
            INNER JOIN Products p ON oi.product_id = p.product_id
            WHERE o.status = 'completed'
            GROUP BY p.category";
    $stmt = $pdo->query($sql);
    displayResult("1. Tổng doanh thu theo danh mục sản phẩm", $stmt);
}

// Yêu cầu 2: Lẩy ra danh sách người dùng theo người giới thiệu
function queryUsersWithReferrer($pdo) {
    $sql = "SELECT u1.user_id, u1.full_name, COALESCE(u2.full_name, 'None') AS referrer_name
            FROM Users u1
            LEFT JOIN Users u2 ON u1.referrer_id = u2.user_id";
    $stmt = $pdo->query($sql);
    displayResult("2. Người dùng và người giới thiệu", $stmt);
}

// Yêu cầu 3: Lấy ra danh sách sản phẩm không còn bán (Inactive) nhưng đã được đặt
function queryInactiveProducts($pdo) {
    $sql = "SELECT DISTINCT p.product_id, p.product_name
            FROM Products p
            INNER JOIN OrderItems oi ON p.product_id = oi.product_id
            WHERE p.is_active = 0";
    $stmt = $pdo->query($sql);
    displayResult("3. Sản phẩm không còn bán nhưng đã được đặt", $stmt);
}

// Yêu cầu 4: Người dùng chưa đặt hàng
function queryInactiveUsers($pdo) {
    $sql = "SELECT u.user_id, u.full_name
            FROM Users u
            LEFT JOIN Orders o ON u.user_id = o.user_id
            WHERE o.order_id IS NULL";
    $stmt = $pdo->query($sql);
    displayResult("4. Người dùng chưa đặt hàng", $stmt);
}

// Yêu cầu 5: Tìm đơn hàng đầu tiên của mỗi người dùng
function queryFirstOrder($pdo) {
    $sql = "SELECT o.user_id, MIN(o.order_id) AS first_order_id
            FROM Orders o
            GROUP BY o.user_id";
    $stmt = $pdo->query($sql);
    displayResult("5. Đơn hàng đầu tiên của mỗi người dùng", $stmt);
}

// Yêu cầu 6: Tổng chi tiêu của mỗi người dùng
function queryTotalSpent($pdo) {
    $sql = "SELECT u.user_id, u.full_name, SUM(oi.quantity * p.price) AS total_spent
            FROM Users u
            LEFT JOIN Orders o ON u.user_id = o.user_id
            LEFT JOIN OrderItems oi ON o.order_id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.product_id
            WHERE o.status = 'completed'
            GROUP BY u.user_id, u.full_name";
    $stmt = $pdo->query($sql);
    displayResult("6. Tổng chi tiêu của mỗi người dùng", $stmt);
}

// Yêu cầu 7: Người dùng chi tiêu trên 25 triệu
function queryHighSpenders($pdo) {
    $sql = "SELECT u.user_id, u.full_name, SUM(oi.quantity * p.price) AS total_spent
            FROM Users u
            LEFT JOIN Orders o ON u.user_id = o.user_id
            LEFT JOIN OrderItems oi ON o.order_id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.product_id
            WHERE o.status = 'completed'
            GROUP BY u.user_id, u.full_name
            HAVING total_spent > 25000000";
    $stmt = $pdo->query($sql);
    displayResult("7. Người dùng chi tiêu trên 25 triệu", $stmt);
}

// Yêu cầu 8: So sánh các giá trị các đơn hàng từ các thành phố
function queryCityStats($pdo) {
    $sql = "SELECT u.city, 
                   COUNT(DISTINCT o.order_id) AS total_orders, 
                   SUM(oi.quantity * p.price) AS total_revenue
            FROM Users u
            LEFT JOIN Orders o ON u.user_id = o.user_id
            LEFT JOIN OrderItems oi ON o.order_id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.product_id
            WHERE o.status = 'completed'
            GROUP BY u.city";
    $stmt = $pdo->query($sql);
    displayResult("8. Tổng số đơn hàng và doanh thu theo thành phố", $stmt);
}

// Yêu cầu 9: Người dùng có ít nhất 2 đơn hàng completed
function queryUsersWithMultipleOrders($pdo) {
    $sql = "SELECT u.user_id, u.full_name, COUNT(o.order_id) AS order_count
            FROM Users u
            INNER JOIN Orders o ON u.user_id = o.user_id
            WHERE o.status = 'completed'
            GROUP BY u.user_id, u.full_name
            HAVING order_count >= 2";
    $stmt = $pdo->query($sql);
    displayResult("9. Người dùng có ít nhất 2 đơn hàng completed", $stmt);
}

// Yêu cầu 10: Đơn hàng có sản phẩm thuộc nhiều danh mục
function queryMultiCategoryOrders($pdo) {
    $sql = "SELECT o.order_id
            FROM Orders o
            INNER JOIN OrderItems oi ON o.order_id = oi.order_id
            INNER JOIN Products p ON oi.product_id = p.product_id
            GROUP BY o.order_id
            HAVING COUNT(DISTINCT p.category) > 1";
    $stmt = $pdo->query($sql);
    displayResult("10. Đơn hàng có sản phẩm thuộc nhiều danh mục", $stmt);
}

// Yêu cầu 11: Kết hợp danh sách người dùng đã đặt hàng và được giới thiệu
function queryCombinedUsers($pdo) {
    $sql = "SELECT u.user_id, u.full_name, 'placed_order' AS source
            FROM Users u
            INNER JOIN Orders o ON u.user_id = o.user_id
            UNION
            SELECT u.user_id, u.full_name, 'referred' AS source
            FROM Users u
            WHERE u.referrer_id IS NOT NULL";
    $stmt = $pdo->query($sql);
    displayResult("11. Kết hợp danh sách người dùng", $stmt);
}

// HTML cơ bản để hiển thị kết quả
?>