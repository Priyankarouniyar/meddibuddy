<?php
/* ---------- SESSION ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- BASIC HELPERS ---------- */

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/* ---------- AUTH HELPERS ---------- */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/* Require login for protected pages */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

/* Require admin access */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

/* ---------- ALERT SYSTEM ---------- */

function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<div class='alert alert-{$alert['type']}'>
                {$alert['message']}
              </div>";
        unset($_SESSION['alert']);
    }
}

function displayAlert() {
    showAlert();
}
