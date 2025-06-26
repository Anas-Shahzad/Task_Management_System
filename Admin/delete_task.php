<?php
session_start();
require_once '../includes/connection.php';

// Admin validation
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Verify task exists and delete
if (isset($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    
    // Start transaction
    mysqli_begin_transaction($connection);
    
    try {
        // Delete from task_assignments first (foreign key constraint)
        mysqli_query($connection, "DELETE FROM task_assignments WHERE task_id = $task_id");
        
        // Then delete the task
        $result = mysqli_query($connection, "DELETE FROM tasks WHERE id = $task_id");
        
        if (mysqli_affected_rows($connection) > 0) {
            mysqli_commit($connection);
            $_SESSION['success'] = "Task deleted successfully";
        } else {
            throw new Exception("Task not found");
        }
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $_SESSION['error'] = "Error deleting task: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit();
?>