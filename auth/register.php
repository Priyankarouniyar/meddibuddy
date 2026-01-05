<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect('../user/dashboard.php');

$error = '';
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
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first','$last','$email','$hash')";
        if (mysqli_query($conn, $sql)) {
            setAlert("Registration successful! Login now.", "success");
            redirect('login.php');
        } else {
            $error = "Email already exists or database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register</h2>
        <?php if($error) echo "<p class='alert alert-danger'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
