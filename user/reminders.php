<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $member_id = sanitize($_POST['member_id']);
        $medicine = sanitize($_POST['medicine_name']);
        $dose_time = sanitize($_POST['dose_time']);
        $frequency = sanitize($_POST['frequency']);
        $notes = sanitize($_POST['notes']);

        $query = "INSERT INTO reminders (user_id, member_id, medicine_name, dose_time, frequency, notes)
                  VALUES ('$userId','$member_id','$medicine','$dose_time','$frequency','$notes')";
        mysqli_query($conn, $query);
        setAlert("Reminder added!", "success");
        redirect('reminders.php');
    }

    if ($_POST['action'] === 'delete') {
        $reminder_id = sanitize($_POST['reminder_id']);
        mysqli_query($conn, "DELETE FROM reminders WHERE reminder_id='$reminder_id' AND user_id='$userId'");
        setAlert("Reminder deleted.", "success");
        redirect('reminders.php');
    }
}

// Fetch reminders
$reminders = mysqli_query($conn, 
    "SELECT r.*, f.first_name, f.last_name 
     FROM reminders r
     JOIN family_members f ON r.member_id=f.member_id
     WHERE r.user_id='$userId'");
$family_members = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId'");
?>
<?php include '../includes/header.php'; ?>
<h2>Medicine Reminders</h2>
<button class="btn btn-primary" onclick="openModal('addReminderModal')">Add Reminder</button>

<?php if(mysqli_num_rows($reminders) > 0): ?>
<table class="table">
    <thead>
        <tr>
            <th>Member</th><th>Medicine</th><th>Dose Time</th><th>Frequency</th><th>Notes</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($r = mysqli_fetch_assoc($reminders)): ?>
        <tr>
            <td><?= $r['first_name'].' '.$r['last_name'] ?></td>
            <td><?= $r['medicine_name'] ?></td>
            <td><?= $r['dose_time'] ?></td>
            <td><?= $r['frequency'] ?></td>
            <td><?= $r['notes'] ?: '-' ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('Delete?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="reminder_id" value="<?= $r['reminder_id'] ?>">
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>No reminders added yet.</p>
<?php endif; ?>

<!-- Add Reminder Modal -->
<div id="addReminderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addReminderModal')">&times;</span>
        <h3>Add Medicine Reminder</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <select name="member_id" required>
                <option value="">Select Family Member</option>
                <?php while($f = mysqli_fetch_assoc($family_members)): ?>
                    <option value="<?= $f['member_id'] ?>"><?= $f['first_name'].' '.$f['last_name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="medicine_name" placeholder="Medicine Name" required>
            <input type="time" name="dose_time" required>
            <input type="text" name="frequency" placeholder="Frequency (e.g., Daily)">
            <textarea name="notes" placeholder="Notes"></textarea>
            <button class="btn btn-primary">Add Reminder</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
