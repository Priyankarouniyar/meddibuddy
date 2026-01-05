<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/notification-engine.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Add Reminder
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_reminder') {
    $prescription_medicine_id = intval($_POST['prescription_medicine_id']);
    $family_member_id = intval($_POST['family_member_id']);
    $frequency_id = intval($_POST['frequency_id']);
    $reminder_time = $_POST['reminder_time'];
    $reminder_days = isset($_POST['reminder_days']) ? implode(',', $_POST['reminder_days']) : '';

    if(!$prescription_medicine_id || !$frequency_id || empty($reminder_time)) {
        $error = "Please fill in all required fields.";
    } else {
        $sql = "INSERT INTO reminders (prescription_medicine_id, family_member_id, frequency_id, reminder_time, reminder_days, status_id) 
                VALUES ('$prescription_medicine_id', '$family_member_id', '$frequency_id', '$reminder_time', '$reminder_days', 1)";
        if(mysqli_query($conn, $sql)) {
            $success = "Reminder created successfully!";
        } else {
            $error = "Error creating reminder: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Reminder
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $reminder_id = intval($_GET['id']);
    $sql = "DELETE FROM reminders WHERE id='$reminder_id'";
    if(mysqli_query($conn, $sql)) {
        $success = "Reminder deleted successfully!";
    } else {
        $error = "Error deleting reminder.";
    }
}

// Handle Update Reminder Status
if(isset($_GET['mark']) && isset($_GET['id'])) {
    $reminder_id = intval($_GET['id']);
    $status = $_GET['mark'] === 'taken' ? 3 : 1; // 3 = Completed, 1 = Active
    $sql = "UPDATE reminders SET status_id='$status' WHERE id='$reminder_id'";
    if(mysqli_query($conn, $sql)) {
        $success = "Reminder status updated!";
    }
}

if(isset($_GET['send_notification']) && isset($_GET['id'])) {
    $reminder_id = intval($_GET['id']);
    
    // Get reminder details with proper error handling
    $reminder_query = mysqli_query($conn, 
        "SELECT r.*, pm.dosage, pm.unit, m.name as medicine_name, fm.name as family_member_name, p.user_id
         FROM reminders r
         JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
         JOIN medicines m ON pm.medicine_id = m.id
         JOIN family_members fm ON r.family_member_id = fm.id
         JOIN prescriptions p ON pm.prescription_id = p.id
         WHERE r.id = '$reminder_id'");
    
    $reminder = $reminder_query ? mysqli_fetch_assoc($reminder_query) : null;
    
    if($reminder && $reminder['user_id'] == $userId) {
        // Get user notification settings with proper error handling
        $settings_query = mysqli_query($conn, "SELECT * FROM notification_settings WHERE user_id='$userId'");
        $settings = ($settings_query && mysqli_num_rows($settings_query) > 0) 
                    ? mysqli_fetch_assoc($settings_query) 
                    : ['email_enabled' => 1, 'sms_enabled' => 0, 'sms_number' => ''];
        
        // Initialize notification engine
        $notificationEngine = new NotificationEngine($conn);
        
        // Send notifications
        if($settings['email_enabled']) {
            $notificationEngine->sendEmailNotification(
                $reminder_id,
                $userId,
                $_SESSION['email'],
                $reminder['medicine_name'],
                $reminder['family_member_name'],
                $reminder['dosage'],
                $reminder['unit'],
                $reminder['reminder_time']
            );
            $success = "Notification sent successfully!";
        } else {
            $error = "Email notifications are not enabled. Please enable them in settings.";
        }
    } else {
        $error = "Reminder not found or access denied.";
    }
}

// Fetch family members with error handling
$members_query = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId' AND is_active=1 ORDER BY name");
$members_array = [];
if($members_query) {
    while($m = mysqli_fetch_assoc($members_query)) {
        $members_array[$m['id']] = $m['name'];
    }
}

// Fetch frequencies with error handling
$frequencies_query = mysqli_query($conn, "SELECT * FROM frequencies ORDER BY times_per_day");
$frequencies_array = [];
if($frequencies_query) {
    while($f = mysqli_fetch_assoc($frequencies_query)) {
        $frequencies_array[$f['id']] = $f['name'];
    }
}

// Fetch prescription medicines for the user's family members with better error handling
$pm_sql = "SELECT pm.id, pm.dosage, pm.unit, m.name as medicine_name, p.doctor_name, fm.name as family_member_name, fm.id as family_member_id
           FROM prescription_medicine pm
           JOIN medicines m ON pm.medicine_id = m.id
           JOIN prescriptions p ON pm.prescription_id = p.id
           JOIN family_members fm ON p.family_member_id = fm.id
           WHERE p.user_id = '$userId' AND p.is_active = 1
           ORDER BY fm.name, m.name";
$pm_result = mysqli_query($conn, $pm_sql);
$pm_array = [];
if($pm_result && mysqli_num_rows($pm_result) > 0) {
    while($pm = mysqli_fetch_assoc($pm_result)) {
        $pm_array[] = $pm;
    }
}

// Fetch reminders with error handling
$reminders_sql = "SELECT r.*, pm.dosage, pm.unit, m.name as medicine_name, fm.name as family_member_name, f.name as frequency_name, s.name as status_name
                 FROM reminders r
                 JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
                 JOIN medicines m ON pm.medicine_id = m.id
                 JOIN family_members fm ON r.family_member_id = fm.id
                 JOIN frequencies f ON r.frequency_id = f.id
                 JOIN statuses s ON r.status_id = s.id
                 ORDER BY r.reminder_time ASC, fm.name";
$reminders = mysqli_query($conn, $reminders_sql);
if(!$reminders) {
    $error = "Error loading reminders: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Reminders - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .reminder-form { display: none; margin-top: 1rem; padding: 1.5rem; background: #f5f5f5; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .reminder-card { background: white; padding: 1.5rem; margin: 1rem 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 5px solid #667eea; }
        .reminder-card.completed { border-left-color: #28a745; opacity: 0.7; }
        .reminder-info { color: #666; font-size: 0.9rem; margin: 0.3rem 0; }
        .reminder-time { font-size: 1.3rem; font-weight: bold; color: #667eea; }
        .medicine-name { font-size: 1.1rem; font-weight: bold; color: #333; }
        .action-buttons { margin-top: 1rem; }
        .btn-sm { padding: 0.5rem 1rem; margin-right: 0.5rem; font-size: 0.9rem; }
        .checkbox-group { margin: 1rem 0; }
        .checkbox-group label { display: inline-block; margin-right: 1rem; font-weight: normal; }
        .status-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-active { background: #e7f3ff; color: #0056b3; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-paused { background: #fff3e0; color: #e65100; }
        .no-medicines-warning { background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Medicine Reminders</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Added warning message if no medicines are available -->
    <?php if(count($pm_array) === 0): ?>
        <div class="no-medicines-warning">
            <strong>⚠️ No medicines available</strong><br>
            To create a reminder, you first need to:
            <ol>
                <li>Go to <strong>Manage Prescriptions</strong></li>
                <li>Create a new prescription for a family member</li>
                <li>Add medicines to that prescription</li>
                <li>Then come back here to set reminders</li>
            </ol>
        </div>
    <?php endif; ?>

    <!-- Add Reminder Button -->
    <button class="btn btn-primary" onclick="toggleReminderForm()" style="margin-bottom: 2rem;">Add New Reminder</button>

    <!-- Add Reminder Form -->
    <div class="reminder-form card" id="add-reminder-form">
        <h3>Create New Reminder</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_reminder">
            
            <div class="form-group">
                <label>Select Medicine *</label>
                <select name="prescription_medicine_id" id="medicine_select" required onchange="updateMemberInfo()">
                    <option value="">-- Choose a medicine from your prescriptions --</option>
                    <?php foreach($pm_array as $pm): ?>
                        <option value="<?= $pm['id'] ?>" data-member-id="<?= $pm['family_member_id'] ?>">
                            <?= htmlspecialchars($pm['medicine_name']) ?> (<?= htmlspecialchars($pm['dosage']) ?><?= htmlspecialchars($pm['unit']) ?>) - For <?= htmlspecialchars($pm['family_member_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #999;">If no medicines appear, you need to create a prescription first with medicines added to it.</small>
            </div>

            <div class="form-group">
                <label>Family Member *</label>
                <select name="family_member_id" id="family_member_id" required>
                    <option value="">Select Family Member</option>
                    <?php foreach($members_array as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Reminder Time *</label>
                    <input type="time" name="reminder_time" required>
                </div>
                <div class="form-group">
                    <label>Frequency *</label>
                    <select name="frequency_id" required>
                        <option value="">Select Frequency</option>
                        <?php foreach($frequencies_array as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Days to Remind (Optional)</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="reminder_days" value="Monday"> Monday</label>
                    <label><input type="checkbox" name="reminder_days" value="Tuesday"> Tuesday</label>
                    <label><input type="checkbox" name="reminder_days" value="Wednesday"> Wednesday</label>
                    <label><input type="checkbox" name="reminder_days" value="Thursday"> Thursday</label>
                    <label><input type="checkbox" name="reminder_days" value="Friday"> Friday</label>
                    <label><input type="checkbox" name="reminder_days" value="Saturday"> Saturday</label>
                    <label><input type="checkbox" name="reminder_days" value="Sunday"> Sunday</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Reminder</button>
            <button type="button" class="btn btn-secondary" onclick="toggleReminderForm()">Cancel</button>
        </form>
    </div>

    <!-- Reminders List -->
    <h3 style="margin-top: 2rem;">Your Reminders</h3>
    <?php 
    if(mysqli_num_rows($reminders) === 0):
    ?>
        <p style="color: #999;">No reminders set. Create one above to get started!</p>
    <?php 
    else:
        while($reminder = mysqli_fetch_assoc($reminders)):
            $completed = $reminder['status_name'] === 'Completed' ? ' completed' : '';
    ?>
        <div class="reminder-card<?= $completed ?>">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div class="medicine-name"><?= htmlspecialchars($reminder['medicine_name']) ?></div>
                    <div class="reminder-time"><?= substr($reminder['reminder_time'], 0, 5) ?></div>
                    <div class="reminder-info">
                        <strong>For:</strong> <?= htmlspecialchars($reminder['family_member_name']) ?>
                    </div>
                    <div class="reminder-info">
                        <strong>Dosage:</strong> <?= htmlspecialchars($reminder['dosage']) ?><?= htmlspecialchars($reminder['unit']) ?>
                    </div>
                    <div class="reminder-info">
                        <strong>Frequency:</strong> <?= htmlspecialchars($reminder['frequency_name']) ?>
                    </div>
                    <?php if($reminder['reminder_days']): ?>
                        <div class="reminder-info">
                            <strong>Days:</strong> <?= htmlspecialchars($reminder['reminder_days']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '_', $reminder['status_name'])) ?>">
                        <?= htmlspecialchars($reminder['status_name']) ?>
                    </span>
                </div>
            </div>

            <div class="action-buttons">
                <!-- Add button to manually send notification -->
                <a href="?send_notification=1&id=<?= $reminder['id'] ?>" class="btn btn-sm" style="background: #667eea; color: white;" title="Send notification now">Send Notification</a>
                
                <?php if($reminder['status_name'] !== 'Completed'): ?>
                    <a href="?mark=taken&id=<?= $reminder['id'] ?>" class="btn btn-sm" style="background: #28a745; color: white;">Mark as Taken</a>
                <?php endif; ?>
                <a href="?delete=1&id=<?= $reminder['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white;" onclick="return confirm('Delete this reminder?')">Delete</a>
            </div>
        </div>
    <?php 
        endwhile;
    endif;
    ?>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function toggleReminderForm() {
    const form = document.getElementById('add-reminder-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function updateMemberInfo() {
    const select = document.getElementById('medicine_select');
    const selected = select.options[select.selectedIndex];
    const memberId = selected.getAttribute('data-member-id');
    if(memberId) {
        document.getElementById('family_member_id').value = memberId;
    }
}
</script>
</body>
</html>
