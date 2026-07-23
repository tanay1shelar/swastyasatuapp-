-- Database Creation Script for HMCMS

CREATE DATABASE IF NOT EXISTS `hmcms_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hmcms_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') DEFAULT 'superadmin',
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default user (username: admin, password: password123)
-- Hash generated via password_hash('password123', PASSWORD_DEFAULT)
INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`, `full_name`) VALUES
('admin', '$2y$10$/lrQbAa316hKXc14aV8t/uJM.w2EUemTmV7AT/BYJWjXW7YYtRdj.', 'superadmin', 'Super Admin');

-- 2. Centers Table
CREATE TABLE IF NOT EXISTS `centers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `center_code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `state` varchar(50) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `center_code` (`center_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Camps Table
CREATE TABLE IF NOT EXISTS `camps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `center_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Scheduled',
  `patients_treated` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`center_id`) REFERENCES `centers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Activity Logs Table (Timeline)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_type` varchar(50) NOT NULL, -- e.g., 'camp_finished', 'center_registered'
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. System Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed some dummy data for Centers so dashboard isn't completely empty
INSERT IGNORE INTO `centers` (`center_code`, `name`, `email`, `state`, `district`, `contact_person`, `contact_number`, `status`) VALUES
('HC-1001', 'City General Hospital', 'hospital@citygen.com', 'Maharashtra', 'Mumbai', 'Dr. Alan Smith', '+1 234 567 8900', 'Active'),
('HC-1002', 'Northside Community Clinic', 'contact@northside.org', 'Karnataka', 'Bengaluru', 'Sarah Jenkins', '+1 234 567 8901', 'Active'),
('HC-1003', 'Westend Rural Health Post', 'westend@ruralhealth.gov', 'Delhi', 'New Delhi', 'Mark Ruffalo', '+1 234 567 8902', 'Inactive');

-- Seed dummy data for Activity Logs
INSERT IGNORE INTO `activity_logs` (`action_type`, `title`, `description`) VALUES
('camp_finished', 'Camp Finished: Northside Eye Clinic', 'Successfully treated 142 patients.'),
('center_registered', 'New Center Registered', '"City General Hospital" was added to the network.'),
('backup', 'Automated Backup Completed', 'System database backed up successfully to secure server.');
