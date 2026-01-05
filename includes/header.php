<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

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
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .navbar { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
        .nav-brand a { font-size: 1.5rem; font-weight: bold; color: #667eea; text-decoration: none; }
        .nav-menu { list-style: none; display: flex; gap: 2rem; margin: 0; padding: 0; }
        .nav-menu a { text-decoration: none; color: #333; font-weight: 500; transition: 0.3s; }
        .nav-menu a:hover { color: #667eea; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-name { color: #667eea; font-weight: bold; }
        .logout-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem; }
        .logout-btn:hover { background: #c82333; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="nav-brand"><a href="<?= isAdmin() ? '/admin/dashboard.php' : '/user/dashboard.php' ?>">🏥 MediBuddy</a></div>
        <ul class="nav-menu">
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <li><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="/admin/users.php">Users</a></li>
                    <li><a href="/admin/medicines.php">Medicines</a></li>
                    <li><a href="/admin/doctors.php">Doctors</a></li>
                    <li><a href="/admin/drugs.php">Drugs</a></li>
                <?php else: ?>
                    <li><a href="/user/dashboard.php">Dashboard</a></li>
                    <li><a href="/user/family-members.php">Family</a></li>
                    <li><a href="/user/prescriptions.php">Prescriptions</a></li>
                    <li><a href="/user/reminders.php">Reminders</a></li>
                <?php endif; ?>
                <li class="user-menu">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                    <a href="/auth/logout.php" class="logout-btn">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="/auth/login.php">Login</a></li>
                <li><a href="/auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container">
    <?php showAlert(); ?>
</div>
