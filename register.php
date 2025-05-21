<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="css/styles.css">
    
</head>
<body>
    <div class="wrapper">
        <?php include 'templates/register.html'; ?>
    </div>
</body>
</html>