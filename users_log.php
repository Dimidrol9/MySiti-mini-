<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['moderator', 'admin'])) {
    header("Location: index.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    $stmt = $pdo->prepare("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Помилка: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал користувачів</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <?php include 'templates/users_log.html'; ?>
    </div>
</body>
</html>