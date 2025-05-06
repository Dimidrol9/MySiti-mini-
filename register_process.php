<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валідація
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Усі поля повинні бути заповнені!";
        header("Location: register.php");
        exit;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Паролі не співпадають!";
        header("Location: register.php");
        exit;
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Пароль повинен містити щонайменше 8 символів!";
        header("Location: register.php");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Перевірка, чи email вже існує
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Користувач із таким email вже існує!";
            header("Location: register.php");
            exit;
        }

        // Хешування пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Додавання користувача
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'guest')");
        $stmt->execute([$email, $hashed_password]);

        // Логування
        $user_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'register')");
        $stmt->execute([$user_id]);
        
        // Додаємо повідомлення про успішну реєстрацію
        $_SESSION['success'] = "Реєстрація пройшла успішно! Будь ласка, увійдіть в систему.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Помилка: " . $e->getMessage();
        header("Location: register.php");
        exit;
    }
} else {
    // Якщо спроба прямого доступу до файлу
    header("Location: register.php");
    exit;
}
?>