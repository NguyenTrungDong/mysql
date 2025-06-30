<?php

include_once 'db.php';

function show($title, $stmt)
{
    echo "<h1>$title</h1>";

    // Lấy toàn bộ dữ liệu một lần
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "<p>Không có dữ liệu.</p>";
        return;
    }

    echo "<table border='1' cellpadding='5' cellspacing='0' class='table'>";
    
    // In tiêu đề bảng (tên cột)
    echo "<tr class='table-dark'>";
    foreach (array_keys($rows[0]) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>";

    // In từng dòng dữ liệu
    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'Khong co') . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

function queryCandidateUser($pdo) 
{
    $sql = "SELECT c.candidate_id,c.full_name FROM candidates c
    WHERE EXISTS 
    (SELECT 1 FROM applications a JOIN jobs j ON a.job_id = j.job_id
    WHERE c.candidate_id = a.candidate_id AND j.title = 'IT') ";
    $stmt = $pdo->query($sql);
    show("Ứng viên IT", $stmt);
}

function queryMaxSalary($pdo)
{
    $sql = "SELECT j.job_id,j.title,j.max_salary
    FROM jobs j WHERE j.max_salary > ANY (SELECT c.expected_salary FROM candidates c)";
    $stmt = $pdo->query($sql);
    show("Công Việc Mức Lương MAX_SALARY VƯỢT MONG ĐỢI", $stmt);
}

function queryMinSalary($pdo)
{
    $sql = "SELECT j.job_id,j.title,j.max_salary
    FROM jobs j WHERE j.min_salary > ANY (SELECT c.expected_salary FROM candidates c)";
    $stmt = $pdo->query($sql);
    show("Công Việc Mức Lương MIN_SALARY VƯỢT MONG ĐỢI", $stmt);
}

function createShortlistedCandidatesTable($pdo)
{
    $sql = "INSERT INTO ShortlistedCandidates (candidate_id, job_id, selection_date)
            SELECT a.candidate_id, a.job_id, CURDATE()
            FROM Applications a
            WHERE a.status = 'Accepted'
            ";
    $stmt = $pdo->query($sql);
    show("tạo bảng mới", $stmt);
}

function ShowCandidateExperience($pdo)
{
    $sql = "SELECT full_name , years_exp , CASE 
    WHEN years_exp  < 1 THEN 'Fresher'
    WHEN years_exp  BETWEEN 1 AND 3 THEN 'Junior'
    WHEN years_exp  > 6 THEN 'Senior'
    WHEN years_exp BETWEEN 4 AND 6 THEN 'Middle_level' 
    END AS candidate_level  FROM candidates  ORDER BY years_exp  DESC";
    $stmt = $pdo ->query($sql);
    show("Hiển thị danh sách ứng cử viên theo kinh nghiệm", $stmt);
}

function ShowAllCandidates($pdo)
{
    $sql = "SELECT candidate_id,full_name,email,
    IFNULL(phone, 'Chưa cung cấp') AS candidate_phone FROM candidates 
    ORDER BY candidate_id DESC";
    $stmt = $pdo->query($sql);
    show("Danh sách ứng viên theo số điện thoại",$stmt);
}

function CompareSalary($pdo)
{
    $sql = "SELECT job_id,title,min_salary, max_salary
    FROM jobs WHERE min_salary != max_salary AND
    max_salary >= 1000
    ORDER BY job_id DESC
    ";
    $stmt = $pdo->query($sql);
    show("Danh sách lương công việc", $stmt);
}