# MediBuddy Project Documentation

## Overview
MediBuddy is a medication reminder application designed to help users manage their prescriptions and receive timely notifications for taking their medicines. The project includes features for user management, prescription tracking, and multi-channel notifications (email, SMS, and WhatsApp).

---

## Project Structure
The project is organized as follows:

### Root Directory
- **index.php**: Entry point for the application.
- **medibuddy.sql**: Database schema for the project.
- **test_reminder.php**: Script for testing reminder functionality.
- **test.php**: General testing script.

### Folders
#### **admin/**
Contains admin panel scripts for managing users, medicines, and other entities.
- `dashboard.php`: Admin dashboard.
- `doctors.php`: Manage doctors.
- `drugs.php`: Manage drugs.
- `login.php`: Admin login.
- `manufacturers.php`: Manage manufacturers.
- `medicines.php`: Manage medicines.
- `reset-password.php`: Reset admin password.
- `users.php`: Manage users.
- `verify-medicines.php`: Verify medicine details.

#### **assets/**
Contains static assets like CSS and JavaScript files.
- **css/**: Stylesheets.
  - `style.css`: Main stylesheet.
- **js/**: JavaScript files.
  - `main.js`: Core JavaScript functionality.
  - `script.js`: Additional scripts.
- **uploads/**: Directory for uploaded files.

#### **auth/**
Handles user authentication.
- `login.php`: User login.
- `logout.php`: User logout.
- `register.php`: User registration.

#### **config/**
Configuration files for the project.
- `database.php`: Database connection settings.
- `twilio.php`: Twilio API configuration for SMS notifications.

#### **cron/**
Cron jobs for automated tasks.
- `scalable_reminders.php`: Scalable reminder handling.
- `send_reminders.php`: Sends reminders to users.
- `send_reminders_backup.php`: Backup reminder script.
- `test-cron.php`: Test cron functionality.

#### **database/**
Database-related files.
- `queue_schema.sql`: Schema for the queue system.

#### **docs/**
Documentation for the project.
- `CRON_SETUP_GUIDE.md`: Guide for setting up cron jobs.
- `phpmailer_handling.md`: Guide for PHPMailer integration.

#### **includes/**
Reusable components and utilities.
- `footer.php`: Footer component.
- `functions.php`: General utility functions.
- `header.php`: Header component.
- `notification-engine.php`: Notification handling logic.

#### **scripts/**
Shell scripts for setup and maintenance.
- `scalable_setup.sh`: Script for scalable setup.

#### **user/**
User-facing features and pages.
- `dashboard.php`: User dashboard.
- `family-members.php`: Manage family members.
- `notification-history.php`: View notification history.
- `notification-settings.php`: Configure notification preferences.
- `prescriptions.php`: Manage prescriptions.
- `reminders.php`: Manage reminders.

#### **vendor/**
Third-party libraries.
- **PHPMailer/**: Library for sending emails.

---

## Features
### User Management
- Registration, login, and logout.
- Manage family members and their prescriptions.

### Notifications
- Multi-channel notifications (email, SMS, WhatsApp).
- Configurable notification preferences.

### Admin Panel
- Manage users, medicines, and manufacturers.
- View and verify prescriptions.

### Cron Jobs
- Automated reminders using `send_reminders.php`.
- Scalable reminder handling with `scalable_reminders.php`.

---

## Setup Instructions
### Prerequisites
- PHP 7.4 or higher.
- MySQL database.
- Web server (e.g., Apache or Nginx).

### Installation
1. Clone the repository.
2. Import the `medibuddy.sql` file into your MySQL database.
3. Update the database configuration in `config/database.php`.
4. Set up the `vendor/PHPMailer` library for email notifications.
5. Configure Twilio in `config/twilio.php` for SMS notifications.
6. Set up cron jobs for automated reminders.

### Cron Job Setup
Refer to `docs/CRON_SETUP_GUIDE.md` for detailed instructions.

---

## Testing
- Use `test_reminder.php` to test reminder functionality.
- Check logs in the `logs/` directory for debugging.

---

## References
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer/wiki)
- [Twilio API Documentation](https://www.twilio.com/docs/usage/api)

---

For further assistance, contact the development team.