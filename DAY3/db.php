<?php 
// Kết nối đến csld mysql bằng PDO
try
{
    $dsn = "mysql:host=localhost;dbname=mysql_day3;charset=utf8";
    $username = "root";
    $password = "";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // thiết lập chế độ báo lỗi
}catch (Exception $exception)
{
    echo "Error:" . $exception->getMessage();
    
}