<?php 

// Kết nối cơ sở dữ liệu

try
{
    $dns = "mysql:host=localhost;dbname=OnlineLearning;charset=utf8";
    $username = "root";
    $password = "";
    $pdo = new PDO($dns,$username,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connect to Database Successfully!";
}catch(Exception $exception)
{
    echo "Error:" . $exception->getMessage();
}