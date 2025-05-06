<?php
session_start();
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Логування входу
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'login')");
            $stmt->execute([$user['id']]);

            // Оновлення часу активності
            $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, last_active) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_active = NOW()");
            $stmt->execute([$user['id']]);

            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Невірний email або пароль!";
        }
    } catch (PDOException $e) {
        $error_message = "Помилка: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/login.js" defer></script>
</head>
<body>
    <div class="wrapper">
        <?php include 'templates/login.html'; ?>
    </div>
</body>
</html>