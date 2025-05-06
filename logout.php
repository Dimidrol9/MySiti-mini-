<?php
session_start();

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Логування виходу
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'logout')");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Помилка в logout.php: " . $e->getMessage(), 3, "C:/xampp/htdocs/auth_app/logs/error.log");
    }

    // Завершення сесії
    session_destroy();
}

// Перенаправлення на index.php
header("Location: index.php");
exit;
?>