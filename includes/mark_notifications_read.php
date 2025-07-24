<?php
session_start();
require_once 'connection.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $connection->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>