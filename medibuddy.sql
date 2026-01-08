-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 03:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medibuddy`
--

-- --------------------------------------------------------

--
-- Table structure for table `blood_types`
--

CREATE TABLE `blood_types` (
  `id` int(11) NOT NULL,
  `name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_types`
--

INSERT INTO `blood_types` (`id`, `name`) VALUES
(3, 'A+'),
(4, 'A-'),
(7, 'AB+'),
(8, 'AB-'),
(5, 'B+'),
(6, 'B-'),
(1, 'O+'),
(2, 'O-');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `hospital_clinic` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `first_name`, `last_name`, `email`, `phone`, `specialization`, `hospital_clinic`, `address`, `license_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Rajesh', 'Kumar', 'dr.rajesh@hospital.com', '+91-9876543210', 'Cardiologist', 'City Heart Hospital', '123 Medical Lane, City', 'LN12345', 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(2, 'Dr. Priya', 'Sharma', 'dr.priya@clinic.com', '+91-9876543211', 'General Physician', 'Care Medical Clinic', '456 Health Street, City', 'LN12346', 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(3, 'Dr. Amit', 'Singh', 'dr.amit@hospital.com', '+91-9876543212', 'Pediatrician', 'Children Health Center', '789 Child Avenue, City', 'LN12347', 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(4, 'Dr. Neha', 'Patel', 'dr.neha@clinic.com', '+91-9876543213', 'Neurologist', 'Brain Care Hospital', '321 Brain Road, City', 'LN12348', 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(5, 'Dr. Vikram', 'Reddy', 'dr.vikram@hospital.com', '+91-9876543214', 'Orthopedic', 'Bone & Joint Clinic', '654 Bone Street, City', 'LN12349', 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `drugs`
--

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `medicine_type_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `warnings` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugs`
--

INSERT INTO `drugs` (`id`, `name`, `medicine_type_id`, `manufacturer_id`, `generic_name`, `dosage`, `unit`, `description`, `side_effects`, `warnings`, `price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Aspirin', 1, 1, 'Acetylsalicylic Acid', '500', 'mg', 'Pain reliever and fever reducer', 'Stomach upset, heartburn', 'Not for children under 3 years', 25.50, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(2, 'Amoxicillin', 1, 2, 'Amoxicillin', '250', 'mg', 'Antibiotic for bacterial infections', 'Nausea, diarrhea', 'May cause allergic reactions', 45.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(3, 'Ibuprofen', 1, 3, 'Ibuprofen', '200', 'mg', 'Anti-inflammatory pain reliever', 'Stomach pain, dizziness', 'Not recommended during pregnancy', 30.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(4, 'Metformin', 1, 4, 'Metformin', '500', 'mg', 'Blood sugar control for diabetes', 'Metallic taste, nausea', 'Monitor kidney function', 35.75, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(5, 'Vitamin D3', 2, 5, 'Cholecalciferol', '1000', 'IU', 'Vitamin supplement', 'Rare side effects', 'Keep out of reach of children', 55.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(6, 'Cough Syrup', 3, 1, 'Dextromethorphan', '10', 'ml', 'Cough relief', 'Drowsiness, dizziness', 'Do not exceed recommended dose', 65.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(7, 'Ointment', 5, 2, 'Triclosan', '20', 'g', 'Skin infection treatment', 'Skin irritation, rash', 'For external use only', 120.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15'),
(8, 'Penicillin', 4, 3, 'Penicillin G', '5', 'ml', 'Antibiotic injection', 'Allergic reactions, fever', 'Check for penicillin allergy', 150.00, 1, '2026-01-05 09:09:15', '2026-01-05 09:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender_id` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `relationship_id` int(11) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) DEFAULT NULL,
  `emergency_doctor_name` varchar(100) DEFAULT NULL,
  `emergency_doctor_phone` varchar(20) DEFAULT NULL,
  `emergency_hospital_name` varchar(150) DEFAULT NULL,
  `emergency_hospital_address` varchar(255) DEFAULT NULL,
  `emergency_hospital_phone` varchar(20) DEFAULT NULL,
  `doctor_specialization` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`id`, `user_id`, `name`, `gender_id`, `date_of_birth`, `relationship_id`, `medical_conditions`, `allergies`, `phone`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relationship`, `emergency_doctor_name`, `emergency_doctor_phone`, `emergency_hospital_name`, `emergency_hospital_address`, `emergency_hospital_phone`, `doctor_specialization`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 'Priyanka Rouniyar', 2, '2026-01-05', 4, 'awd', 'sdsd', '9810623411', 'nabin', '+9779810623411', 'SIBLING', 'dr.ram', '+977435430', 'zsw', 'city', '+9779819033801', 'phy', 1, '2026-01-05 09:50:17', '2026-01-05 09:51:41'),
(2, 4, 'swatika', 2, '2026-01-05', 4, 'fever', 'bitter guard', '9819033801', 'Priyanka Rouniyar', '+977435430', 'SIBLING', 'dr.ram', '', 'zsw', 'zxsac', '', 'phy', 1, '2026-01-05 14:57:21', '2026-01-05 14:57:21'),
(3, 1, 'Test Member', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-07 17:22:06', '2026-01-07 17:22:06'),
(4, 5, 'rukhshar zeba', 2, '2024-12-05', 4, '', '', '9804373217', 'Priyanka Rouniyar', '+9779810623411', 'SIBLING', 'dr.ram', '', 'city hospital', 'ktm', '', 'phy', 1, '2026-01-07 18:55:51', '2026-01-07 18:55:51');

-- --------------------------------------------------------

--
-- Table structure for table `frequencies`
--

CREATE TABLE `frequencies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `times_per_day` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `frequencies`
--

INSERT INTO `frequencies` (`id`, `name`, `times_per_day`) VALUES
(1, 'Once Daily', 1),
(2, 'Twice Daily', 2),
(3, 'Thrice Daily', 3),
(4, 'Every 4 Hours', 6),
(5, 'Every 6 Hours', 4),
(6, 'Every 8 Hours', 3),
(7, 'Every 12 Hours', 2),
(8, 'As Needed', 0);

-- --------------------------------------------------------

--
-- Table structure for table `genders`
--

CREATE TABLE `genders` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genders`
--

INSERT INTO `genders` (`id`, `name`) VALUES
(2, 'Female'),
(1, 'Male'),
(3, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manufacturers`
--

INSERT INTO `manufacturers` (`id`, `name`, `country`) VALUES
(1, 'Pharma Corp', 'India'),
(2, 'Health Labs', 'USA'),
(3, 'Wellness Plus', 'India'),
(4, 'Global Meds', 'Germany'),
(5, 'MediCare', 'UK');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `medicine_type_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `medicine_type_id`, `manufacturer_id`, `generic_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Aspirin 500mg', 1, 1, 'Acetylsalicylic Acid', 'Pain reliever and fever reducer', 1, '2026-01-05 09:09:16'),
(2, 'Amoxicillin 250mg', 1, 2, 'Amoxicillin', 'Antibiotic for bacterial infections', 1, '2026-01-05 09:09:16'),
(3, 'Ibuprofen 200mg', 1, 3, 'Ibuprofen', 'Anti-inflammatory pain reliever', 1, '2026-01-05 09:09:16'),
(4, 'Metformin 500mg', 1, 4, 'Metformin', 'Blood sugar control for diabetes', 1, '2026-01-05 09:09:16'),
(5, 'Vitamin D3 1000IU', 2, 5, 'Cholecalciferol', 'Vitamin supplement', 1, '2026-01-05 09:09:16'),
(6, 'paracetamol', 2, 4, 'zswq', 'sSQW', 1, '2026-01-07 15:21:35'),
(7, 'paracetamol', 2, 5, 'zswq', 'rgwg', 1, '2026-01-07 17:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_compositions`
--

CREATE TABLE `medicine_compositions` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `composition_name` varchar(100) NOT NULL,
  `strength` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_compositions`
--

INSERT INTO `medicine_compositions` (`id`, `medicine_id`, `composition_name`, `strength`) VALUES
(1, 1, 'Acetylsalicylic Acid', '500mg'),
(2, 2, 'Amoxicillin', '250mg'),
(3, 3, 'Ibuprofen', '200mg'),
(4, 4, 'Metformin', '500mg'),
(5, 5, 'Cholecalciferol', '1000IU');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_types`
--

CREATE TABLE `medicine_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_types`
--

INSERT INTO `medicine_types` (`id`, `name`) VALUES
(2, 'Capsule'),
(5, 'Cream'),
(4, 'Injection'),
(3, 'Liquid'),
(7, 'Powder'),
(6, 'Syrup'),
(1, 'Tablet');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` enum('email','sms','both') DEFAULT 'email',
  `recipient_email` varchar(100) DEFAULT NULL,
  `recipient_phone` varchar(15) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `sms_number` varchar(15) DEFAULT NULL,
  `notification_minutes_before` int(11) DEFAULT 15 COMMENT 'Send notification X minutes before reminder time',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `family_member_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `file_uploaded_at` timestamp NULL DEFAULT NULL,
  `prescription_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `user_id`, `family_member_id`, `doctor_id`, `created_by_user_id`, `description`, `prescription_file`, `file_uploaded_at`, `prescription_date`, `expiry_date`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 3, 1, 2, 3, 'g', NULL, '2026-01-07 18:21:13', '2026-01-07', NULL, 1, '2026-01-07 18:21:13', '2026-01-07 18:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_medicine`
--

CREATE TABLE `prescription_medicine` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relationships`
--

CREATE TABLE `relationships` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relationships`
--

INSERT INTO `relationships` (`id`, `name`) VALUES
(5, 'Brother'),
(9, 'Daughter'),
(3, 'Father'),
(10, 'Grandfather'),
(11, 'Grandmother'),
(7, 'Husband'),
(2, 'Mother'),
(1, 'Self'),
(4, 'Sister'),
(8, 'Son'),
(6, 'Wife');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `prescription_medicine_id` int(11) NOT NULL,
  `family_member_id` int(11) NOT NULL,
  `frequency_id` int(11) NOT NULL,
  `reminder_time` time NOT NULL,
  `reminder_days` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_gateway`
--

CREATE TABLE `sms_gateway` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gateway_type` enum('twilio','nexmo','custom') DEFAULT 'custom',
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `sender_id` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`) VALUES
(1, 'Active'),
(3, 'Completed'),
(2, 'Inactive'),
(4, 'Paused'),
(5, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_doctor_name` varchar(100) DEFAULT NULL,
  `emergency_doctor_phone` varchar(20) DEFAULT NULL,
  `emergency_hospital_name` varchar(150) DEFAULT NULL,
  `emergency_hospital_address` varchar(255) DEFAULT NULL,
  `emergency_hospital_phone` varchar(20) DEFAULT NULL,
  `doctor_specialization` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_doctor_name`, `emergency_doctor_phone`, `emergency_hospital_name`, `emergency_hospital_address`, `emergency_hospital_phone`, `doctor_specialization`, `email`, `password`, `user_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin@medibuddy.com', '$2y$10$ZTsOBL/V2UdPHQqBoBuI8ur8jU4Wl0R2/.5J9dtRpq5iugHl6SmGq', 'admin', 1, '2026-01-05 09:09:17', '2026-01-06 06:08:47'),
(2, 'John', 'Doe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'john@example.com', '$2y$10$iL8m9Zq5J2.vZvNbKqPqNef7vZvNbKqPqNef7vZvNbKqPqNef7vZvNb', 'user', 1, '2026-01-05 09:09:18', '2026-01-05 09:09:18'),
(3, 'Priyanka', 'Rouniyar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rouniyarpriyanka2019@gmail.com', '$2y$10$g.HHEDFnfKfZhig1Q6TfAelQPvwqHINf2tjJ0jENfMUiNgRTJFZRy', 'user', 1, '2026-01-05 09:15:52', '2026-01-05 09:15:52'),
(4, 'kriti', 'rouniyar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'kriti@gmail.com', '$2y$10$Ck9xvgYR0V4d6fU.LpETQODo4OyvIUx1D9y5gy.l5o83WUwfTHt1C', 'user', 1, '2026-01-05 14:53:29', '2026-01-05 14:53:29'),
(5, 'rukshar', 'zeba', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rukshar2019@gmail.com', '$2y$10$GzpZLr4XuQCp/i/jqCDcyuGoJPvZP0D8fxG4nCRSzXpftdxAxmgSe', 'user', 1, '2026-01-07 18:54:32', '2026-01-07 18:54:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blood_types`
--
ALTER TABLE `blood_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_doctor_id` (`id`);

--
-- Indexes for table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_type_id` (`medicine_type_id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `idx_drug_id` (`id`);

--
-- Indexes for table `family_members`
--
ALTER TABLE `family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gender_id` (`gender_id`),
  ADD KEY `relationship_id` (`relationship_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_emergency_contact` (`emergency_contact_phone`),
  ADD KEY `idx_emergency_medical` (`emergency_doctor_phone`,`emergency_hospital_phone`);

--
-- Indexes for table `frequencies`
--
ALTER TABLE `frequencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `genders`
--
ALTER TABLE `genders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_type_id` (`medicine_type_id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `idx_medicine_id` (`id`);

--
-- Indexes for table `medicine_compositions`
--
ALTER TABLE `medicine_compositions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `medicine_types`
--
ALTER TABLE `medicine_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reminder_id` (`reminder_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `family_member_id` (`family_member_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`);

--
-- Indexes for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `idx_prescription_id` (`prescription_id`);

--
-- Indexes for table `relationships`
--
ALTER TABLE `relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_medicine_id` (`prescription_medicine_id`),
  ADD KEY `frequency_id` (`frequency_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `idx_family_member_id` (`family_member_id`);

--
-- Indexes for table `sms_gateway`
--
ALTER TABLE `sms_gateway`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blood_types`
--
ALTER TABLE `blood_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `frequencies`
--
ALTER TABLE `frequencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `genders`
--
ALTER TABLE `genders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medicine_compositions`
--
ALTER TABLE `medicine_compositions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medicine_types`
--
ALTER TABLE `medicine_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `relationships`
--
ALTER TABLE `relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms_gateway`
--
ALTER TABLE `sms_gateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drugs`
--
ALTER TABLE `drugs`
  ADD CONSTRAINT `drugs_ibfk_1` FOREIGN KEY (`medicine_type_id`) REFERENCES `medicine_types` (`id`),
  ADD CONSTRAINT `drugs_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`);

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `family_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `family_members_ibfk_2` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`),
  ADD CONSTRAINT `family_members_ibfk_3` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`);

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`medicine_type_id`) REFERENCES `medicine_types` (`id`),
  ADD CONSTRAINT `medicines_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`);

--
-- Constraints for table `medicine_compositions`
--
ALTER TABLE `medicine_compositions`
  ADD CONSTRAINT `medicine_compositions_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`reminder_id`) REFERENCES `reminders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`family_member_id`) REFERENCES `family_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_4` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  ADD CONSTRAINT `prescription_medicine_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_medicine_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`prescription_medicine_id`) REFERENCES `prescription_medicine` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`family_member_id`) REFERENCES `family_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reminders_ibfk_3` FOREIGN KEY (`frequency_id`) REFERENCES `frequencies` (`id`),
  ADD CONSTRAINT `reminders_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`);

--
-- Constraints for table `sms_gateway`
--
ALTER TABLE `sms_gateway`
  ADD CONSTRAINT `sms_gateway_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
