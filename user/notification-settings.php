<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Settings Update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    $email_enabled = isset($_POST['email_enabled']) ? 1 : 0;
    $sms_enabled = isset($_POST['sms_enabled']) ? 1 : 0;
    $sms_number = sanitize($_POST['sms_number'] ?? '');
    $notification_minutes_before = intval($_POST['notification_minutes_before'] ?? 15);

    if($sms_enabled && empty($sms_number)) {
        $error = "SMS number is required when SMS notifications are enabled.";
    } else {
        // Check if settings exist
        $check = mysqli_query($conn, "SELECT id FROM notification_settings WHERE user_id='$userId'");
        
        if(mysqli_num_rows($check) > 0) {
            // Update existing
            $sql = "UPDATE notification_settings SET 
                    email_enabled='$email_enabled', 
                    sms_enabled='$sms_enabled', 
                    sms_number='$sms_number',
                    notification_minutes_before='$notification_minutes_before'
                    WHERE user_id='$userId'";
        } else {
            // Insert new
            $sql = "INSERT INTO notification_settings (user_id, email_enabled, sms_enabled, sms_number, notification_minutes_before)
                    VALUES ('$userId', '$email_enabled', '$sms_enabled', '$sms_number', '$notification_minutes_before')";
        }
        
        if(mysqli_query($conn, $sql)) {
            $success = "Notification settings updated successfully!";
        } else {
            $error = "Error updating settings: " . mysqli_error($conn);
        }
    }
}

// Fetch current settings
$settings_result = mysqli_query($conn, "SELECT * FROM notification_settings WHERE user_id='$userId'");
$settings = mysqli_fetch_assoc($settings_result) ?? [
    'email_enabled' => 1,
    'sms_enabled' => 0,
    'sms_number' => '',
    'notification_minutes_before' => 15
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-container { max-width: 600px; margin: 0 auto; }
        .settings-section { background: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .settings-section h3 { margin-top: 0; color: #333; border-bottom: 2px solid #667eea; padding-bottom: 1rem; }
        .setting-item { display: flex; align-items: center; margin: 1.5rem 0; padding-bottom: 1.5rem; border-bottom: 1px solid #eee; }
        .setting-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .setting-toggle { margin-right: 1rem; }
        .toggle-switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
        input:checked + .slider { background-color: #667eea; }
        input:checked + .slider:before { transform: translateX(26px); }
        .setting-info { flex: 1; }
        .setting-info label { display: block; font-weight: bold; margin-bottom: 0.3rem; }
        .setting-info small { color: #999; display: block; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .info-box { background: #f0f4ff; border-left: 4px solid #667eea; padding: 1rem; margin: 1rem 0; border-radius: 4px; }
        .info-box strong { color: #667eea; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Notification Settings</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <div class="settings-container">
        <!-- Email Notifications -->
        <div class="settings-section">
            <h3>Email Notifications</h3>
            <div class="info-box">
                <strong>Reminder:</strong> You will receive email notifications when it's time to take medicine. Make sure your email address is verified.
            </div>
            <p>Your email: <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
        </div>

        <!-- SMS Notifications -->
        <div class="settings-section">
            <h3>SMS Notifications</h3>
            <div class="info-box">
                <strong>Note:</strong> SMS notifications require a valid phone number. Standard SMS rates may apply depending on your carrier.
            </div>
        </div>

        <!-- Settings Form -->
        <form method="POST" class="settings-container">
            <input type="hidden" name="action" value="update_settings">
            
            <div class="settings-section">
                <h3>Configure Notifications</h3>

                <!-- Email Toggle -->
                <div class="setting-item">
                    <div class="setting-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_enabled" <?= $settings['email_enabled'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-info">
                        <label>Email Notifications</label>
                        <small>Receive medicine reminders via email</small>
                    </div>
                </div>

                <!-- SMS Toggle -->
                <div class="setting-item">
                    <div class="setting-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="sms_enabled" onchange="toggleSmsFields()">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-info">
                        <label>SMS Notifications</label>
                        <small>Receive medicine reminders via SMS (requires gateway setup)</small>
                    </div>
                </div>

                <!-- SMS Number Input -->
                <div id="sms-section" style="display: <?= $settings['sms_enabled'] ? 'block' : 'none' ?>; margin-left: 0; border: none; padding: 0; background: transparent;">
                    <div class="form-group">
                        <label>SMS Phone Number</label>
                        <input type="tel" name="sms_number" placeholder="e.g., +1234567890" value="<?= htmlspecialchars($settings['sms_number'] ?? '') ?>">
                        <small>Include country code (e.g., +1 for USA, +44 for UK)</small>
                    </div>
                </div>

                <!-- Notification Timing -->
                <div class="form-group">
                    <label>Send Notifications</label>
                    <select name="notification_minutes_before">
                        <option value="5" <?= $settings['notification_minutes_before'] == 5 ? 'selected' : '' ?>>5 minutes before</option>
                        <option value="10" <?= $settings['notification_minutes_before'] == 10 ? 'selected' : '' ?>>10 minutes before</option>
                        <option value="15" <?= $settings['notification_minutes_before'] == 15 ? 'selected' : '' ?>>15 minutes before</option>
                        <option value="30" <?= $settings['notification_minutes_before'] == 30 ? 'selected' : '' ?>>30 minutes before</option>
                        <option value="60" <?= $settings['notification_minutes_before'] == 60 ? 'selected' : '' ?>>1 hour before</option>
                    </select>
                    <small>How soon before the reminder time should you be notified?</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">Save Settings</button>
            </div>
        </form>

        <!-- SMS Gateway Setup Info -->
        <div class="settings-section">
            <h3>SMS Gateway Setup (Optional)</h3>
            <div class="info-box">
                <strong>How to enable SMS:</strong>
                <ol style="margin: 1rem 0 0 1rem;">
                    <li>Get an SMS gateway account (Twilio, Nexmo, or similar)</li>
                    <li>Contact admin to configure your API credentials</li>
                    <li>Once configured, SMS notifications will work automatically</li>
                </ol>
            </div>
            <p style="color: #999; font-size: 0.9rem; margin-top: 1rem;">Currently: <strong>SMS Gateway Not Configured</strong></p>
        </div>

        <!-- View Notification Logs -->
        <div style="text-align: center; margin: 2rem 0;">
            <a href="notification-history.php" class="btn btn-secondary">View Notification History</a>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function toggleSmsFields() {
    const smsSection = document.getElementById('sms-section');
    const smsCheckbox = document.querySelector('input[name="sms_enabled"]');
    smsSection.style.display = smsCheckbox.checked ? 'block' : 'none';
}
</script>
</body>
</html>
