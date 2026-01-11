<?php
/* ---------- SESSION ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- INCLUDES ---------- */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

/* ---------- AUTH CHECK ---------- */
if (!isset($noAuthCheck)) {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBuddy</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <style>
        .navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        .nav-brand a {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }
        .nav-menu {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.3s;
        }
        .nav-menu a:hover {
            color: #667eea;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-name {
            color: #667eea;
            font-weight: bold;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container">

        <!-- BRAND -->
        <div class="nav-brand">
            <a href="<?= isAdmin() ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/user/dashboard.php' ?>">
                🏥 MediBuddy
            </a>
        </div>

        <!-- MENU -->
        <ul class="nav-menu">

            <?php if (isLoggedIn()): ?>

                <?php if (isAdmin()): ?>
                    <a href="dashboard.php" class="btn btn-primary" style="margin-top">Dashboard</a>
                    <a href="users.php" class="btn btn-primary" style="margin-top">Users</a>
                    <a href="medicines.php" class="btn btn-primary" style="margin-top">Medicines</a>
                    <a href="doctors.php" class="btn btn-primary" style="margin-top">Doctors</a></li>
                    <a href="drugs.php" class="btn btn-primary" style="margin-top">Drugs</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary" style="margin-top">Dashboard</a>
                    <a href="family-members.php" class="btn btn-primary" style="margin-top">Family</a>
                    <a href="prescriptions.php" class="btn btn-primary" style="margin-top">Prescription</a>
                    <a href="reminders.php" class="btn btn-primary" style="margin-top">Reminder</a>
                   
                <?php endif; ?>

                <li class="user-menu">
                    <span class="user-name">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>
                    </span>
                    <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-btn">
                        Logout
                    </a>
                </li>

            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                <li><a href="<?= BASE_URL ?>/auth/register.php">Register</a></li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<div class="container">
    <?php showAlert(); ?>
</div>
