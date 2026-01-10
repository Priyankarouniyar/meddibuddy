<?php
require_once '../includes/functions.php';

// Clear all session data
$_SESSION = array();

// Destroy the session
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}

// Use absolute URL with full medibuddy path
header("Location: /medibuddy/auth/login.php");
exit;
?>
