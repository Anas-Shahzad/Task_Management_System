<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change this in production!
define('DB_PASS', '');           // Change this in production!
define('DB_NAME', 'tms_db');
define('DB_PORT', 3306);         // Default MySQL port

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);    // Change to 0 in production

// Create connection with improved security
try {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if (!$connection) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Set charset to prevent SQL injection
    mysqli_set_charset($connection, 'utf8mb4');
    
    // Set timezone for timestamps
    mysqli_query($connection, "SET time_zone = '+00:00'");
    
} catch (Exception $e) {
    // Log error securely (create logs directory first)
    error_log($e->getMessage(), 3, __DIR__.'/../logs/db_errors.log');
    
    // User-friendly message
    die("System maintenance in progress. Please try again later.");
}
?>