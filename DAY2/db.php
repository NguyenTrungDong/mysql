<?php 

// Kết nối cơ sở dữ liệu sử dụng PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mysql_day2", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8");
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
