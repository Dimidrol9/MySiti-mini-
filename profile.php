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

    $stmt = $pdo->prepare("SELECT email, role, name, phone, birth_date FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $birth_date = trim($_POST['birth_date']);

        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, birth_date = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $birth_date, $_SESSION['user_id']]);

        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'update_profile')");
        $stmt->execute([$_SESSION['user_id']]);

        $message = "Профіль успішно оновлено!";
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
    <title>Мій профіль</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <?php include 'templates/profile.html'; ?>
    </div>
</body>
</html>