<?php
/**
 * MediBuddy Reminder Notification Cron Job (BACKUP VERSION)
 * 
 * This script runs automatically and sends notifications for medicine reminders
 * 
 * Setup Instructions:
 * 1. On Linux/Mac: Add to crontab -e: * * * * * php /path/to/medibuddy/cron/send_reminders.php
 * 2. On Windows: Use Task Scheduler to run this file every minute
 * 3. For local testing: Visit http://localhost/medibuddy/cron/send_reminders.php manually
 */

require_once '../config/database.php';
require_once '../includes/notification-engine.php';

// Security: Check if this is a valid cron request
$allowedIPs = ['127.0.0.1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
$isCronRequest = php_sapi_name() === 'cli' || in_array($clientIP, $allowedIPs);

if(!$isCronRequest && php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from command line or localhost");
}

// Initialize notification engine
$notificationEngine = new NotificationEngine($conn);

// Get current time
$currentTime = new DateTime();
$currentHour = $currentTime->format('H');
$currentMinute = $currentTime->format('i');
$currentDay = $currentTime->format('l'); // Monday, Tuesday, etc.
$formattedTime = $currentTime->format('H:i');

// Log file for debugging
$logFile = '../logs/cron_' . date('Y-m-d') . '.log';
if(!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

writeLog("===== Reminder Notification Job Started =====");
writeLog("Current Time: $formattedTime | Day: $currentDay");

// Find reminders that need to be sent
$sql = "SELECT r.id as reminder_id, r.reminder_time, r.reminder_days, 
               r.frequency_id, r.family_member_id, r.prescription_medicine_id,
               m.name as medicine_name, m.dosage, m.unit,
               fm.name as family_member_name,
               p.user_id,
               u.email as user_email,
               ns.email_enabled, ns.sms_enabled, ns.sms_number, ns.notification_minutes_before,
               s.name as status_name
        FROM reminders r
        JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
        JOIN medicines m ON pm.medicine_id = m.id
        JOIN family_members fm ON r.family_member_id = fm.id
        JOIN prescriptions p ON pm.prescription_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN statuses s ON r.status_id = s.id
        LEFT JOIN notification_settings ns ON u.id = ns.user_id
        WHERE s.name IN ('Active', 'Pending')
        AND r.reminder_time IS NOT NULL";

$result = mysqli_query($conn, $sql);

if(!$result) {
    writeLog("ERROR: Database query failed - " . mysqli_error($conn));
    die("Database query failed");
}

$remindersSent = 0;
$remindersSkipped = 0;

while($reminder = mysqli_fetch_assoc($result)) {
    // Default notification settings if not found
    $emailEnabled = $reminder['email_enabled'] ?? 1;
    $smsEnabled = $reminder['sms_enabled'] ?? 0;
    $smsNumber = $reminder['sms_number'] ?? '';
    $minutesBefore = $reminder['notification_minutes_before'] ?? 15;

    // Extract reminder hour and minute
    $reminderTimeParts = explode(':', $reminder['reminder_time']);
    $reminderHour = $reminderTimeParts[0];
    $reminderMinute = $reminderTimeParts[1];

    // Check if this is a scheduled day
    if($reminder['reminder_days']) {
        $scheduledDays = explode(',', $reminder['reminder_days']);
        $scheduledDays = array_map('trim', $scheduledDays);
        if(!in_array($currentDay, $scheduledDays)) {
            $remindersSkipped++;
            continue;
        }
    }

    // Calculate notification time (send X minutes before reminder time)
    $notificationTime = new DateTime();
    $notificationTime->setTime($reminderHour, $reminderMinute);
    $notificationTime->sub(new DateInterval("PT{$minutesBefore}M"));

    // Check if current time matches notification time (with 1 minute tolerance)
    $timeDiff = abs($currentTime->getTimestamp() - $notificationTime->getTimestamp());
    $shouldSendNotification = $timeDiff <= 60; // Within 1 minute

    if($shouldSendNotification) {
        // Check if notification was already sent
        $checkSent = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT id FROM notification_logs WHERE reminder_id='{$reminder['reminder_id']}' 
            AND DATE(created_at) = CURDATE() AND status='sent'"));

        if($checkSent) {
            writeLog("Notification for reminder #{$reminder['reminder_id']} already sent today. Skipping.");
            $remindersSkipped++;
            continue;
        }

        writeLog("Processing reminder #{$reminder['reminder_id']}: {$reminder['medicine_name']} for {$reminder['family_member_name']}");

        // Send notifications based on user preferences
        $sent = false;

        if($emailEnabled && !empty($reminder['user_email'])) {
            $emailSent = $notificationEngine->sendEmailNotification(
                $reminder['reminder_id'],
                $reminder['user_id'],
                $reminder['user_email'],
                $reminder['medicine_name'],
                $reminder['family_member_name'],
                $reminder['dosage'],
                $reminder['unit'],
                $reminder['reminder_time']
            );
            
            if($emailSent) {
                writeLog("✓ Email sent to {$reminder['user_email']}");
                $sent = true;
            } else {
                writeLog("✗ Email failed for {$reminder['user_email']}");
            }
        }

        if($smsEnabled && !empty($smsNumber)) {
            $smsSent = $notificationEngine->sendSmsNotification(
                $reminder['reminder_id'],
                $reminder['user_id'],
                $smsNumber,
                $reminder['medicine_name'],
                $reminder['family_member_name'],
                $reminder['reminder_time']
            );
            
            if($smsSent) {
                writeLog("✓ SMS sent to $smsNumber");
                $sent = true;
            } else {
                writeLog("✗ SMS failed for $smsNumber");
            }
        }

        if($sent) {
            $remindersSent++;
        }
    }
}

writeLog("===== Job Completed =====");
writeLog("Reminders Sent: $remindersSent | Skipped: $remindersSkipped\n");

// Return status for monitoring
if(php_sapi_name() === 'cli') {
    echo "Cron Job Complete: $remindersSent reminders sent, $remindersSkipped skipped.\n";
} else {
    echo json_encode([
        'status' => 'success',
        'reminders_sent' => $remindersSent,
        'reminders_skipped' => $remindersSkipped,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>