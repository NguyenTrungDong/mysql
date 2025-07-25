<?php include_once 'model.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lí tuyển dụng</title>
     <!-- BOOTSTRAPT 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- FONTAWESOME-ICON -->
     <script src="https://kit.fontawesome.com/ffb3c051a8.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <?php 
        queryCandidateUser($pdo);
        queryMaxSalary($pdo);
        queryMinSalary($pdo);
        // createShortlistedCandidatesTable($pdo);
        ShowCandidateExperience($pdo);
        ShowAllCandidates($pdo);
        CompareSalary($pdo);
        ?>
    </div>
</body>
</html>