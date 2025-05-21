<?php
session_start();


// Перевіряємо чи авторизований користувач
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Отримуємо випадкове питання
$questions = [
    'Ви ще тут?' => 'Так',
    'Все гаразд?' => 'Так',
    'Продовжуємо працювати?' => 'Так',
    'Ви активні?' => 'Так',
    'Ще не пішли?' => 'Ні'
];

// Вибираємо випадкове питання
$question_keys = array_keys($questions);
$random_index = array_rand($question_keys);
$current_question = $question_keys[$random_index];
$correct_answer = $questions[$current_question];

// Зберігаємо питання та правильну відповідь в сесії
$_SESSION['activity_question'] = $current_question;
$_SESSION['activity_answer'] = $correct_answer;
$_SESSION['activity_start_time'] = time(); // час початку перевірки

// Обробка відповіді на питання
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $user_answer = $_POST['answer'];
    $response_time = time() - $_SESSION['activity_start_time'];
    
    // Оновлюємо запис активності користувача в таблиці user_activity
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Перевіряємо, чи існує запис для цього користувача
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = ?");
        $check_stmt->execute([$_SESSION['user_id']]);
        $exists = $check_stmt->fetchColumn();
        
        // Отримаємо email користувача (якщо ще не збережено в сесії)
        if (!isset($_SESSION['email'])) {
            $stmt_email = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt_email->execute([$_SESSION['user_id']]);
            $user_email = $stmt_email->fetchColumn();
            $_SESSION['email'] = $user_email;
        }

        // Оновлюємо або вставляємо активність з email
        if ($exists) {
            $stmt = $pdo->prepare("UPDATE user_activity SET last_active = NOW(), email = ? WHERE user_id = ?");
            $stmt->execute([$_SESSION['email'], $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, last_active, email) VALUES (?, NOW(), ?)");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['email']]);
        }

        
        // Логування відповіді
        $log_details = json_encode([
            'question' => $_SESSION['activity_question'],
            'user_answer' => $user_answer,
            'correct_answer' => $_SESSION['activity_answer'],
            'response_time' => $response_time
        ]);
        error_log("Активність користувача ID {$_SESSION['user_id']}: $log_details");
        
    } catch (PDOException $e) {
        // Логування помилки
        error_log("Помилка при оновленні активності: " . $e->getMessage());
    }
    
    // Перевіряємо відповідь
    if ($user_answer === $_SESSION['activity_answer']) {
        // Оновлюємо час останньої активності
        $_SESSION['last_auth'] = time();
        
        // Очищаємо збережену URL і перенаправляємо на home.php
        unset($_SESSION['return_to_page']);
        header("Location: /auth_app/home.php");
        exit;
    } else {
        $message = '<div class="error">Неправильна відповідь. Спробуйте ще раз.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Перевірка активності</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
    let secondsLeft = 30;

    function updateTimer() {
        const timerElement = document.getElementById('timer');
        
        if (secondsLeft <= 0) {
            window.location.href = 'login.php';
            return;
        }
        
        if (timerElement) {
            timerElement.textContent = secondsLeft;
        }
        secondsLeft--;
        setTimeout(updateTimer, 1000);
    }

    window.onload = function() {
        sessionStorage.removeItem('returnToPage');
        updateTimer();
    };
    </script>
    <style>
        .activity-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .question {
            font-size: 24px;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        
        .timer-container {
            margin-bottom: 20px;
            color: #e74c3c;
            font-weight: bold;
        }
        
        .answer-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .answer-button {
            padding: 15px 40px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .yes-button {
            background-color: #4caf50;
            color: white;
        }
        
        .no-button {
            background-color: #f44336;
            color: white;
        }
        
        .answer-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .yes-button:hover {
            background-color: #43a047;
        }
        
        .no-button:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="activity-container">
        <h1>Перевірка активності</h1>
        <?php echo $message; ?>
        
        <div class="question">
            <?php echo htmlspecialchars($current_question); ?>
        </div>
        
        <div class="timer-container">
            Залишилося часу: <span id="timer">30</span> секунд
        </div>
        
        <form method="post" action="">
            <div class="answer-buttons">
                <button type="submit" name="answer" value="Так" class="answer-button yes-button">Так</button>
                <button type="submit" name="answer" value="Ні" class="answer-button no-button">Ні</button>
            </div>
        </form>
    </div>
</body>
</html>