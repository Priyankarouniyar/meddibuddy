<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #667eea; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; margin-top: 0.5rem; }
        .admin-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .menu-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .menu-card a { text-decoration: none; display: block; color: #667eea; font-weight: bold; }
        .menu-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-3px); transition: 0.3s; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Admin Dashboard</h2>
    <p style="color: #666;">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>

    <!-- Statistics -->
    <h3>Quick Statistics</h3>
    <div class="stats-grid">
        <?php
        // Total Users
        $users_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE user_type='user'");
        $users = mysqli_fetch_assoc($users_result)['count'];

        // Total Family Members
        $members_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM family_members WHERE is_active=1");
        $members = mysqli_fetch_assoc($members_result)['count'];

        // Total Prescriptions
        $prescriptions_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM prescriptions WHERE is_active=1");
        $prescriptions = mysqli_fetch_assoc($prescriptions_result)['count'];

        // Total Reminders
        $reminders_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM reminders WHERE is_active=1");
        $reminders = mysqli_fetch_assoc($reminders_result)['count'];
        ?>
        <div class="stat-card">
            <div class="stat-number"><?= $users ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $members ?></div>
            <div class="stat-label">Family Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $prescriptions ?></div>
            <div class="stat-label">Prescriptions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $reminders ?></div>
            <div class="stat-label">Active Reminders</div>
        </div>
    </div>

    <!-- Admin Menu -->
    <h3>Admin Management</h3>
    <div class="admin-menu">
        <div class="menu-card">
            <a href="users.php">Manage Users</a>
            <p style="color: #999; font-size: 0.9rem; margin: 0.5rem 0 0 0;">View and manage users</p>
        </div>
        <div class="menu-card">
            <a href="medicines.php">Manage Medicines</a>
            <p style="color: #999; font-size: 0.9rem; margin: 0.5rem 0 0 0;">Add and update medicines</p>
        </div>
        <div class="menu-card">
            <a href="manufacturers.php">Manage Manufacturers</a>
            <p style="color: #999; font-size: 0.9rem; margin: 0.5rem 0 0 0;">Pharmaceutical companies</p>
        </div>
        <div class="menu-card">
            <a href="verify-medicines.php">Verify Medicines</a>
            <p style="color: #999; font-size: 0.9rem; margin: 0.5rem 0 0 0;">Quality control</p>
        </div>
    </div>

    <!-- Recent Users -->
    <h3>Recent Users</h3>
    <div class="card">
        <?php
        $recent_users = mysqli_query($conn, "SELECT * FROM users WHERE user_type='user' ORDER BY created_at DESC LIMIT 5");
        $count = mysqli_num_rows($recent_users);
        
        if($count === 0) {
            echo "<p>No users yet.</p>";
        } else {
            ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 0.8rem; text-align: left;">Name</th>
                        <th style="padding: 0.8rem; text-align: left;">Email</th>
                        <th style="padding: 0.8rem; text-align: left;">Registered</th>
                        <th style="padding: 0.8rem; text-align: left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.8rem;"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td style="padding: 0.8rem;"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="padding: 0.8rem;"><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                        <td style="padding: 0.8rem;">
                            <span style="background: <?= $user['is_active'] ? '#e8f5e9' : '#ffebee' ?>; color: <?= $user['is_active'] ? '#2e7d32' : '#c62828' ?>; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem;">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
