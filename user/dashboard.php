<?php
require_once '../includes/header.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$userId = $_SESSION['user_id'];

/* ---------- STATISTICS ---------- */
$family_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS count FROM family_members WHERE user_id='$userId' AND is_active=1")
)['count'];

$prescriptions_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS count FROM prescriptions WHERE user_id='$userId' AND is_active=1")
)['count'];

$reminders_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS count FROM reminders WHERE is_active=1")
)['count'];

/* ---------- UPCOMING REMINDERS ---------- */
$upcoming_reminders = mysqli_query($conn, "
    SELECT r.*, m.name AS medicine_name, fm.name AS family_member_name, f.name AS frequency_name
    FROM reminders r
    JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
    JOIN medicines m ON pm.medicine_id = m.id
    JOIN family_members fm ON r.family_member_id = fm.id
    JOIN frequencies f ON r.frequency_id = f.id
    WHERE r.is_active=1 AND r.status_id=1
    ORDER BY r.reminder_time ASC
    LIMIT 5
");
?>

<style>
/* ---------- DASHBOARD ---------- */

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: #fff;
    padding: 1.8rem;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    border-top: 4px solid #667eea;
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
}

.stat-label {
    margin-top: 0.5rem;
    font-size: 1.2rem;
    color: #555;
}

/* ---------- QUICK ACTIONS ---------- */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}

.quick-action {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    text-align: center;
    transition: 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.quick-action:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.quick-action h4 {
    font-size: 1.6rem;
    margin-bottom: 0.6rem;
    color: #333;
}

.quick-action p {
    color: #666;
    font-size: 1rem;
}

/* ---------- BUTTONS ---------- */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1.6rem;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-primary {
    background: #667eea;
    color: #fff;
}

.btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102,126,234,0.4);
}

.btn-action {
    margin-top: 1.2rem;
}

/* ---------- UPCOMING REMINDERS ---------- */
.upcoming-box {
    background: white;
    padding: 1.8rem;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    margin-top: 2.5rem;
}

.reminder-item {
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reminder-item:last-child {
    border-bottom: none;
}

.reminder-time {
    font-weight: bold;
    color: #667eea;
    font-size: 1.1rem;
}
</style>

<main class="main-content">

    <h2 style="font-size:3rem; color:#333;">Dashboard</h2>
    <p style="font-size:1.5rem; color:#555; margin-bottom:2rem;">
        Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!
    </p>

    <!-- STATISTICS -->
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

    <!-- QUICK ACTIONS -->
    <h3>Quick Actions</h3>
    <div class="dashboard-grid">

        <div class="quick-action">
            <h4>👨‍👩‍👧‍👦 Family Members</h4>
            <p style="color:black; font-weight: 100;;">Manage your family medical profiles.</p>
            <a href="family-members.php" class="btn btn-primary btn-action">Manage</a>
        </div>

        <div class="quick-action">
            <h4>📋 Prescriptions</h4>
            <p>Create and manage prescriptions.</p>
            <a href="prescriptions.php" class="btn btn-primary btn-action">View</a>
        </div>

        <div class="quick-action">
            <h4>⏰ Reminders</h4>
            <p>Set medicine reminders.</p>
            <a href="reminders.php" class="btn btn-primary btn-action">Configure</a>
        </div>

    </div>

    <!-- UPCOMING REMINDERS -->
    <div class="upcoming-box">
        <h3>Upcoming Reminders</h3>

        <?php if (mysqli_num_rows($upcoming_reminders) === 0): ?>
            <p style="text-align:center; color:#999; padding:1.5rem;">
                No upcoming reminders.
            </p>
        <?php else: ?>
            <?php while ($r = mysqli_fetch_assoc($upcoming_reminders)): ?>
                <div class="reminder-item">
                    <div>
                        <strong><?= htmlspecialchars($r['medicine_name']) ?></strong><br>
                        <small><?= htmlspecialchars($r['family_member_name']) ?> • <?= htmlspecialchars($r['frequency_name']) ?></small>
                    </div>
                    <div class="reminder-time"><?= substr($r['reminder_time'], 0, 5) ?></div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
