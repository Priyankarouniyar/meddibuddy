<?php
// Twilio configuration for SMS, WhatsApp, and Email notifications

// ==============================================
// TWILIO MAIN CONFIGURATION
// ==============================================
// You can get these from your Twilio Console: https://www.twilio.com/console
$twilio_account_sid = "YOUR_ACCOUNT_SID_HERE";  // Replace with your actual Account SID
$twilio_auth_token = "YOUR_AUTH_TOKEN_HERE";    // Replace with your actual Auth Token

// ==============================================
// SMS CONFIGURATION
// ==============================================
$twilio_phone_number = "YOUR_TWILIO_PHONE_NUMBER"; // Replace with your Twilio phone number (e.g., +1234567890)
$twilio_messaging_service_sid = null; // If you're using a Messaging Service, add SID here
$sms_enabled = true;  // Set to false to disable SMS notifications globally

// ==============================================
// WHATSAPP CONFIGURATION
// ==============================================
$whatsapp_enabled = true;  // Set to false to disable WhatsApp notifications
$whatsapp_from = "whatsapp:YOUR_WHATSAPP_NUMBER";  // Your Twilio WhatsApp number (e.g., whatsapp:+14155238886)
// WhatsApp Business API requires approval from Twilio and Facebook
// Templates must be pre-approved by WhatsApp
$whatsapp_templates = [
    'reminder' => 'medicine_reminder',  // Template name for medicine reminders
    'appointment' => 'appointment_reminder',  // Template name for appointments
    'general' => 'general_notification'  // Template name for general notifications
];

// ==============================================
// EMAIL CONFIGURATION (Twilio SendGrid)
// ==============================================
$email_enabled = true;  // Set to false to disable email notifications
$sendgrid_api_key = "YOUR_SENDGRID_API_KEY";  // Get from SendGrid dashboard
$email_from_address = "noreply@meddibuddy.com";  // Your verified sender email
$email_from_name = "MeddiBuddy";  // Display name for emails
$email_reply_to = "support@meddibuddy.com";  // Reply-to address

// Email templates configuration
$email_templates = [
    'reminder' => [
        'subject' => 'Medicine Reminder - Time to take your medication',
        'template_id' => 'medicine_reminder_template'  // SendGrid template ID
    ],
    'appointment' => [
        'subject' => 'Appointment Reminder',
        'template_id' => 'appointment_reminder_template'
    ],
    'welcome' => [
        'subject' => 'Welcome to MeddiBuddy',
        'template_id' => 'welcome_template'
    ]
];

// ==============================================
// GENERAL CONFIGURATION
// ==============================================
$default_country_code = "+1";  // Default country code for phone numbers

// Twilio API Base URL (usually doesn't need to change)
$twilio_api_url = "https://api.twilio.com";
$sendgrid_api_url = "https://api.sendgrid.com/v3";

// Error handling configuration
$notification_error_logging = true;  // Set to true to log notification errors
$max_retry_attempts = 3;  // Maximum retry attempts for failed notifications

// ==============================================
// VALIDATION FUNCTIONS
// ==============================================

// Function to validate Twilio SMS configuration
function validateSmsConfig() {
    global $twilio_account_sid, $twilio_auth_token, $twilio_phone_number;
    
    $errors = [];
    
    if (empty($twilio_account_sid) || $twilio_account_sid === "YOUR_ACCOUNT_SID_HERE") {
        $errors[] = "Twilio Account SID is not configured";
    }
    
    if (empty($twilio_auth_token) || $twilio_auth_token === "YOUR_AUTH_TOKEN_HERE") {
        $errors[] = "Twilio Auth Token is not configured";
    }
    
    if (empty($twilio_phone_number) || $twilio_phone_number === "YOUR_TWILIO_PHONE_NUMBER") {
        $errors[] = "Twilio Phone Number is not configured";
    }
    
    return empty($errors) ? true : $errors;
}

// Function to validate WhatsApp configuration
function validateWhatsAppConfig() {
    global $twilio_account_sid, $twilio_auth_token, $whatsapp_from;
    
    $errors = [];
    
    if (empty($twilio_account_sid) || $twilio_account_sid === "YOUR_ACCOUNT_SID_HERE") {
        $errors[] = "Twilio Account SID is not configured for WhatsApp";
    }
    
    if (empty($twilio_auth_token) || $twilio_auth_token === "YOUR_AUTH_TOKEN_HERE") {
        $errors[] = "Twilio Auth Token is not configured for WhatsApp";
    }
    
    if (empty($whatsapp_from) || $whatsapp_from === "whatsapp:YOUR_WHATSAPP_NUMBER") {
        $errors[] = "WhatsApp sender number is not configured";
    }
    
    return empty($errors) ? true : $errors;
}

// Function to validate Email configuration
function validateEmailConfig() {
    global $sendgrid_api_key, $email_from_address;
    
    $errors = [];
    
    if (empty($sendgrid_api_key) || $sendgrid_api_key === "YOUR_SENDGRID_API_KEY") {
        $errors[] = "SendGrid API Key is not configured";
    }
    
    if (empty($email_from_address) || !filter_var($email_from_address, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid sender email address is not configured";
    }
    
    return empty($errors) ? true : $errors;
}

// ==============================================
// SERVICE AVAILABILITY FUNCTIONS
// ==============================================

// Function to check if SMS is available
function isSmsEnabled() {
    global $sms_enabled;
    return $sms_enabled && (validateSmsConfig() === true);
}

// Function to check if WhatsApp is available
function isWhatsAppEnabled() {
    global $whatsapp_enabled;
    return $whatsapp_enabled && (validateWhatsAppConfig() === true);
}

// Function to check if Email is available
function isEmailEnabled() {
    global $email_enabled;
    return $email_enabled && (validateEmailConfig() === true);
}

// Function to get available notification channels
function getAvailableChannels() {
    $channels = [];
    
    if (isSmsEnabled()) {
        $channels[] = 'sms';
    }
    
    if (isWhatsAppEnabled()) {
        $channels[] = 'whatsapp';
    }
    
    if (isEmailEnabled()) {
        $channels[] = 'email';
    }
    
    return $channels;
}

// ==============================================
// HELPER FUNCTIONS
// ==============================================

// Function to format phone number for WhatsApp
function formatWhatsAppNumber($phone) {
    global $default_country_code;
    
    // Remove all non-digit characters
    $phone = preg_replace('/\D/', '', $phone);
    
    // Add country code if not present
    if (!str_starts_with($phone, substr($default_country_code, 1))) {
        $phone = substr($default_country_code, 1) . $phone;
    }
    
    return 'whatsapp:+' . $phone;
}

// Function to format phone number for SMS
function formatSmsNumber($phone) {
    global $default_country_code;
    
    // Remove all non-digit characters except +
    $phone = preg_replace('/[^\d+]/', '', $phone);
    
    // Add country code if not present
    if (!str_starts_with($phone, '+')) {
        if (!str_starts_with($phone, substr($default_country_code, 1))) {
            $phone = $default_country_code . $phone;
        } else {
            $phone = '+' . $phone;
        }
    }
    
    return $phone;
}

// Optional: Initialize Twilio SDK if you're using the official library
// For pure PHP without Composer, we'll use cURL for API calls

// ==============================================
// API ENDPOINT CONFIGURATIONS
// ==============================================

// Twilio API endpoints for direct HTTP calls
$twilio_endpoints = [
    'sms' => 'https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json',
    'whatsapp' => 'https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json',
    'lookup' => 'https://lookups.twilio.com/v1/PhoneNumbers/{PhoneNumber}'
];

// SendGrid API endpoints
$sendgrid_endpoints = [
    'send_email' => 'https://api.sendgrid.com/v3/mail/send',
    'templates' => 'https://api.sendgrid.com/v3/templates'
];

// ==============================================
// HTTP CLIENT FUNCTIONS
// ==============================================

// Function to make HTTP requests using cURL
function makeHttpRequest($url, $method = 'POST', $headers = [], $data = null, $auth = null) {
    $ch = curl_init();
    
    // Basic cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3
    ]);
    
    // Add authentication if provided
    if ($auth) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
    }
    
    // Add data for POST/PUT requests
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("HTTP Request Error: " . $error);
        return false;
    }
    
    return [
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'http_code' => $httpCode,
        'response' => $response,
        'data' => json_decode($response, true)
    ];
}

// Function to send SMS using Twilio API
function sendTwilioSms($to, $message, $from = null) {
    global $twilio_account_sid, $twilio_auth_token, $twilio_phone_number, $twilio_endpoints;
    
    if (!isSmsEnabled()) {
        return ['success' => false, 'error' => 'SMS service not enabled or configured'];
    }
    
    $from = $from ?: $twilio_phone_number;
    $url = str_replace('{AccountSid}', $twilio_account_sid, $twilio_endpoints['sms']);
    
    $data = [
        'To' => formatSmsNumber($to),
        'From' => $from,
        'Body' => $message
    ];
    
    $auth = $twilio_account_sid . ':' . $twilio_auth_token;
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    
    $result = makeHttpRequest($url, 'POST', $headers, $data, $auth);
    
    if ($result && $result['success']) {
        return ['success' => true, 'message_sid' => $result['data']['sid'] ?? ''];
    } else {
        $error = $result ? ($result['data']['message'] ?? 'Unknown error') : 'Request failed';
        error_log("Twilio SMS Error: " . $error);
        return ['success' => false, 'error' => $error];
    }
}

// Function to send WhatsApp message using Twilio API
function sendTwilioWhatsApp($to, $message, $template = null) {
    global $twilio_account_sid, $twilio_auth_token, $whatsapp_from, $twilio_endpoints;
    
    if (!isWhatsAppEnabled()) {
        return ['success' => false, 'error' => 'WhatsApp service not enabled or configured'];
    }
    
    $url = str_replace('{AccountSid}', $twilio_account_sid, $twilio_endpoints['whatsapp']);
    
    $data = [
        'To' => formatWhatsAppNumber($to),
        'From' => $whatsapp_from,
        'Body' => $message
    ];
    
    // Add template if provided (for approved WhatsApp Business templates)
    if ($template) {
        $data['ContentSid'] = $template;
    }
    
    $auth = $twilio_account_sid . ':' . $twilio_auth_token;
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    
    $result = makeHttpRequest($url, 'POST', $headers, $data, $auth);
    
    if ($result && $result['success']) {
        return ['success' => true, 'message_sid' => $result['data']['sid'] ?? ''];
    } else {
        $error = $result ? ($result['data']['message'] ?? 'Unknown error') : 'Request failed';
        error_log("Twilio WhatsApp Error: " . $error);
        return ['success' => false, 'error' => $error];
    }
}

// Function to send email using SendGrid API
function sendEmail($to, $subject, $content, $template_id = null) {
    global $sendgrid_api_key, $email_from_address, $email_from_name, $sendgrid_endpoints;
    
    if (!isEmailEnabled()) {
        return ['success' => false, 'error' => 'Email service not enabled or configured'];
    }
    
    $url = $sendgrid_endpoints['send_email'];
    
    $email_data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $to]
                ],
                'subject' => $subject
            ]
        ],
        'from' => [
            'email' => $email_from_address,
            'name' => $email_from_name
        ]
    ];
    
    // Use template or plain content
    if ($template_id) {
        $email_data['template_id'] = $template_id;
    } else {
        $email_data['content'] = [
            [
                'type' => 'text/html',
                'value' => $content
            ]
        ];
    }
    
    $headers = [
        'Authorization: Bearer ' . $sendgrid_api_key,
        'Content-Type: application/json'
    ];
    
    $result = makeHttpRequest($url, 'POST', $headers, json_encode($email_data));
    
    if ($result && $result['success']) {
        return ['success' => true, 'message_id' => $result['data']['message_id'] ?? ''];
    } else {
        $error = $result ? ($result['data']['errors'][0]['message'] ?? 'Unknown error') : 'Request failed';
        error_log("SendGrid Email Error: " . $error);
        return ['success' => false, 'error' => $error];
    }
}

// ==============================================
// NOTIFICATION PREFERENCES
// ==============================================

// Default notification preferences for new users
$default_notification_preferences = [
    'medicine_reminders' => [
        'sms' => true,
        'whatsapp' => true,
        'email' => true
    ],
    'appointment_reminders' => [
        'sms' => true,
        'whatsapp' => true,
        'email' => true
    ],
    'medication_updates' => [
        'sms' => false,
        'whatsapp' => false,
        'email' => true
    ],
    'system_notifications' => [
        'sms' => false,
        'whatsapp' => false,
        'email' => true
    ]
];

// Function to get user's notification preferences (to be used with database)
function getUserNotificationPreferences($user_id) {
    global $conn, $default_notification_preferences;
    
    // This function should query the database for user preferences
    // For now, return default preferences
    return $default_notification_preferences;
}

// ==============================================
// UTILITY FUNCTIONS FOR NOTIFICATIONS
// ==============================================

// Function to send notification via multiple channels
function sendMultiChannelNotification($user_id, $phone, $email, $message, $channels = ['sms', 'whatsapp', 'email'], $template_type = 'general') {
    $results = [];
    
    // Get user preferences
    $preferences = getUserNotificationPreferences($user_id);
    
    // Send SMS if enabled and user prefers it
    if (in_array('sms', $channels) && isSmsEnabled() && $preferences[$template_type]['sms']) {
        $results['sms'] = sendTwilioSms($phone, $message);
    }
    
    // Send WhatsApp if enabled and user prefers it
    if (in_array('whatsapp', $channels) && isWhatsAppEnabled() && $preferences[$template_type]['whatsapp']) {
        $results['whatsapp'] = sendTwilioWhatsApp($phone, $message);
    }
    
    // Send Email if enabled and user prefers it
    if (in_array('email', $channels) && isEmailEnabled() && $preferences[$template_type]['email']) {
        global $email_templates;
        $subject = $email_templates[$template_type]['subject'] ?? 'MeddiBuddy Notification';
        $template_id = $email_templates[$template_type]['template_id'] ?? null;
        
        $results['email'] = sendEmail($email, $subject, $message, $template_id);
    }
    
    return $results;
}

// Function to send medicine reminder notification
function sendMedicineReminder($user_id, $phone, $email, $medicine_name, $dosage, $time) {
    $message = "🔔 Medicine Reminder: Time to take your {$medicine_name} ({$dosage}) at {$time}. Stay healthy!";
    
    return sendMultiChannelNotification(
        $user_id, 
        $phone, 
        $email, 
        $message, 
        ['sms', 'whatsapp', 'email'], 
        'medicine_reminders'
    );
}

// Function to send appointment reminder
function sendAppointmentReminder($user_id, $phone, $email, $doctor_name, $appointment_time, $location) {
    $message = "📅 Appointment Reminder: You have an appointment with Dr. {$doctor_name} at {$appointment_time}. Location: {$location}";
    
    return sendMultiChannelNotification(
        $user_id, 
        $phone, 
        $email, 
        $message, 
        ['sms', 'whatsapp', 'email'], 
        'appointment_reminders'
    );
}

// Function to test notification services
function testNotificationServices($test_phone, $test_email) {
    $results = [];
    $test_message = "This is a test message from MeddiBuddy notification system.";
    
    if (isSmsEnabled()) {
        $results['sms'] = sendTwilioSms($test_phone, $test_message);
    }
    
    if (isWhatsAppEnabled()) {
        $results['whatsapp'] = sendTwilioWhatsApp($test_phone, $test_message);
    }
    
    if (isEmailEnabled()) {
        $results['email'] = sendEmail($test_email, "MeddiBuddy Test Notification", $test_message);
    }
    
    return $results;
}

// Function to log notification attempts
function logNotification($user_id, $channel, $message, $result) {
    global $notification_error_logging;
    
    if ($notification_error_logging) {
        $status = $result['success'] ? 'SUCCESS' : 'FAILED';
        $error = $result['success'] ? '' : (' - Error: ' . $result['error']);
        
        error_log("NOTIFICATION LOG - User ID: {$user_id}, Channel: {$channel}, Status: {$status}{$error}");
    }
}

?>