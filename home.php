<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Помилка підключення до бази: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Головна</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/activity_check.js" defer></script>
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <h1>Ласкаво просимо до системи авторизації</h1>
        <p>Це головна сторінка вашого додатка. Тут ви можете керувати своїм профілем, змінювати пароль та переглядати журнали активності.</p>
        <p>Система підтримує три ролі користувачів:</p>
        <ul>
            <li><strong>Гість (guest)</strong>: Може переглядати свій профіль і змінювати пароль.</li>
            <li><strong>Модератор (moderator)</strong>: Має доступ до журналів користувачів і активності.</li>
            <li><strong>Адмін (admin)</strong>: Має повний доступ до всіх функцій, включаючи керування користувачами.</li>
        </ul>
        <p>Використовуйте бічне меню для навігації по функціях.</p>
    </div>
    
    <?php include 'templates/modal.html'; ?>

    
</body>
</html>