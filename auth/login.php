<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect('../user/dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
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
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>
    <?php if($error) echo "<p class='alert alert-danger'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Not registered? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
