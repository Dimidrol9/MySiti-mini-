<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Неавторизований користувач']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Перевірка часу останньої активності
    $stmt = $pdo->prepare("SELECT last_active FROM user_activity WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $activity = $stmt->fetch();

    if ($activity) {
        $last_active = strtotime($activity['last_active']);
        $current_time = time();
        $inactive_threshold = 1800; // 30 хвилин у секундах

        if (($current_time - $last_active) > $inactive_threshold) {
            // Логування завершення сесії
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'timeout')");
            $stmt->execute([$_SESSION['user_id']]);
            session_destroy();
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Сесію завершено через неактивність']);
            exit;
        }
    } else {
        // Якщо запису немає, створюємо його
        $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, last_active) VALUES (?, NOW())");
        $stmt->execute([$_SESSION['user_id']]);
    }

    // Оновлення часу активності
    $stmt = $pdo->prepare("UPDATE user_activity SET last_active = NOW() WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Логування перевірки активності
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'activity_check')");
    $stmt->execute([$_SESSION['user_id']]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    // Логування помилки
    error_log("Помилка в check_activity.php: " . $e->getMessage(), 3, "C:/xampp/htdocs/auth_app/logs/error.log");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Помилка бази даних']);
}
?>