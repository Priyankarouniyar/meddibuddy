<?php
// Database configuration - No session_start here to avoid duplicates

$host = "localhost";
$user = "root";
$pass = "";  // Default XAMPP password is empty
$db   = "medibuddy";

// Establish connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Improved error handling: Log error but don't die immediately (allows page to load with a message)
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());  // Logs to PHP error log
    // Optional: Set a session alert instead of dying
    // session_start();  // Only if needed here, but avoid
    // $_SESSION['db_error'] = "Database connection failed. Please try again later.";
    // Instead of die(), you can continue and handle in index.php
    $conn = null;  // Set to null for checks elsewhere
} else {
    // Optional: Set charset for security
    mysqli_set_charset($conn, "utf8");
}

// Note: Functions like redirect, isLoggedIn, etc., are now in functions.php to avoid duplicates
?>
