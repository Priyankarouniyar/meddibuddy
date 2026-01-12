<?php
/**
 * MediBuddy Reminder Notification Cron Job
 * 
 * This script runs automatically and sends notifications for medicine reminders
 * 
 * Setup Instructions:
 * 1. On Linux/Mac: Add to crontab -e: * * * * * php /path/to/medibuddy/cron/send_reminders.php
 * 2. On Windows: Use Task Scheduler to run this file every minute
 * 3. For local testing: Visit http://localhost/medibuddy/cron/send_reminders.php manually
 */

require_once '../config/database.php';
require_once '../config/twilio.php';
require '../vendor/PHPMailer/PHPMailer.php';
require '../vendor/PHPMailer/SMTP.php';
require '../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Security: Check if this is a valid cron request
$allowedIPs = ['127.0.0.1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
$isCronRequest = php_sapi_name() === 'cli' || in_array($clientIP, $allowedIPs);

if(!$isCronRequest && php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from command line or localhost");
}

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
               u.email as user_email, u.phone as user_phone, u.name as user_name,
               ns.email_enabled, ns.sms_enabled, ns.whatsapp_enabled, 
               ns.sms_number, ns.notification_minutes_before,
               s.name as status_name,
               f.times_per_day
        FROM reminders r
        JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
        JOIN medicines m ON pm.medicine_id = m.id
        JOIN family_members fm ON r.family_member_id = fm.id
        JOIN prescriptions p ON pm.prescription_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN statuses s ON r.status_id = s.id
        JOIN frequencies f ON r.frequency_id = f.id
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
    $smsEnabled = $reminder['sms_enabled'] ?? 1;
    $whatsappEnabled = $reminder['whatsapp_enabled'] ?? 1;
    $userPhone = $reminder['user_phone'] ?? $reminder['sms_number'] ?? '';
    $minutesBefore = $reminder['notification_minutes_before'] ?? 15;

    // Extract reminder hour and minute
    $reminderTimeParts = explode(':', $reminder['reminder_time']);
    $reminderHour = $reminderTimeParts[0];
    $reminderMinute = $reminderTimeParts[1];

    // Check frequency
    $timesPerDay = $reminder['times_per_day'] ?? 1;
    $interval = 24 / $timesPerDay; // Interval in hours

    $shouldSend = false;
    for ($i = 0; $i < $timesPerDay; $i++) {
        $notificationTime = new DateTime();
        $notificationTime->setTime($reminderHour, $reminderMinute);
        $notificationTime->add(new DateInterval("PT" . ($i * $interval) . "H"));
        $notificationTime->sub(new DateInterval("PT{$minutesBefore}M"));

        $timeDiff = abs($currentTime->getTimestamp() - $notificationTime->getTimestamp());
        if ($timeDiff <= 60) { // Within 1 minute
            $shouldSend = true;
            break;
        }
    }

    if (!$shouldSend) {
        $remindersSkipped++;
        continue;
    }

    // Reintroduce reminder_days handling
    if ($reminder['reminder_days']) {
        $scheduledDays = explode(',', $reminder['reminder_days']);
        $scheduledDays = array_map('trim', $scheduledDays);
        if (!in_array($currentDay, $scheduledDays)) {
            $remindersSkipped++;
            continue;
        }
    }

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

    // Prepare notification channels based on user preferences
    $channels = [];
    if($emailEnabled) $channels[] = 'email';
    if($smsEnabled && !empty($userPhone)) $channels[] = 'sms';
    if($whatsappEnabled && !empty($userPhone)) $channels[] = 'whatsapp';

    if(empty($channels)) {
        writeLog("No notification channels enabled for user #{$reminder['user_id']}. Skipping.");
        $remindersSkipped++;
        continue;
    }

    // Send multi-channel medicine reminder using Twilio
    $notificationResults = sendMedicineReminder(
        $reminder['user_id'],
        $userPhone,
        $reminder['user_email'],
        $reminder['medicine_name'],
        $reminder['dosage'] . ' ' . $reminder['unit'],
        $reminder['reminder_time']
    );

    // Process results and log
    $sent = false;
    foreach($notificationResults as $channel => $result) {
        if($result['success']) {
            writeLog("✓ {$channel} notification sent successfully");
            
            // Log successful notification to database
            $logSql = "INSERT INTO notification_logs (reminder_id, user_id, channel, status, message, created_at) 
                      VALUES ('{$reminder['reminder_id']}', '{$reminder['user_id']}', '$channel', 'sent', 
                              'Medicine reminder sent for {$reminder['medicine_name']}', NOW())";
            mysqli_query($conn, $logSql);
            
            $sent = true;
        } else {
            writeLog("✗ {$channel} notification failed: " . $result['error']);
            
            // Log failed notification to database
            $logSql = "INSERT INTO notification_logs (reminder_id, user_id, channel, status, message, created_at) 
                      VALUES ('{$reminder['reminder_id']}', '{$reminder['user_id']}', '$channel', 'failed', 
                              'Error: {$result['error']}', NOW())";
            mysqli_query($conn, $logSql);
        }
    }

    if($sent) {
        $remindersSent++;
        
        // Update reminder last_sent timestamp
        $updateSql = "UPDATE reminders SET last_sent = NOW() WHERE id = '{$reminder['reminder_id']}'";
        mysqli_query($conn, $updateSql);
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

/**
 * Send medicine reminder via email using PHPMailer
 */
function sendEmailReminder($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com'; // Replace with your email
        $mail->Password = 'your_password'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@example.com', 'MediBuddy');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

// Update sendMedicineReminder to include email handling
function sendMedicineReminder($userId, $userPhone, $userEmail, $medicineName, $dosage, $reminderTime) {
    $results = [];

    // Email Notification
    if (!empty($userEmail)) {
        $emailSubject = "Medicine Reminder: $medicineName";
        $emailBody = "<p>Dear User,</p>
                      <p>This is a reminder to take your medicine:</p>
                      <ul>
                          <li><strong>Medicine:</strong> $medicineName</li>
                          <li><strong>Dosage:</strong> $dosage</li>
                          <li><strong>Time:</strong> $reminderTime</li>
                      </ul>
                      <p>Stay healthy,</p>
                      <p>MediBuddy Team</p>";

        $results['email'] = sendEmailReminder($userEmail, $emailSubject, $emailBody);
    }

    // SMS and WhatsApp logic remains unchanged
    // ...existing code...

    return $results;
}
?>
