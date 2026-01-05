<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// if (isLoggedIn()) redirect('../user/dashboard.php');

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = sanitize($_POST['first_name']);
    $last = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($first) || empty($last) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if(mysqli_num_rows($check) > 0) {
            $error = "Email address already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) VALUES ('$first','$last','$email','$hash','user')";
            if (mysqli_query($conn, $sql)) {
                $success = true;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .auth-container { background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 500px; width: 100%; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #667eea; font-size: 2rem; margin: 0 0 0.5rem 0; }
        .auth-header p { color: #999; margin: 0; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 0.9rem; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1rem; transition: border-color 0.3s; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-group input::placeholder { color: #ccc; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn-register { width: 100%; padding: 1rem; background: #667eea; color: white; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-register:hover { background: #5a67d8; }
        .auth-footer { text-align: center; margin-top: 1.5rem; color: #666; }
        .auth-footer a { color: #667eea; text-decoration: none; font-weight: bold; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-danger { background: #ffe6e6; color: #e53e3e; border: 1px solid #e53e3e; }
        .alert-success { background: #e6fffa; color: #38b2ac; border: 1px solid #38b2ac; }
        .success-message { text-align: center; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join MediBuddy and take control of your health</p>
        </div>

        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success success-message">
                <strong>Registration Successful!</strong>
                <p>Your account has been created. Redirecting to login...</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>
        <?php else: ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="First name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Last name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="At least 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
                <button type="submit" class="btn-register">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
