<?php
session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

/* Clear session data */
$_SESSION = [];

/* Destroy session */
session_destroy();

/* Redirect to login page */
redirect(BASE_URL . '/auth/login.php?logout=success');
