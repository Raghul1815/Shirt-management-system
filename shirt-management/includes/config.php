<?php
// Database config for InfinityFree
$host = "root";   // MySQL Hostname
$dbname = "***";        // Your Database Name
$username = "***";          // MySQL Username
$password = "***";          // MySQL Password

// Session start (only once, safe check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>
