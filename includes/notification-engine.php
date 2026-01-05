<?php
// Notification Engine - Core email and SMS sending functions
// Only uses Core PHP - no third party libraries

class NotificationEngine {
    private $conn;
    private $siteEmail = 'noreply@medibuddy.com';
    private $siteName = 'MediBuddy';

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    /**
     * Send email notification for medicine reminder
     * @param int $reminderId - Reminder ID
     * @param int $userId - User ID
     * @param string $userEmail - User email address
     * @param string $medicineName - Medicine name
     * @param string $familyMemberName - Family member name
     * @param string $dosage - Medicine dosage
     * @param string $unit - Unit (mg, ml, etc)
     * @param string $reminderTime - Reminder time
     * @return bool - Success/Failure
     */
    public function sendEmailNotification($reminderId, $userId, $userEmail, $medicineName, $familyMemberName, $dosage, $unit, $reminderTime) {
        $subject = "[MediBuddy] Medicine Reminder - $medicineName";
        
        $message = $this->getEmailTemplate($medicineName, $familyMemberName, $dosage, $unit, $reminderTime);
        
        // Prepare headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->siteName . " <" . $this->siteEmail . ">\r\n";
        $headers .= "X-Mailer: MediBuddy Notification System\r\n";

        // Send email using PHP's mail() function
        $mailSent = mail($userEmail, $subject, $message, $headers);

        // Log the notification attempt
        $this->logNotification($reminderId, $userId, 'email', $userEmail, null, $subject, $message, $mailSent ? 'sent' : 'failed', $mailSent ? null : 'Mail function failed');

        return $mailSent;
    }

    /**
     * Send SMS notification (placeholder for demonstration)
     * @param int $reminderId - Reminder ID
     * @param int $userId - User ID
     * @param string $phoneNumber - Phone number to send SMS to
     * @param string $medicineName - Medicine name
     * @param string $familyMemberName - Family member name
     * @param string $reminderTime - Reminder time
     * @return bool - Success/Failure
     */
    public function sendSmsNotification($reminderId, $userId, $phoneNumber, $medicineName, $familyMemberName, $reminderTime) {
        $smsMessage = "MediBuddy Reminder: Time to take $medicineName for $familyMemberName at $reminderTime";

        // Check if user has SMS gateway configured
        $gateway = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM sms_gateway WHERE user_id='$userId' AND is_active=1"));
        
        if($gateway) {
            // If gateway is configured, send via gateway
            $smsSent = $this->sendViaGateway($gateway, $phoneNumber, $smsMessage);
        } else {
            // For demonstration: Create a pending SMS notification
            // In production, integrate with Twilio, Nexmo, or similar service
            $smsSent = true;
        }

        // Log the SMS notification attempt
        $this->logNotification($reminderId, $userId, 'sms', null, $phoneNumber, 'SMS Reminder', $smsMessage, $smsSent ? 'sent' : 'failed');

        return $smsSent;
    }

    /**
     * Send both email and SMS notifications
     */
    public function sendBothNotifications($reminderId, $userId, $userEmail, $phoneNumber, $medicineName, $familyMemberName, $dosage, $unit, $reminderTime) {
        $emailSent = $this->sendEmailNotification($reminderId, $userId, $userEmail, $medicineName, $familyMemberName, $dosage, $unit, $reminderTime);
        $smsSent = $this->sendSmsNotification($reminderId, $userId, $phoneNumber, $medicineName, $familyMemberName, $reminderTime);
        
        return $emailSent && $smsSent;
    }

    /**
     * Get HTML email template
     */
    private function getEmailTemplate($medicineName, $familyMemberName, $dosage, $unit, $reminderTime) {
        $currentTime = date('Y-m-d H:i');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; border-radius: 8px; }
                .header { background: #667eea; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .medicine-box { background: #f0f4ff; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .footer { text-align: center; color: #999; font-size: 0.9rem; margin-top: 20px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                h2 { color: #667eea; margin-top: 0; }
                .time { font-size: 1.5rem; color: #667eea; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>MediBuddy - Medicine Reminder</h1>
                </div>
                <div class='content'>
                    <h2>Time to Take Medicine</h2>
                    <p>Hi there,</p>
                    <p>This is a friendly reminder that it's time to take <strong>$medicineName</strong>.</p>
                    
                    <div class='medicine-box'>
                        <p><strong>Medicine:</strong> $medicineName</p>
                        <p><strong>Patient:</strong> $familyMemberName</p>
                        <p><strong>Dosage:</strong> $dosage $unit</p>
                        <p><strong>Reminder Time:</strong> <span class='time'>$reminderTime</span></p>
                    </div>
                    
                    <p><strong>Please take the medicine as prescribed by your doctor.</strong></p>
                    
                    <p>If you have any questions or concerns about your medication, please contact your healthcare provider.</p>
                    
                    <p style='margin-top: 30px;'>
                        <a href='https://localhost/medibuddy/user/reminders.php' class='button'>View My Reminders</a>
                    </p>
                    
                    <div class='footer'>
                        <p>This is an automated message from MediBuddy. Please do not reply to this email.</p>
                        <p>Sent on: $currentTime</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Log notification in database
     */
    private function logNotification($reminderId, $userId, $type, $email, $phone, $subject, $message, $status, $errorMsg = null) {
        $type = mysqli_real_escape_string($this->conn, $type);
        $email = mysqli_real_escape_string($this->conn, $email);
        $phone = mysqli_real_escape_string($this->conn, $phone);
        $subject = mysqli_real_escape_string($this->conn, $subject);
        $message = mysqli_real_escape_string($this->conn, substr($message, 0, 500)); // Truncate for storage
        $errorMsg = mysqli_real_escape_string($this->conn, $errorMsg);
        $sentAt = $status === 'sent' ? "NOW()" : "NULL";

        $sql = "INSERT INTO notification_logs (reminder_id, user_id, notification_type, recipient_email, recipient_phone, subject, message, status, error_message, sent_at)
                VALUES ('$reminderId', '$userId', '$type', '$email', '$phone', '$subject', '$message', '$status', '$errorMsg', $sentAt)";
        
        mysqli_query($this->conn, $sql);
    }

    /**
     * Send via configured SMS gateway
     * Placeholder for actual SMS gateway integration (Twilio, Nexmo, etc)
     */
    private function sendViaGateway($gateway, $phoneNumber, $message) {
        // This is a placeholder - actual implementation would depend on the gateway type
        // For Twilio: use cURL to send HTTP request to Twilio API
        // For Nexmo: use cURL to send HTTP request to Nexmo API
        // etc.
        
        // For now, return true (demonstration)
        return true;
    }

    /**
     * Get notification logs for a user
     */
    public function getUserNotificationLogs($userId, $limit = 50) {
        $sql = "SELECT nl.*, r.reminder_time, m.name as medicine_name, fm.name as family_member_name
                FROM notification_logs nl
                LEFT JOIN reminders r ON nl.reminder_id = r.id
                LEFT JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
                LEFT JOIN medicines m ON pm.medicine_id = m.id
                LEFT JOIN family_members fm ON r.family_member_id = fm.id
                WHERE nl.user_id='$userId'
                ORDER BY nl.created_at DESC
                LIMIT $limit";
        
        return mysqli_query($this->conn, $sql);
    }

    /**
     * Get pending notifications that need to be sent
     */
    public function getPendingNotifications() {
        $sql = "SELECT nl.*, r.reminder_time, m.name as medicine_name, fm.name as family_member_name, u.email, u.full_name
                FROM notification_logs nl
                JOIN reminders r ON nl.reminder_id = r.id
                JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
                JOIN medicines m ON pm.medicine_id = m.id
                JOIN family_members fm ON r.family_member_id = fm.id
                JOIN users u ON nl.user_id = u.id
                WHERE nl.status = 'pending'
                AND nl.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
                ORDER BY nl.created_at ASC";
        
        return mysqli_query($this->conn, $sql);
    }
}
?>
