<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = ''; // Ініціалізація $message

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($new_password) < 6) {
            $message = "Новий пароль має бути не коротше 6 символів!";
        } elseif ($new_password !== $confirm_password) {
            $message = "Нові паролі не збігаються!";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (password_verify($current_password, $user['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'change_password')");
                $stmt->execute([$_SESSION['user_id']]);

                $message = "Пароль успішно змінено!";
            } else {
                $message = "Поточний пароль невірний!";
            }
        }
    }
} catch (PDOException $e) {
    $message = "Помилка: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зміна пароля</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <?php include 'templates/change_password.html'; ?>
    </div>
</body>
</html>