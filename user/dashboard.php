<?php
require_once '../includes/header.php';
if(!isLoggedIn() || isAdmin()) redirect('../auth/login.php');
?>
<h1>Welcome, <?= $_SESSION['full_name'] ?></h1>
<div class="dashboard-grid">
    <div class="card">
        <h3>Family Members</h3>
        <p>Manage your family members and their information.</p>
        <a href="family-members.php" class="btn btn-primary">Go</a>
    </div>
    <div class="card">
        <h3>Medicine Reminders</h3>
        <p>Create and manage medicine reminders for your family members.</p>
        <a href="reminders.php" class="btn btn-primary">Go</a>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
