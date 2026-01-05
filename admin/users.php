<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

// Delete user
if(isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id AND user_type='user'");
    setAlert("User deleted successfully", "success");
    redirect('users.php');
}

// Fetch all users
$result = mysqli_query($conn, "SELECT * FROM users WHERE user_type='user'");
?>
<?php include '../includes/header.php'; ?>
<div class="container">
<h2>Manage Users</h2>
<table class="table">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($user = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?php echo $user['user_id']; ?></td>
    <td><?php echo $user['full_name']; ?></td>
    <td><?php echo $user['email']; ?></td>
    <td>
        <a href="users.php?delete_user=<?php echo $user['user_id']; ?>" onclick="return confirmDelete('Delete this user?')" class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php include '../includes/footer.php'; ?>