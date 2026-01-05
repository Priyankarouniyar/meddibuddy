<?php 
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}
$noAuthCheck = true; // Prevent header.php from redirecting
if (!$conn) {
    setAlert("Database connection failed. Please contact support.", "error");
    displayAlert();
    // Continue loading the page
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBuddy - Medicine Reminder System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">MediBuddy</a>
            </div>
            <ul class="nav-menu">
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to MediBuddy</h1>
            <p>Your Personal Medicine Reminder System</p>
            <p>Never miss a dose again. Manage medications for your entire family in one place.</p>
            <div style="margin-top: 2rem;">
                <a href="auth/register.php" class="btn btn-primary" style="margin-right: 1rem;">Get Started</a>
                <a href="auth/login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <div class="main-content">
        <div class="container">
            <h2 style="text-align:center; margin-bottom:2rem;">Features</h2>
            <div class="dashboard-grid">
                <div class="card">
                    <h3 style="color: #667eea; margin-bottom: 1rem;">Medicine Reminders</h3>
                    <p>Set up personalized medicine reminders with customizable schedules and notifications.</p>
                </div>

                <div class="card">
                    <h3 style="color: #667eea; margin-bottom: 1rem;">Family Management</h3>
                    <p>Manage medications for your entire family from a single account.</p>
                </div>

                <div class="card">
                    <h3 style="color: #667eea; margin-bottom: 1rem;">Prescription Tracking</h3>
                    <p>Upload and manage prescriptions digitally with easy access anytime.</p>
                </div>

                <div class="card">
                    <h3 style="color: #667eea; margin-bottom: 1rem;">Medicine Database</h3>
                    <p>Access detailed information about medicines including composition and usage.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> MediBuddy. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
