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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBuddy - Medicine Reminder System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5rem 1rem; text-align: center; }
        .hero-section h1 { font-size: 3.5rem; margin-bottom: 1rem; font-weight: bold; }
        .hero-section p { font-size: 1.3rem; margin-bottom: 1rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .hero-buttons { margin-top: 2rem; }
        .hero-buttons a { display: inline-block; margin: 0 0.5rem; }
        .features-section { padding: 4rem 0; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin: 2rem 0; }
        .feature-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; transition: 0.3s; }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        .feature-icon { font-size: 2.5rem; margin-bottom: 1rem; color: #667eea; }
        .cta-section { background: #f5f5f5; padding: 3rem 1rem; text-align: center; margin-top: 3rem; }
        .cta-section h2 { margin-bottom: 1.5rem; }
        .navbar-landing { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .navbar-landing .nav-menu a { color: #667eea; }
        .navbar-landing .nav-menu a:hover { background: #f0f0f0; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-landing">
        <div class="container">
            <div class="nav-brand"><a href="index.php">🏥 MediBuddy</a></div>
            <ul class="nav-menu">
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>MediBuddy</h1>
            <p>Your Personal Medicine Reminder System</p>
            <p>Never miss a dose again. Manage medications for your entire family, track prescriptions, and receive timely reminders.</p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn btn-primary">Get Started Free</a>
                <a href="auth/login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 1rem;">Why Choose MediBuddy?</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">⏰</div>
                    <h3>Smart Reminders</h3>
                    <p>Get personalized medicine reminders based on your schedule. Set frequency, time, and days to suit your routine.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">👨‍👩‍👧‍👦</div>
                    <h3>Family Management</h3>
                    <p>Manage medications for your entire family members from a single account. Track everyone's health in one place.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Prescription Tracking</h3>
                    <p>Store and organize prescriptions digitally. Keep doctor details and prescription information easily accessible.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">💊</div>
                    <h3>Medicine Database</h3>
                    <p>Access comprehensive medicine information including composition, manufacturers, and usage guidelines.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Track History</h3>
                    <p>Monitor medication adherence and mark doses as taken to maintain a complete health record.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure & Private</h3>
                    <p>Your health data is encrypted and secure. Complete privacy for you and your family's medical information.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Take Control of Your Health?</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">Join thousands of users who never miss their medication again.</p>
            <a href="auth/register.php" class="btn btn-primary" style="padding: 0.9rem 2rem; font-size: 1.1rem;">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> MediBuddy - Medicine Reminder System. All rights reserved.</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: rgba(255,255,255,0.7);">Your trusted health companion for medication management.</p>
        </div>
    </footer>
</body>
</html>
