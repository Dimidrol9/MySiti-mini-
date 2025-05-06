<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Додавання нового користувача
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Некоректний email!";
        } elseif (strlen($password) < 6) {
            $message = "Пароль має бути не коротше 6 символів!";
        } elseif (!in_array($role, ['guest', 'moderator', 'admin'])) {
            $message = "Некоректна роль!";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = "Користувач із таким email уже існує!";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$email, $password_hash, $role]);
                $message = "Користувача успішно додано!";
            }
        }
    }

    // Редагування ролі користувача
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
        $user_id = (int)$_POST['user_id'];
        $role = trim($_POST['role']);

        if (!in_array($role, ['guest', 'moderator', 'admin'])) {
            $message = "Некоректна роль!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $user_id]);
            $message = "Роль користувача успішно оновлено!";
        }
    }

    // Видалення користувача
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        if ($user_id === $_SESSION['user_id']) {
            $message = "Ви не можете видалити власний обліковий запис!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "Користувача успішно видалено!";
        }
    }

    // Отримання списку користувачів
    $stmt = $pdo->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Помилка: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <?php include 'templates/admin_panel.html'; ?>
    </div>
</body>
</html>