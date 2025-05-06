<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['moderator', 'admin'])) {
    header("Location: index.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=auth_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email_filter = isset($_GET['email']) ? trim($_GET['email']) : '';
    $action_filter = isset($_GET['action']) ? trim($_GET['action']) : '';

    $per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;

    $query = "SELECT COUNT(*) FROM activity_logs JOIN users ON activity_logs.user_id = users.id";
    $conditions = [];
    $params = [];
    if ($email_filter) {
        $conditions[] = "users.email LIKE ?";
        $params[] = "%$email_filter%";
    }
    if ($action_filter) {
        $conditions[] = "activity_logs.action = ?";
        $params[] = $action_filter;
    }
    if ($conditions) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    $query = "SELECT activity_logs.*, users.email FROM activity_logs JOIN users ON activity_logs.user_id = users.id";
    if ($conditions) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $query .= " ORDER BY activity_logs.timestamp DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($query);
    $param_count = 1;
    if ($email_filter) {
        $stmt->bindValue($param_count++, "%$email_filter%", PDO::PARAM_STR);
    }
    if ($action_filter) {
        $stmt->bindValue($param_count++, $action_filter, PDO::PARAM_STR);
    }
    $stmt->bindValue($param_count++, $per_page, PDO::PARAM_INT);
    $stmt->bindValue($param_count++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
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
    <title>Журнал активності</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/sidebar.html'; ?>
    <div class="content">
        <?php include 'templates/activity_logs.html'; ?>
    </div>
</body>
</html>