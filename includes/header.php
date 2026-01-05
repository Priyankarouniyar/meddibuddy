<?php
session_start();
require_once '../config/database.php';
require_once 'functions.php';

if (!isset($noAuthCheck)) {
    if (!isLoggedIn()) redirect('../auth/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js"></script>
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="nav-brand"><a href="dashboard.php">MediBuddy</a></div>
        <ul class="nav-menu">
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-users.php">Users</a></li>
                <?php else: ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="family-members.php">Family Members</a></li>
                    <li><a href="reminders.php">Reminders</a></li>
                <?php endif; ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container">
<?php displayAlert(); ?>
