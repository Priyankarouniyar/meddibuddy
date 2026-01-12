# PHPMailer Integration Guide

This document explains how PHPMailer is integrated into the `send_reminders.php` script for sending email notifications.

## Overview
PHPMailer is a popular library for sending emails in PHP. It provides an easy-to-use interface for sending emails via SMTP, with support for authentication, encryption, and HTML content.

## File Structure
The PHPMailer library is located in the `vendor/PHPMailer/` directory. The following files are included:
- `PHPMailer.php`
- `SMTP.php`
- `Exception.php`

These files are required in the `send_reminders.php` script to handle email notifications.

## Configuration
The `sendEmailReminder` function in `send_reminders.php` is responsible for sending emails using PHPMailer. Below are the key configuration options:

### SMTP Settings
- **Host**: The SMTP server address. Replace `smtp.example.com` with your SMTP provider's hostname (e.g., `smtp.gmail.com`).
- **Username**: Your email address used for authentication.
- **Password**: The password for your email account.
- **Port**: The port number for the SMTP server (e.g., `587` for TLS).
- **Encryption**: The encryption method (e.g., `PHPMailer::ENCRYPTION_STARTTLS`).

### Example Configuration
```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## Usage in `send_reminders.php`
The `sendMedicineReminder` function is updated to include email handling. It uses the `sendEmailReminder` function to send email notifications.

### Email Content
The email includes the following details:
- Medicine name
- Dosage
- Reminder time

Example:
```php
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
```

### Function Call
The `sendMedicineReminder` function calls `sendEmailReminder` as follows:
```php
$results['email'] = sendEmailReminder($userEmail, $emailSubject, $emailBody);
```

## Error Handling
If an email fails to send, the error message is logged using the `writeLog` function. The error details are also stored in the `notification_logs` database table.

## Testing
To test the email functionality:
1. Update the SMTP settings with valid credentials.
2. Run the `send_reminders.php` script manually.
3. Check the logs in the `logs/` directory for success or error messages.

## Troubleshooting
- **Authentication Errors**: Ensure the username and password are correct.
- **Firewall Issues**: Verify that the server allows outbound connections to the SMTP server.
- **Invalid Hostname**: Double-check the SMTP server address.

## References
- [PHPMailer GitHub Repository](https://github.com/PHPMailer/PHPMailer)
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer/wiki)

---

For further assistance, contact the development team.