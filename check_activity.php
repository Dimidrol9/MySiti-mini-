<?php
session_start();

header('Content-Type: application/json');

// Перевіряємо, чи авторизований користувач
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Отримуємо або повертаємо питання
$questions = [
    'Ви ще тут?' => 'Так',
    'Все гаразд?' => 'Так',
    'Продовжуємо працювати?' => 'Так',
    'Ви активні?' => 'Так',
    'Ще не пішли?' => 'Ні'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Вибираємо випадкове питання
    $question_keys = array_keys($questions);
    $random_index = array_rand($question_keys);
    $current_question = $question_keys[$random_index];
    $correct_answer = $questions[$current_question];

    // Зберігаємо в сесії
    $_SESSION['activity_question'] = $current_question;
    $_SESSION['activity_answer'] = $correct_answer;
    $_SESSION['activity_start_time'] = time();

    echo json_encode(['status' => 'success', 'question' => $current_question]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $user_answer = $_POST['answer'];
    $response_time = time() - $_SESSION['activity_start_time'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Оновлення активності
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = ?");
        $check_stmt->execute([$_SESSION['user_id']]);
        $exists = $check_stmt->fetchColumn();

        if (!isset($_SESSION['email'])) {
            $stmt_email = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt_email->execute([$_SESSION['user_id']]);
            $_SESSION['email'] = $stmt_email->fetchColumn();
        }

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE user_activity SET last_active = NOW(), email = ? WHERE user_id = ?");
            $stmt->execute([$_SESSION['email'], $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, last_active, email) VALUES (?, NOW(), ?)");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['email']]);
        }

        // Логування
        $log_details = json_encode([
            'question' => $_SESSION['activity_question'],
            'user_answer' => $user_answer,
            'correct_answer' => $_SESSION['activity_answer'],
            'response_time' => $response_time
        ]);
        error_log("Активність користувача ID {$_SESSION['user_id']}: $log_details");

        if ($user_answer === $_SESSION['activity_answer']) {
            $_SESSION['last_auth'] = time();
            echo json_encode(['status' => 'success', 'message' => 'Activity verified']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Неправильна відповідь']);
        }
    } catch (PDOException $e) {
        error_log("Помилка: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Server error']);
    }
    exit;
}
?>