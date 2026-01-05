<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isAdmin()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $query = "SELECT * FROM users WHERE email='$email' AND user_type='admin' AND is_active=1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['user_id'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['user_type'] = $admin['user_type'];

                setAlert("Welcome back, {$admin['full_name']}!", "success");
                redirect('dashboard.php');
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - MediBuddy</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/main.js"></script>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Admin Login</h2>
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateLoginForm();">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
    </div>
</div>
</body>
</html>