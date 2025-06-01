<?php include_once 'model.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Commerce SQL Queries</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>E-Commerce SQL Query Results</h1>
    <?php
    // Gọi các hàm thực hiện truy vấn
    queryRevenueByCategory($pdo);
    queryUsersWithReferrer($pdo);
    queryInactiveProducts($pdo);
    queryInactiveUsers($pdo);
    queryFirstOrder($pdo);
    queryTotalSpent($pdo);
    queryHighSpenders($pdo);
    queryCityStats($pdo);
    queryUsersWithMultipleOrders($pdo);
    queryMultiCategoryOrders($pdo);
    queryCombinedUsers($pdo);
    ?>
</body>
</html>