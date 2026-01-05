<?php
/**
 * Test Cron Job Page
 * Allows manual triggering of reminder notifications for testing
 */

require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/notification-engine.php';

requireLogin();
requireAdmin();

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if($_POST['action'] === 'trigger_cron') {
        // Execute the cron job manually
        exec('php ../cron/send_reminders.php', $output, $returnCode);
        
        if($returnCode === 0) {
            $success = "Cron job executed successfully!";
        } else {
            $error = "Cron job execution failed.";
        }
    } elseif($_POST['action'] === 'test_email') {
        $userId = intval($_POST['test_user_id']);
        $userResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$userId'"));
        
        if($userResult) {
            $engine = new NotificationEngine($conn);
            $sent = $engine->sendEmailNotification(
                0,
                $userId,
                $userResult['email'],
                'Test Medicine',
                'Test Family Member',
                '500',
                'mg',
                date('H:i')
            );
            
            $success = $sent ? "Test email sent to {$userResult['email']}" : "Failed to send test email";
        } else {
            $error = "User not found";
        }
    }
}

// Get recent notification logs
$logs = mysqli_query($conn, "SELECT nl.*, u.full_name, m.name as medicine_name 
                            FROM notification_logs nl
                            LEFT JOIN users u ON nl.user_id = u.id
                            LEFT JOIN prescription_medicine pm ON (SELECT prescription_medicine_id FROM reminders WHERE id = nl.reminder_id)
                            LEFT JOIN medicines m ON pm.medicine_id = m.id
                            ORDER BY nl.created_at DESC LIMIT 20");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cron Job Test - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-section { background: white; padding: 2rem; margin: 1rem 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .log-entry { background: #f5f5f5; padding: 1rem; margin: 0.5rem 0; border-radius: 4px; border-left: 4px solid #667eea; }
        .log-entry.sent { border-left-color: #28a745; }
        .log-entry.failed { border-left-color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>

<main class="main-content">
    <h2>Notification System - Admin Test Panel</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Manual Cron Trigger -->
    <div class="admin-section">
        <h3>Trigger Cron Job Manually</h3>
        <p>Click the button below to execute the reminder notification job immediately.</p>
        <form method="POST">
            <input type="hidden" name="action" value="trigger_cron">
            <button type="submit" class="btn btn-primary">Run Cron Job Now</button>
        </form>
    </div>

    <!-- Send Test Email -->
    <div class="admin-section">
        <h3>Send Test Email</h3>
        <form method="POST">
            <input type="hidden" name="action" value="test_email">
            <div class="form-group">
                <label>Select User</label>
                <select name="test_user_id" required>
                    <option>-- Select User --</option>
                    <?php 
                    $users = mysqli_query($conn, "SELECT id, full_name, email FROM users WHERE user_type='user' LIMIT 10");
                    while($user = mysqli_fetch_assoc($users)) {
                        echo "<option value='{$user['id']}'>{$user['full_name']} ({$user['email']})</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Send Test Email</button>
        </form>
    </div>

    <!-- Recent Notification Logs -->
    <div class="admin-section">
        <h3>Recent Notification Logs (Last 20)</h3>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Recipient</th>
                    <th>Sent At</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($log = mysqli_fetch_assoc($logs)) {
                    $statusClass = $log['status'] === 'sent' ? 'sent' : ($log['status'] === 'failed' ? 'failed' : 'pending');
                    $recipient = $log['recipient_email'] ?? $log['recipient_phone'] ?? 'N/A';
                    $sentAt = $log['sent_at'] ? date('Y-m-d H:i:s', strtotime($log['sent_at'])) : 'Pending';
                ?>
                    <tr>
                        <td><?= ucfirst($log['notification_type']) ?></td>
                        <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
                        <td><span class="status-badge status-<?= $statusClass ?>"><?= ucfirst($log['status']) ?></span></td>
                        <td><?= htmlspecialchars($recipient) ?></td>
                        <td><?= $sentAt ?></td>
                        <td><?= $log['error_message'] ? htmlspecialchars($log['error_message']) : '-' ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
