<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

$response = [
    'unread' => 0,
    'latest_message' => '',
    'new_notification' => false
];

if (isset($_SESSION['user_id'])) {
    // Get unread count
    $count_stmt = $connection->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $count_stmt->bind_param("i", $_SESSION['user_id']);
    $count_stmt->execute();
    $response['unread'] = $count_stmt->get_result()->fetch_row()[0];

    // Get latest unread message if available
    if ($response['unread'] > 0) {
        $message_stmt = $connection->prepare("SELECT message FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC LIMIT 1");
        $message_stmt->bind_param("i", $_SESSION['user_id']);
        $message_stmt->execute();
        $response['latest_message'] = $message_stmt->get_result()->fetch_row()[0];
        $response['new_notification'] = true;
    }
}

echo json_encode($response);
?>