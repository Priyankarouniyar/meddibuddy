<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$success = '';
$error = '';

// Handle Toggle Active Status
if(isset($_GET['toggle']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $sql = "UPDATE users SET is_active = NOT is_active WHERE id='$user_id' AND user_type='user'";
    if(mysqli_query($conn, $sql)) {
        $success = "User status updated!";
    } else {
        $error = "Error updating user status.";
    }
}

// Handle Delete User
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    // Delete all related data first
    $family_members = mysqli_query($conn, "SELECT id FROM family_members WHERE user_id='$user_id'");
    while($fm = mysqli_fetch_assoc($family_members)) {
        mysqli_query($conn, "DELETE FROM reminders WHERE family_member_id='{$fm['id']}'");
    }
    mysqli_query($conn, "DELETE FROM family_members WHERE user_id='$user_id'");
    mysqli_query($conn, "DELETE FROM prescriptions WHERE user_id='$user_id'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$user_id' AND user_type='user'");
    $success = "User and all related data deleted!";
}

// Fetch all regular users
$users = mysqli_query($conn, "SELECT * FROM users WHERE user_type='user' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table thead { background: #f5f5f5; }
        .user-table th, .user-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .user-table th { font-weight: bold; border-bottom: 2px solid #ddd; }
        .user-table tbody tr:hover { background: #f9f9f9; }
        .btn-xs { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
        .badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; }
        .badge-active { background: #e8f5e9; color: #2e7d32; }
        .badge-inactive { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Manage Users</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <div class="card">
        <?php 
        if(mysqli_num_rows($users) === 0):
        ?>
            <p>No users registered yet.</p>
        <?php 
        else:
        ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Family Members</th>
                        <th>Prescriptions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users)): 
                        // Count family members
                        $fm_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM family_members WHERE user_id='{$user['id']}'"))['count'];
                        
                        // Count prescriptions
                        $pres_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM prescriptions WHERE user_id='{$user['id']}'"))['count'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                        <td><?= $fm_count ?></td>
                        <td><?= $pres_count ?></td>
                        <td>
                            <span class="badge <?= $user['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?toggle=1&id=<?= $user['id'] ?>" class="btn btn-secondary btn-xs">
                                <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                            <a href="?delete=1&id=<?= $user['id'] ?>" class="btn btn-xs" style="background: #dc3545; color: white;" onclick="return confirm('Delete user and all related data?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
