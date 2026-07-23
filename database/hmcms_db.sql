-- Healthcare & Medical Camp Management System (HMCMS)
-- Database Initialization Script

CREATE DATABASE IF NOT EXISTS `hmcms_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hmcms_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('super-admin', 'camp-admin', 'doctor', 'health-worker', 'citizen') NOT NULL DEFAULT 'citizen',
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Medical Camps Table
CREATE TABLE IF NOT EXISTS `camps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `camp_name` VARCHAR(150) NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `target_services` VARCHAR(255) NOT NULL,
    `status` ENUM('Planned', 'Active', 'Completed') DEFAULT 'Planned',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Medicine Inventory Table
CREATE TABLE IF NOT EXISTS `medicines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `medicine_name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `supplier` VARCHAR(100) DEFAULT NULL,
    `expiry_date` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('In Stock', 'Low Stock', 'Out of Stock') DEFAULT 'In Stock',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Registrations Table
CREATE TABLE IF NOT EXISTS `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `contact` VARCHAR(50) NOT NULL,
    `camp_name` VARCHAR(150) NOT NULL,
    `token_number` VARCHAR(50) NOT NULL,
    `symptoms` TEXT DEFAULT NULL,
    `status` ENUM('Checked In', 'Awaiting Arrival', 'Completed') DEFAULT 'Awaiting Arrival',
    `registered_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Prescriptions Table
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_name` VARCHAR(100) NOT NULL,
    `token_number` VARCHAR(50) NOT NULL,
    `doctor_name` VARCHAR(100) NOT NULL,
    `camp_name` VARCHAR(150) NOT NULL,
    `diagnosis` TEXT NOT NULL,
    `prescribed_medicines` TEXT NOT NULL,
    `instructions` TEXT DEFAULT NULL,
    `date_issued` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pre-seed default camps data
INSERT INTO `camps` (`camp_name`, `location`, `start_date`, `end_date`, `target_services`, `status`) VALUES
('Camp Sunshine Health Drive', 'Community Hall, Sector 4, NY', '2026-07-25', '2026-07-27', 'General Physicians, Pediatrics, Blood Tests', 'Active'),
('Eastside Community Eye Camp', 'East District Civic Center', '2026-08-02', '2026-08-04', 'Ophthalmologists, Vision Testing', 'Planned'),
('Maternal & Child Health Camp', 'North Suburb Healthcare Clinic', '2026-08-12', '2026-08-14', 'Gynecologists, Immunization Nurses', 'Planned');
