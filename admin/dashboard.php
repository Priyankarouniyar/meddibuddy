<?php
require_once '../includes/header.php';
if(!isAdmin()) redirect('../auth/login.php');
?>
<h1>Admin Dashboard</h1>
<div class="dashboard-grid">
    <div class="card">
        <h3>Manage Users</h3>
        <p>View, edit, and manage all registered users.</p>
        <a href="manage-users.php" class="btn btn-primary">Go</a>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
