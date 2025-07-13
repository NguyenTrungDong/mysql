<?php 

try
{
    $dns = "mysql:host=localhost;dbname=hotelbookingsystem;charset=utf8";
    $username = "root";
    $password = "";
    $pdo = new PDO($dns,$username,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    echo "Connect to database successfully!";
}catch(Exception $exception)
{
    echo "ERROR:" . $exception->getMessage();
}