<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

if (!$username || !$password || !$role) {
    die("Missing credentials or role.");
}

$stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    if ($role === $user['role']) {
        $_SESSION['user'] = [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        if ($role === 'admin') {
            header("Location: ../login_admin.php");
            exit;
        } else {
            header("Location: ../login_user.php");
            exit;
        }
    } else {
        echo "Invalid role selected!";
    }
} else {
    echo "Invalid username or password!";
}
