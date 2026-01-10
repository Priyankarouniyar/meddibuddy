<?php 
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/notification-engine.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// =========================
// Handle Add Reminder
// =========================
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

// =========================
// Fetch Family Members
// =========================
$members_query = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId' AND is_active=1 ORDER BY name");
$members_array = [];
if($members_query) {
    while($m = mysqli_fetch_assoc($members_query)) {
        $members_array[$m['id']] = $m['name'];
    }
}

// =========================
// Fetch Frequencies
// =========================
$frequencies_query = mysqli_query($conn, "SELECT * FROM frequencies ORDER BY times_per_day");
$frequencies_array = [];
if($frequencies_query) {
    while($f = mysqli_fetch_assoc($frequencies_query)) {
        $frequencies_array[$f['id']] = $f['name'];
    }
}

// =========================
// Fetch Prescription Medicines
// Corrected Query with Doctors Join
// =========================
$pm_sql = "SELECT 
            pm.id, 
            pm.dosage, 
            pm.unit, 
            m.name AS medicine_name, 
            d.first_name AS doctor_first_name, 
            d.last_name AS doctor_last_name,
            fm.name AS family_member_name, 
            fm.id AS family_member_id
          FROM prescription_medicine pm
          JOIN medicines m ON pm.medicine_id = m.id
          JOIN prescriptions p ON pm.prescription_id = p.id
          JOIN family_members fm ON p.family_member_id = fm.id
          JOIN doctors d ON p.doctor_id = d.id
          WHERE p.user_id='$userId' AND p.is_active=1
          ORDER BY fm.name, m.name";

$pm_result = mysqli_query($conn, $pm_sql);
$pm_array = [];
if($pm_result && mysqli_num_rows($pm_result) > 0) {
    while($pm = mysqli_fetch_assoc($pm_result)) {
        $pm_array[] = $pm;
    }
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
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .btn-sm { padding: 0.5rem 1rem; margin-right: 0.5rem; font-size: 0.9rem; }
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

    <!-- Add Reminder Button -->
    <button class="btn btn-primary" onclick="toggleReminderForm()">Add New Reminder</button>

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
                            <?= htmlspecialchars($pm['medicine_name']) ?> (<?= htmlspecialchars($pm['dosage']) ?><?= htmlspecialchars($pm['unit']) ?>) - For <?= htmlspecialchars($pm['family_member_name']) ?> - Prescribed by Dr. <?= htmlspecialchars($pm['doctor_first_name'].' '.$pm['doctor_last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

            <button type="submit" class="btn btn-primary">Create Reminder</button>
            <button type="button" class="btn btn-secondary" onclick="toggleReminderForm()">Cancel</button>
        </form>
    </div>
</main>

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
