<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND is_active=1");
        if (mysqli_num_rows($res) == 1) {
            $user = mysqli_fetch_assoc($res);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_type'] = $user['user_type'];

                if($user['user_type'] === 'admin'){
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../user/dashboard.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .auth-container { background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 450px; width: 100%; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #667eea; font-size: 2rem; margin: 0 0 0.5rem 0; }
        .auth-header p { color: #999; margin: 0; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 0.9rem; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1rem; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-group input::placeholder { color: #ccc; }
        .btn-login { width: 100%; padding: 1rem; background: #667eea; color: white; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { background: #5a67d8; }
        .auth-footer { text-align: center; margin-top: 1.5rem; color: #666; }
        .auth-footer a { color: #667eea; text-decoration: none; font-weight: bold; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-danger { background: #ffe6e6; color: #e53e3e; border: 1px solid #e53e3e; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Login to your MediBuddy account</p>
        </div>

        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p style="margin-top: 1rem; font-size: 0.9rem;">
                <!-- Demo Credentials:<br>
                Email: <code style="background: #f5f5f5; padding: 0.2rem 0.5rem;">admin@medibuddy.com</code><br>
                Password: <code style="background: #f5f5f5; padding: 0.2rem 0.5rem;">admin123</code> -->
            </p>
        </div>
    </div>
</body>
</html>
