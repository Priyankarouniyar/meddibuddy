<?php
require_once '../includes/header.php';
if(!isLoggedIn() || isAdmin()) redirect('../auth/login.php');

// Fetch statistics for the user
$userId = $_SESSION['user_id'];

$family_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM family_members WHERE user_id='$userId' AND is_active=1"))['count'];
$prescriptions_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM prescriptions WHERE user_id='$userId' AND is_active=1"))['count'];
$reminders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reminders WHERE is_active=1"))['count'];

// Fetch upcoming reminders
$upcoming_reminders = mysqli_query($conn, "SELECT r.*, m.name as medicine_name, fm.name as family_member_name, f.name as frequency_name
                                           FROM reminders r
                                           JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
                                           JOIN medicines m ON pm.medicine_id = m.id
                                           JOIN family_members fm ON r.family_member_id = fm.id
                                           JOIN frequencies f ON r.frequency_id = f.id
                                           WHERE r.is_active=1 AND r.status_id=1
                                           ORDER BY r.reminder_time ASC
                                           LIMIT 5");
?>
<style>
    .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-box { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #667eea; text-align: center; }
    .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
    .stat-label { color: #999; font-size: 0.9rem; margin-top: 0.5rem; }
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
    .quick-action { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; transition: 0.3s; }
    .quick-action:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .quick-action h4 { margin: 0 0 0.5rem 0; color: #333; }
    .quick-action p { color: #999; margin: 0.5rem 0; font-size: 0.9rem; }
    .upcoming-box { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem; }
    .reminder-item { padding: 1rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .reminder-item:last-child { border-bottom: none; }
    .reminder-time { font-weight: bold; color: #667eea; font-size: 1.1rem; }
</style>

<main class="main-content">
    <h2>Dashboard</h2>
    <p style="color: #999; margin-bottom: 2rem;">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>

    <!-- Quick Statistics -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="stat-number"><?= $family_count ?></div>
            <div class="stat-label">Family Members</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?= $prescriptions_count ?></div>
            <div class="stat-label">Prescriptions</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?= $reminders_count ?></div>
            <div class="stat-label">Active Reminders</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h3>Quick Actions</h3>
    <div class="dashboard-grid">
        <div class="quick-action">
            <h4>👨‍👩‍👧‍👦 Family Members</h4>
            <p>Manage your family members and their medical information.</p>
            <a href="family-members.php" class="btn btn-primary" style="margin-top: 1rem;">Manage</a>
        </div>
        <div class="quick-action">
            <h4>📋 Prescriptions</h4>
            <p>Create and manage medical prescriptions with ease.</p>
            <a href="prescriptions.php" class="btn btn-primary" style="margin-top: 1rem;">View</a>
        </div>
        <div class="quick-action">
            <h4>⏰ Reminders</h4>
            <p>Set up and track medicine reminders for your family.</p>
            <a href="reminders.php" class="btn btn-primary" style="margin-top: 1rem;">Configure</a>
        </div>
    </div>

    <!-- Upcoming Reminders -->
    <div class="upcoming-box">
        <h3>Upcoming Reminders</h3>
        <?php 
        if(mysqli_num_rows($upcoming_reminders) === 0):
            echo "<p style='color: #999; text-align: center; padding: 2rem;'>No upcoming reminders. Create one to get started!</p>";
        else:
            while($reminder = mysqli_fetch_assoc($upcoming_reminders)):
        ?>
            <div class="reminder-item">
                <div>
                    <div style="font-weight: bold; margin-bottom: 0.3rem;"><?= htmlspecialchars($reminder['medicine_name']) ?></div>
                    <div style="color: #999; font-size: 0.9rem;">for <?= htmlspecialchars($reminder['family_member_name']) ?> - <?= htmlspecialchars($reminder['frequency_name']) ?></div>
                </div>
                <div class="reminder-time"><?= substr($reminder['reminder_time'], 0, 5) ?></div>
            </div>
        <?php 
            endwhile;
        endif;
        ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
