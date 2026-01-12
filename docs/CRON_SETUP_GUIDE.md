# MeddiBuddy Cron Job Setup Guide
## Automated Medicine Reminder System

This guide will help you set up the automated medicine reminder system that sends SMS, WhatsApp, and Email notifications using Twilio.

---

## 📋 Prerequisites

### System Requirements
- **PHP 7.4+** with cURL extension enabled
- **MySQL/MariaDB** database
- **Web server** (Apache/Nginx) or XAMPP/WAMP for local development
- **Internet connection** for Twilio API calls

### Twilio Account Setup
1. **Sign up** at [twilio.com](https://www.twilio.com)
2. **Get credentials**:
   - Account SID
   - Auth Token
   - Phone Number (purchase one)
3. **For WhatsApp**: Apply for WhatsApp Business API access
4. **For Email**: Set up SendGrid account

---

## ⚙️ Configuration Steps

### 1. Update Twilio Configuration
Edit `/config/twilio.php` and replace placeholder values:

```php
$twilio_account_sid = "AC1234567890abcdef";  // Your actual Account SID
$twilio_auth_token = "your_auth_token_here";  // Your actual Auth Token
$twilio_phone_number = "+1234567890";         // Your Twilio phone number
$whatsapp_from = "whatsapp:+14155238886";     // Twilio WhatsApp number
$sendgrid_api_key = "SG.xxxxxxxxxxxxx";      // SendGrid API key (optional)
$email_from_address = "noreply@meddibuddy.com";
```

### 2. Test Configuration
Create a test file `/test_notifications.php`:

```php
<?php
require_once 'config/twilio.php';

// Test with your phone and email
$results = testNotificationServices("+1234567890", "your@email.com");

foreach($results as $service => $result) {
    echo $service . ": " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if(!$result['success']) {
        echo "Error: " . $result['error'] . "\n";
    }
}
?>
```

Run test: `php test_notifications.php`

---

## 🐧 Linux Setup Instructions

### Method 1: Using Crontab (Recommended)

#### Step 1: Find PHP Path
```bash
which php
# Output: /usr/bin/php (note this path)
```

#### Step 2: Edit Crontab
```bash
crontab -e
```

#### Step 3: Add Cron Job
Choose one of these schedules:

**Every minute (precise timing):**
```bash
* * * * * /usr/bin/php /home/fm-pc-lt-285/personal/meddibuddy/cron/send_reminders.php >> /home/fm-pc-lt-285/personal/meddibuddy/logs/cron.log 2>&1
```

**Every 5 minutes (less server load):**
```bash
*/5 * * * * /usr/bin/php /home/fm-pc-lt-285/personal/meddibuddy/cron/send_reminders.php >> /home/fm-pc-lt-285/personal/meddibuddy/logs/cron.log 2>&1
```

**Every 15 minutes:**
```bash
*/15 * * * * /usr/bin/php /home/fm-pc-lt-285/personal/meddibuddy/cron/send_reminders.php >> /home/fm-pc-lt-285/personal/meddibuddy/logs/cron.log 2>&1
```

#### Step 4: Save and Exit
- **Nano**: Press `Ctrl+X`, then `Y`, then `Enter`
- **Vim**: Press `Esc`, type `:wq`, press `Enter`

#### Step 5: Verify Cron Job
```bash
crontab -l
```

### Method 2: Using Systemd Timer (Advanced)

#### Step 1: Create Service File
```bash
sudo nano /etc/systemd/system/meddibuddy-reminders.service
```

Add content:
```ini
[Unit]
Description=MeddiBuddy Medicine Reminders
After=network.target

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/php /home/fm-pc-lt-285/personal/meddibuddy/cron/send_reminders.php
WorkingDirectory=/home/fm-pc-lt-285/personal/meddibuddy/cron
```

#### Step 2: Create Timer File
```bash
sudo nano /etc/systemd/system/meddibuddy-reminders.timer
```

Add content:
```ini
[Unit]
Description=Run MeddiBuddy reminders every minute
Requires=meddibuddy-reminders.service

[Timer]
OnCalendar=*:*:00
Persistent=true

[Install]
WantedBy=timers.target
```

#### Step 3: Enable and Start
```bash
sudo systemctl daemon-reload
sudo systemctl enable meddibuddy-reminders.timer
sudo systemctl start meddibuddy-reminders.timer
sudo systemctl status meddibuddy-reminders.timer
```

---

## 🪟 Windows Setup Instructions

### Method 1: Using Task Scheduler (Recommended)

#### Step 1: Open Task Scheduler
- Press `Win + R`
- Type `taskschd.msc`
- Press `Enter`

#### Step 2: Create Basic Task
1. Click **"Create Basic Task"** in the right panel
2. **Name**: `MeddiBuddy Medicine Reminders`
3. **Description**: `Automated medicine reminder notifications`
4. Click **Next**

#### Step 3: Configure Trigger
1. Select **"Daily"**
2. Click **Next**
3. Set **Start date**: Today
4. Set **Start time**: `00:00:00` (midnight)
5. **Recur every**: `1 days`
6. Click **Next**

#### Step 4: Configure Action
1. Select **"Start a program"**
2. Click **Next**
3. **Program/script**: `C:\xampp\php\php.exe` (adjust path to your PHP)
4. **Add arguments**: `C:\xampp\htdocs\meddibuddy\cron\send_reminders.php`
5. **Start in**: `C:\xampp\htdocs\meddibuddy\cron\`
6. Click **Next**

#### Step 5: Advanced Settings
1. Check **"Open the Properties dialog"**
2. Click **Finish**
3. In Properties window:
   - Go to **Triggers** tab
   - Select your trigger and click **Edit**
   - Check **"Repeat task every"**: `1 minute`
   - **For a duration of**: `Indefinitely`
   - Click **OK**

#### Step 6: Security Settings
1. In Properties window, go to **General** tab
2. Select **"Run whether user is logged on or not"**
3. Check **"Run with highest privileges"**
4. Click **OK**

### Method 2: Using XAMPP Control Panel

#### Step 1: Create Batch File
Create `run_reminders.bat` in your project folder:

```batch
@echo off
cd /d C:\xampp\htdocs\meddibuddy\cron
C:\xampp\php\php.exe send_reminders.php >> ..\logs\cron.log 2>&1
```

#### Step 2: Schedule Batch File
Follow **Method 1** steps but use the batch file instead:
- **Program/script**: `C:\path\to\your\run_reminders.bat`

---

## 🧪 Testing Instructions

### Manual Testing

#### 1. Command Line Test
**Linux:**
```bash
cd /home/fm-pc-lt-285/personal/meddibuddy/cron/
php send_reminders.php
```

**Windows:**
```cmd
cd C:\xampp\htdocs\meddibuddy\cron\
C:\xampp\php\php.exe send_reminders.php
```

#### 2. Web Browser Test
Visit: `http://localhost/meddibuddy/cron/send_reminders.php`

**Expected Output:**
```json
{
    "status": "success",
    "reminders_sent": 0,
    "reminders_skipped": 0,
    "timestamp": "2026-01-11 14:30:00"
}
```

### Integration Testing

#### 1. Create Test Reminder
1. Log into MeddiBuddy admin panel
2. Create a test user with phone number and email
3. Add a medicine reminder for 2 minutes from now
4. Wait and check if notifications are received

#### 2. Check Logs
**Linux:**
```bash
tail -f /home/fm-pc-lt-285/personal/meddibuddy/logs/cron_$(date +%Y-%m-%d).log
```

**Windows:**
Check: `C:\xampp\htdocs\meddibuddy\logs\cron_2026-01-11.log`

---

## 🔧 Troubleshooting

### Common Issues

#### 1. "cURL Error" or "HTTP Request Failed"
**Solution:**
- Check internet connection
- Verify cURL is installed: `php -m | grep curl`
- Check firewall settings

#### 2. "SMS Service Not Enabled"
**Solution:**
- Verify Twilio credentials in `/config/twilio.php`
- Check Account SID and Auth Token are correct
- Ensure phone number is in E.164 format (+1234567890)

#### 3. "Database Connection Failed"
**Solution:**
- Check `/config/database.php` settings
- Verify MySQL service is running
- Test database connection manually

#### 4. "Permission Denied" (Linux)
**Solution:**
```bash
sudo chown -R www-data:www-data /home/fm-pc-lt-285/personal/meddibuddy/
sudo chmod -R 755 /home/fm-pc-lt-285/personal/meddibuddy/
sudo chmod -R 777 /home/fm-pc-lt-285/personal/meddibuddy/logs/
```

#### 5. Cron Job Not Running
**Linux:**
```bash
# Check if cron service is running
sudo systemctl status cron

# Check cron logs
sudo tail -f /var/log/syslog | grep CRON
```

**Windows:**
- Check Task Scheduler History
- Verify user has "Log on as a service" rights
- Run task manually to test

### Debug Mode

Add this to the top of `send_reminders.php` for detailed debugging:

```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add debug output
echo "Script started at: " . date('Y-m-d H:i:s') . "\n";
```

---

## 📊 Monitoring

### Log Files Location
- **Main log**: `/logs/cron_YYYY-MM-DD.log`
- **Error log**: `/logs/error.log` (if configured)
- **System cron log**: `/var/log/cron` (Linux only)

### Log Monitoring Commands

**Linux:**
```bash
# Watch live logs
tail -f /home/fm-pc-lt-285/personal/meddibuddy/logs/cron_$(date +%Y-%m-%d).log

# Check last 50 lines
tail -n 50 /home/fm-pc-lt-285/personal/meddibuddy/logs/cron_$(date +%Y-%m-%d).log

# Search for errors
grep "ERROR\|FAILED" /home/fm-pc-lt-285/personal/meddibuddy/logs/cron_*.log
```

### Database Monitoring
Check notification logs in database:

```sql
-- Recent notifications
SELECT * FROM notification_logs ORDER BY created_at DESC LIMIT 10;

-- Success rate by channel
SELECT channel, 
       COUNT(*) as total,
       SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as successful,
       (SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as success_rate
FROM notification_logs 
WHERE DATE(created_at) = CURDATE()
GROUP BY channel;
```

---

## ⚡ Performance Tips

### 1. Optimize Cron Frequency
- **High precision needed**: Every minute
- **Standard use**: Every 5 minutes
- **Low traffic**: Every 15 minutes

### 2. Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_reminders_time ON reminders(reminder_time);
CREATE INDEX idx_notification_logs_date ON notification_logs(created_at);
```

### 3. Log Rotation
**Linux - Add to crontab:**
```bash
0 0 * * * find /home/fm-pc-lt-285/personal/meddibuddy/logs/ -name "cron_*.log" -mtime +7 -delete
```

---

## 🆘 Support

### Getting Help
1. **Check logs** first for error messages
2. **Test configuration** using the test script
3. **Verify Twilio account** status and credits
4. **Check database** for reminder data

### Contact Information
- **System Admin**: Check database connection and server settings
- **Twilio Support**: For API-related issues
- **Developer**: For code modifications

---

**Last Updated**: January 11, 2026  
**Version**: 2.0 (Twilio Integration)