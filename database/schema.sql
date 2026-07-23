<<<<<<< HEAD
-- =========================================================================
-- Healthcare & Medical Camp Management System (HMCMS)
-- Database Foundation Schema (MySQL / XAMPP Compatible)
-- Database Name: hmcms
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `hmcms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hmcms`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `patient_attendance`;
DROP TABLE IF EXISTS `patient_verification`;
DROP TABLE IF EXISTS `patients`;
DROP TABLE IF EXISTS `medical_camps`;
DROP TABLE IF EXISTS `health_workers`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------------------
-- Table: users (System administrative accounts)
-- -------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Administrator', 'Doctor', 'Registrar', 'Camp Coordinator') NOT NULL,
    `profile_photo` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('Active', 'Suspended') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_empid` (`employee_id`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: health_workers (Staff demographics & credentials)
-- -------------------------------------------------------------------------
CREATE TABLE `health_workers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `specialization` VARCHAR(100) DEFAULT NULL,
    `experience_years` INT DEFAULT 0,
    `qualification` VARCHAR(100) DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `dob` DATE DEFAULT NULL,
    `gender` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `assigned_camp` VARCHAR(150) DEFAULT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `users` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: medical_camps (Scheduled outreach health camps)
-- -------------------------------------------------------------------------
CREATE TABLE `medical_camps` (
    `camp_id` INT AUTO_INCREMENT PRIMARY KEY,
    `camp_name` VARCHAR(150) NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `doctor_name` VARCHAR(100) NOT NULL,
    `coordinator` VARCHAR(100) NOT NULL,
    `date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('Scheduled', 'Active', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    INDEX `idx_camps_status` (`status`),
    INDEX `idx_camps_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: patients (Attendee demographic intake registries)
-- -------------------------------------------------------------------------
CREATE TABLE `patients` (
    `patient_id` VARCHAR(50) PRIMARY KEY,
    `registration_number` VARCHAR(50) NOT NULL UNIQUE,
    `token_number` VARCHAR(10) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `middle_name` VARCHAR(100) DEFAULT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `dob` DATE NOT NULL,
    `age` INT NOT NULL,
    `blood_group` VARCHAR(5) DEFAULT NULL,
    `aadhaar` VARCHAR(20) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `alternate_phone` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `occupation` VARCHAR(100) DEFAULT NULL,
    `address` TEXT NOT NULL,
    `village` VARCHAR(100) DEFAULT NULL,
    `taluka` VARCHAR(100) DEFAULT NULL,
    `district` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `pincode` VARCHAR(10) DEFAULT NULL,
    `height` DECIMAL(5,2) DEFAULT NULL,
    `weight` DECIMAL(5,2) DEFAULT NULL,
    `bmi` DECIMAL(4,2) DEFAULT NULL,
    `blood_pressure` VARCHAR(20) DEFAULT NULL,
    `pulse` INT DEFAULT NULL,
    `temperature` DECIMAL(4,1) DEFAULT NULL,
    `medical_history` TEXT DEFAULT NULL,
    `allergies` TEXT DEFAULT NULL,
    `current_medication` TEXT DEFAULT NULL,
    `guardian_name` VARCHAR(100) DEFAULT NULL,
    `guardian_phone` VARCHAR(20) DEFAULT NULL,
    `camp_id` INT DEFAULT NULL,
    `status` ENUM('Registered', 'Verified', 'In Triage', 'In Consultation', 'Completed', 'Rejected', 'Pending ID') DEFAULT 'Registered',
    `photo` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`camp_id`) REFERENCES `medical_camps` (`camp_id`) ON DELETE SET NULL,
    INDEX `idx_patients_status` (`status`),
    INDEX `idx_patients_phone` (`phone`),
    INDEX `idx_patients_aadhaar` (`aadhaar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: patient_verification (OTP & biometric audit trail checks)
-- -------------------------------------------------------------------------
CREATE TABLE `patient_verification` (
    `verification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_id` VARCHAR(50) NOT NULL,
    `verification_status` ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    `verified_by` INT DEFAULT NULL,
    `verification_date` DATETIME DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
    FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_verif_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: patient_attendance (Daily check-in / check-out timesheets)
-- -------------------------------------------------------------------------
CREATE TABLE `patient_attendance` (
    `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_id` VARCHAR(50) NOT NULL,
    `check_in` VARCHAR(20) NOT NULL,
    `check_out` VARCHAR(20) DEFAULT '--',
    `attendance_status` ENUM('Present', 'Absent', 'Late') DEFAULT 'Present',
    `token_number` VARCHAR(10) NOT NULL,
    FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
    INDEX `idx_attendance_status` (`attendance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: notifications (Dynamic clinical warnings)
-- -------------------------------------------------------------------------
CREATE TABLE `notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    `status` ENUM('Unread', 'Read') DEFAULT 'Unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- Table: activity_logs (Security audit trail logs)
-- -------------------------------------------------------------------------
CREATE TABLE `activity_logs` (
    `activity_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `activity` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SEED DATA
-- =========================================================================

-- 1. Users: Seed 5 Health Workers (password BCrypt hash of 'Password@123')
INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_photo`, `status`) VALUES
(1, 'EMP-2026-9042', 'Dr. Aditi Sharma', 'aditi.sharma@apollo.com', '+91 98765 43210', '$2y$10$w3pScb7LczWBDwW1QzPzzOx130k.VEUPI.lpE6lH1WD4w/XPxoVY.', 'Administrator', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=150', 'Active'),
(2, 'EMP-2026-1011', 'Dr. Rajesh Verma', 'rajesh.verma@fortis.com', '+91 98765 43211', '$2y$10$w3pScb7LczWBDwW1QzPzzOx130k.VEUPI.lpE6lH1WD4w/XPxoVY.', 'Doctor', 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&q=80&w=150', 'Active'),
(3, 'EMP-2026-5523', 'Neha Patel', 'neha.patel@hmcms.org', '+91 98765 43212', '$2y$10$w3pScb7LczWBDwW1QzPzzOx130k.VEUPI.lpE6lH1WD4w/XPxoVY.', 'Registrar', 'https://images.unsplash.com/photo-1594824813573-246434de83fb?auto=format&fit=crop&q=80&w=150', 'Active'),
(4, 'EMP-2026-4402', 'Vikram Singh', 'vikram.singh@hmcms.org', '+91 98765 43213', '$2y$10$w3pScb7LczWBDwW1QzPzzOx130k.VEUPI.lpE6lH1WD4w/XPxoVY.', 'Camp Coordinator', 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&q=80&w=150', 'Active'),
(5, 'EMP-2026-8809', 'Dr. Preeti Nair', 'preeti.nair@apollo.com', '+91 98765 43214', '$2y$10$w3pScb7LczWBDwW1QzPzzOx130k.VEUPI.lpE6lH1WD4w/XPxoVY.', 'Doctor', 'https://images.unsplash.com/photo-1594824813573-246434de83fb?auto=format&fit=crop&q=80&w=150', 'Active');

-- 2. Health Workers details mapping
INSERT INTO `health_workers` (`employee_id`, `full_name`, `email`, `phone`, `specialization`, `experience_years`, `qualification`, `dob`, `gender`, `address`, `assigned_camp`) VALUES
('EMP-2026-9042', 'Dr. Aditi Sharma', 'aditi.sharma@apollo.com', '+91 98765 43210', 'Cardiology & General Medicine', 12, 'MD, MBBS', '1988-11-24', 'Female', 'Apollo Hospital Residency, New Delhi, India', 'Apollo Rural Health Camp - Phase 1'),
('EMP-2026-1011', 'Dr. Rajesh Verma', 'rajesh.verma@fortis.com', '+91 98765 43211', 'General Medicine', 8, 'MBBS', '1990-05-15', 'Male', 'Fortis Staff Quarters, Alwar, Rajasthan', 'Fortis Cardiology & Diabetes Camp'),
('EMP-2026-5523', 'Neha Patel', 'neha.patel@hmcms.org', '+91 98765 43212', 'Clinical Registrar', 4, 'BSc Nursing', '1995-08-12', 'Female', 'Panchayat Colony, Palwal, Haryana', 'Rotary Pediatric Outreach Camp'),
('EMP-2026-4402', 'Vikram Singh', 'vikram.singh@hmcms.org', '+91 98765 43213', 'Public Health Triage', 6, 'BPharma', '1992-04-30', 'Male', 'Block Colony, Nuh, Haryana', 'Apollo Rural Health Camp - Phase 1'),
('EMP-2026-8809', 'Dr. Preeti Nair', 'preeti.nair@apollo.com', '+91 98765 43214', 'Pediatrics', 7, 'MBBS, DCH', '1991-09-20', 'Female', 'Apollo Vasant Kunj Staff Residencies, New Delhi', 'Rotary Pediatric Outreach Camp');

-- 3. Seeding 10 Camps
INSERT INTO `medical_camps` (`camp_id`, `camp_name`, `location`, `doctor_name`, `coordinator`, `date`, `start_time`, `end_time`, `status`) VALUES
(1, 'Apollo Rural Health Camp - Phase 1', 'Community Health Centre, Palwal, Haryana', 'Dr. Aditi Sharma', 'Vikram Singh', '2026-07-10', '09:00:00', '17:00:00', 'Active'),
(2, 'Fortis Cardiology & Diabetes Camp', 'Zila Parishad Hall, Alwar, Rajasthan', 'Dr. Rajesh Verma', 'Neha Patel', '2026-08-01', '10:00:00', '18:00:00', 'Scheduled'),
(3, 'Rotary Pediatric Outreach Camp', 'Panchayat Bhawan, Palwal, Haryana', 'Dr. Preeti Nair', 'Vikram Singh', '2026-07-19', '09:00:00', '16:00:00', 'Active'),
(4, 'HMCMS Diabetic & Hypertension Station', 'Primary School Room, Nuh, Haryana', 'Dr. Rajesh Verma', 'Neha Patel', '2026-07-18', '09:30:00', '17:30:00', 'Completed'),
(5, 'Rural Eye & General Triage Checkup', 'Village Chaupal, Palwal, Haryana', 'Dr. Aditi Sharma', 'Vikram Singh', '2026-07-15', '09:00:00', '17:00:00', 'Completed'),
(6, 'Red Cross Basic Vaccine Distribution', 'Community Hall, Mathura, UP', 'Dr. Preeti Nair', 'Neha Patel', '2026-08-10', '09:00:00', '15:00:00', 'Scheduled'),
(7, 'Primary Care & Asthma Camp', 'Panchayat Hall, Hodal, Haryana', 'Dr. Aditi Sharma', 'Vikram Singh', '2026-07-22', '10:00:00', '17:00:00', 'Scheduled'),
(8, 'Fortis Maternal Care & Vitals Triage', 'Govt Dispensary, Mewat, Haryana', 'Dr. Preeti Nair', 'Neha Patel', '2026-08-15', '09:00:00', '16:00:00', 'Scheduled'),
(9, 'Elderly Health Screening Clinic', 'Old Age Home, Palwal, Haryana', 'Dr. Aditi Sharma', 'Vikram Singh', '2026-07-12', '09:00:00', '16:00:00', 'Completed'),
(10, 'Monsoon Fever & Dengue Prevention Station', 'Block Panchayat Room, Alwar, Rajasthan', 'Dr. Rajesh Verma', 'Neha Patel', '2026-07-20', '09:00:00', '17:00:00', 'Scheduled');

-- 4. Seeding 100 Patients (Diverse dummy rows)
INSERT INTO `patients` (`patient_id`, `registration_number`, `token_number`, `first_name`, `middle_name`, `last_name`, `gender`, `dob`, `age`, `blood_group`, `aadhaar`, `phone`, `alternate_phone`, `email`, `occupation`, `address`, `village`, `taluka`, `district`, `state`, `pincode`, `height`, `weight`, `bmi`, `blood_pressure`, `pulse`, `temperature`, `medical_history`, `allergies`, `current_medication`, `guardian_name`, `guardian_phone`, `camp_id`, `status`, `photo`) VALUES
('PAT-001', 'REG-200001', '#201', 'Aarav', '', 'Sharma', 'Male', '1984-05-12', 42, 'O+', '3849 2039 1049', '+91 98765 10293', '', 'aarav.sharma@gmail.com', 'Farmer', 'House 12, Village 1, Palwal, Haryana', 'Village 1', 'Hodal Block', 'Palwal', 'Haryana', '121102', 172.5, 68.2, 22.9, '120/80', 72, 98.4, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-002', 'REG-200002', '#202', 'Sunita', '', 'Verma', 'Female', '1995-10-22', 30, 'A+', '8930 2049 2039', '+91 97862 39021', '', 'sunita.verma@gmail.com', 'Housewife', 'House 43, Village 3, Palwal, Haryana', 'Village 3', 'Hodal Block', 'Palwal', 'Haryana', '121102', 158.0, 54.0, 21.6, '118/76', 68, 97.9, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-003', 'REG-200003', '#203', 'Rohan', '', 'Kumar', 'Male', '1970-03-15', 56, 'B+', '4029 3920 1029', '+91 96543 21098', '', 'rohan.kumar@gmail.com', 'Labourer', 'House 102, Village 7, Palwal, Haryana', 'Village 7', 'Hodal Block', 'Palwal', 'Haryana', '121102', 168.0, 62.0, 22.0, '138/92', 78, 98.6, 'Hypertension history', 'None', 'Amlodipine 5mg', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-004', 'REG-200004', '#204', 'Ananya', '', 'Singh', 'Female', '2010-07-08', 15, 'AB+', '3920 2049 1042', '+91 93456 78901', '', 'ananya.singh@gmail.com', 'Student', 'House 22, Village 15, Palwal, Haryana', 'Village 15', 'Hodal Block', 'Palwal', 'Haryana', '121102', 152.0, 42.0, 18.2, '110/70', 80, 98.2, 'Asthma', 'None', 'Albuterol inhaler', 'Karan Singh', '+91 93456 78900', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-005', 'REG-200005', '#205', 'Karan', '', 'Patel', 'Male', '1988-12-14', 37, 'O-', '2039 1029 3920', '+91 91234 56789', '', 'karan.patel@gmail.com', 'Shopkeeper', 'House 56, Village 4, Palwal, Haryana', 'Village 4', 'Hodal Block', 'Palwal', 'Haryana', '121102', 175.0, 78.5, 25.6, '124/82', 70, 98.8, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-006', 'REG-200006', '#206', 'Diya', '', 'Nair', 'Female', '2005-02-18', 21, 'A-', '1029 3920 2049', '+91 92345 67890', '', 'diya.nair@gmail.com', 'Student', 'House 89, Village 2, Palwal, Haryana', 'Village 2', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 52.0, 20.3, '112/74', 74, 98.0, 'None', 'Dust allergy', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-007', 'REG-200007', '#207', 'Vihaan', '', 'Reddy', 'Male', '1965-09-05', 60, 'B-', '3920 1029 4029', '+91 93456 12345', '', 'vihaan.reddy@gmail.com', 'Farmer', 'House 112, Village 9, Palwal, Haryana', 'Village 9', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 65.0, 22.5, '136/88', 76, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-008', 'REG-200008', '#208', 'Meera', '', 'Rao', 'Female', '1952-04-12', 74, 'AB-', '1029 4029 3920', '+91 94567 23456', '', 'meera.rao@gmail.com', 'Retired', 'House 15, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 154.0, 48.0, 20.2, '142/90', 82, 98.1, 'Mild arthritis', 'Lactose intolerant', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-009', 'REG-200009', '#209', 'Arjun', '', 'Joshi', 'Male', '1980-11-20', 45, 'O+', '4029 2049 3920', '+91 95678 34567', '', 'arjun.joshi@gmail.com', 'Teacher', 'House 73, Village 11, Palwal, Haryana', 'Village 11', 'Hodal Block', 'Palwal', 'Haryana', '121102', 178.0, 82.0, 25.9, '126/82', 72, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-010', 'REG-200010', '#210', 'Aditi', '', 'Gupta', 'Female', '1992-07-25', 33, 'A+', '2049 3920 1029', '+91 96789 45678', '', 'aditi.gupta@gmail.com', 'Housewife', 'House 5, Village 5, Palwal, Haryana', 'Village 5', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 58.0, 22.1, '118/76', 69, 98.3, 'Migraine', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-011', 'REG-200011', '#211', 'Vijay', '', 'Mehta', 'Male', '1962-02-15', 64, 'B+', '3920 1042 2049', '+91 97890 56789', '', 'vijay.mehta@gmail.com', 'Driver', 'House 95, Village 19, Palwal, Haryana', 'Village 19', 'Hodal Block', 'Palwal', 'Haryana', '121102', 166.0, 70.0, 25.4, '134/86', 75, 98.4, 'Hypertension history', 'None', 'Amlodipine 5mg', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-012', 'REG-200012', '#212', 'Kavita', '', 'Mishra', 'Female', '1975-06-18', 50, 'AB+', '1042 2049 3920', '+91 98901 67890', '', 'kavita.mishra@gmail.com', 'Housewife', 'House 142, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 156.0, 66.0, 27.1, '122/80', 71, 98.0, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-013', 'REG-200013', '#213', 'Sanjay', '', 'Pandey', 'Male', '1973-10-10', 52, 'O-', '2049 3920 1042', '+91 99012 78901', '', 'sanjay.pandey@gmail.com', 'Labourer', 'House 88, Village 14, Palwal, Haryana', 'Village 14', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 72.0, 24.9, '128/84', 73, 98.6, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-014', 'REG-200014', '#214', 'Sita', '', 'Trivedi', 'Female', '1948-03-24', 78, 'A-', '3920 1042 2049', '+91 90123 89012', '', 'sita.trivedi@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-015', 'REG-200015', '#215', 'Krishna', '', 'Gupta', 'Male', '1990-08-30', 35, 'B-', '1042 2049 3920', '+91 91234 90123', '', 'krishna.gupta@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-016', 'REG-200016', '#216', 'Priya', '', 'Chatterjee', 'Female', '1998-05-15', 28, 'AB-', '2049 3920 1042', '+91 92345 01234', '', 'priya.chatterjee@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-017', 'REG-200017', '#217', 'Abhishek', '', 'Mukherjee', 'Male', '1983-09-12', 42, 'O+', '3920 1042 2049', '+91 93456 12340', '', 'abhishek.mukherjee@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-018', 'REG-200018', '#218', 'Ritu', '', 'Banerjee', 'Female', '1987-12-05', 38, 'A+', '1042 2049 3920', '+91 94567 23450', '', 'ritu.banerjee@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-019', 'REG-200019', '#219', 'Manoj', '', 'Dutta', 'Male', '1977-04-18', 49, 'B+', '2049 3920 1042', '+91 95678 34560', '', 'manoj.dutta@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-020', 'REG-200020', '#220', 'Shweta', '', 'Sen', 'Female', '1990-07-28', 35, 'AB+', '3920 1042 2049', '+91 96789 45670', '', 'shweta.sen@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 1, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-021', 'REG-200021', '#221', 'Amit', '', 'Das', 'Male', '1982-11-15', 43, 'O-', '1042 2049 3920', '+91 97890 56780', '', 'amit.das@gmail.com', 'Shopkeeper', 'House 42, Village 1, Alwar, Rajasthan', 'Village 1', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 174.0, 72.0, 23.8, '120/80', 72, 98.4, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-022', 'REG-200022', '#222', 'Kiran', '', 'Choudhury', 'Female', '1985-05-20', 41, 'A-', '2049 3920 1042', '+91 98901 67890', '', 'kiran.choudhury@gmail.com', 'Housewife', 'House 5, Village 15, Alwar, Rajasthan', 'Village 15', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 160.0, 58.0, 22.7, '118/76', 69, 98.3, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-023', 'REG-200023', '#223', 'Rahul', '', 'Bose', 'Male', '1978-02-15', 48, 'B-', '3920 1042 2049', '+91 99012 78900', '', 'rahul.bose@gmail.com', 'Teacher', 'House 112, Village 6, Alwar, Rajasthan', 'Village 6', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-024', 'REG-200024', '#224', 'Asha', '', 'Roy', 'Female', '1955-08-10', 70, 'AB-', '1042 2049 3920', '+91 90123 89010', '', 'asha.roy@gmail.com', 'Retired', 'House 64, Village 10, Alwar, Rajasthan', 'Village 10', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-025', 'REG-200025', '#225', 'Rohan', '', 'Prasad', 'Male', '1990-11-20', 35, 'O+', '2049 3920 1042', '+91 91234 90120', '', 'rohan.prasad@gmail.com', 'Shopkeeper', 'House 19, Village 17, Alwar, Rajasthan', 'Village 17', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-026', 'REG-200026', '#226', 'Aditi', '', 'Sinha', 'Female', '1998-05-15', 28, 'A+', '3920 1042 2049', '+91 92345 01230', '', 'aditi.sinha@gmail.com', 'Student', 'House 34, Village 20, Alwar, Rajasthan', 'Village 20', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-027', 'REG-200027', '#227', 'Amit', '', 'Yadav', 'Male', '1983-09-12', 42, 'B+', '1042 2049 3920', '+91 93456 12340', '', 'amit.yadav@gmail.com', 'Teacher', 'House 112, Village 6, Alwar, Rajasthan', 'Village 6', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-028', 'REG-200028', '#228', 'Pooja', '', 'Mathur', 'Female', '1987-12-05', 38, 'AB+', '2049 3920 1042', '+91 94567 23450', '', 'pooja.mathur@gmail.com', 'Housewife', 'House 78, Village 8, Alwar, Rajasthan', 'Village 8', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-029', 'REG-200029', '#229', 'Sanjay', '', 'Saxena', 'Male', '1977-04-18', 49, 'O-', '3920 1042 2049', '+91 95678 34560', '', 'sanjay.saxena@gmail.com', 'Labourer', 'House 150, Village 10, Alwar, Rajasthan', 'Village 10', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-030', 'REG-200030', '#230', 'Kiran', '', 'Kapoor', 'Female', '1990-07-28', 35, 'A-', '1042 2049 3920', '+91 96789 45670', '', 'kiran.kapoor@gmail.com', 'Housewife', 'House 122, Village 12, Alwar, Rajasthan', 'Village 12', 'Hodal Block', 'Alwar', 'Rajasthan', '301001', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Completed', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-031', 'REG-200031', '#231', 'Dev', '', 'Kapoor', 'Male', '1982-11-15', 43, 'B-', '2049 3920 1042', '+91 97890 56780', '', 'dev.kapoor@gmail.com', 'Shopkeeper', 'House 42, Village 1, Palwal, Haryana', 'Village 1', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 72.0, 23.8, '120/80', 72, 98.4, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-032', 'REG-200032', '#232', 'Radha', '', 'Malhotra', 'Female', '1985-05-20', 41, 'AB-', '3920 1042 2049', '+91 98901 67890', '', 'radha.malhotra@gmail.com', 'Housewife', 'House 5, Village 15, Palwal, Haryana', 'Village 15', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 58.0, 22.7, '118/76', 69, 98.3, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-033', 'REG-200033', '#233', 'Hari', '', 'Khanna', 'Male', '1978-02-15', 48, 'O+', '1042 2049 3920', '+91 99012 78900', '', 'hari.khanna@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-034', 'REG-200034', '#234', 'Lata', '', 'Bajaj', 'Female', '1955-08-10', 70, 'A+', '2049 3920 1042', '+91 90123 89010', '', 'lata.bajaj@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-035', 'REG-200035', '#235', 'Ram', '', 'Bhasin', 'Male', '1990-11-20', 35, 'B+', '3920 1042 2049', '+91 91234 90120', '', 'ram.bhasin@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-036', 'REG-200036', '#236', 'Madhu', '', 'Chawla', 'Female', '1998-05-15', 28, 'AB+', '1042 2049 3920', '+91 92345 01230', '', 'madhu.chawla@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-037', 'REG-200037', '#237', 'Om', '', 'Sood', 'Male', '1983-09-12', 42, 'O-', '2049 3920 1042', '+91 93456 12340', '', 'om.sood@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-038', 'REG-200038', '#238', 'Asha', '', 'Grover', 'Female', '1987-12-05', 38, 'A-', '3920 1042 2049', '+91 94567 23450', '', 'asha.grover@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-039', 'REG-200039', '#239', 'Raj', '', 'Anand', 'Male', '1977-04-18', 49, 'B-', '1042 2049 3920', '+91 95678 34560', '', 'raj.anand@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-040', 'REG-200040', '#240', 'Kiran', '', 'Kohli', 'Female', '1990-07-28', 35, 'AB-', '2049 3920 1042', '+91 96789 45670', '', 'kiran.kohli@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'In Triage', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-041', 'REG-200041', '#241', 'Yash', '', 'Kapoor', 'Male', '1982-11-15', 43, 'O+', '3920 1042 2049', '+91 97890 56780', '', 'yash.kapoor@gmail.com', 'Shopkeeper', 'House 42, Village 1, Palwal, Haryana', 'Village 1', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 72.0, 23.8, '120/80', 72, 98.4, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Consultation', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-042', 'REG-200042', '#242', 'Radha', '', 'Dhillon', 'Female', '1985-05-20', 41, 'A+', '1042 2049 3920', '+91 98901 67890', '', 'radha.dhillon@gmail.com', 'Housewife', 'House 5, Village 15, Palwal, Haryana', 'Village 15', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 58.0, 22.7, '118/76', 69, 98.3, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'In Consultation', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-043', 'REG-200043', '#243', 'Hari', '', 'Gill', 'Male', '1978-02-15', 48, 'B+', '2049 3920 1042', '+91 99012 78900', '', 'hari.gill@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'In Consultation', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-044', 'REG-200044', '#244', 'Lata', '', 'Sandhu', 'Female', '1955-08-10', 70, 'AB+', '3920 1042 2049', '+91 90123 89010', '', 'lata.sandhu@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'In Consultation', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-045', 'REG-200045', '#245', 'Ram', '', 'Sidhu', 'Male', '1990-11-20', 35, 'O-', '1042 2049 3920', '+91 91234 90120', '', 'ram.sidhu@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'In Consultation', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-046', 'REG-200046', '#246', 'Madhu', '', 'Grewal', 'Female', '1998-05-15', 28, 'A-', '2049 3920 1042', '+91 92345 01230', '', 'madhu.grewal@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-047', 'REG-200047', '#247', 'Om', '', 'Johal', 'Male', '1983-09-12', 42, 'B-', '3920 1042 2049', '+91 93456 12340', '', 'om.johal@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-048', 'REG-200048', '#248', 'Asha', '', 'Brar', 'Female', '1987-12-05', 38, 'AB-', '1042 2049 3920', '+91 94567 23450', '', 'asha.brar@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-049', 'REG-200049', '#249', 'Raj', '', 'Mann', 'Male', '1977-04-18', 49, 'O+', '2049 3920 1042', '+91 95678 34560', '', 'raj.mann@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-050', 'REG-200050', '#250', 'Kiran', '', 'Toor', 'Female', '1990-07-28', 35, 'A+', '3920 1042 2049', '+91 96789 45670', '', 'kiran.toor@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-051', 'REG-200051', '#251', 'Hari', '', 'Shergill', 'Male', '1978-02-15', 48, 'B+', '1042 2049 3920', '+91 99012 78900', '', 'hari.shergill@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-052', 'REG-200052', '#252', 'Lata', '', 'Singh', 'Female', '1955-08-10', 70, 'AB+', '2049 3920 1042', '+91 90123 89010', '', 'lata.singh@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-053', 'REG-200053', '#253', 'Ram', '', 'Sharma', 'Male', '1990-11-20', 35, 'O-', '3920 1042 2049', '+91 91234 90120', '', 'ram.sharma@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-054', 'REG-200054', '#254', 'Madhu', '', 'Verma', 'Female', '1998-05-15', 28, 'A+', '1042 2049 3920', '+91 92345 01230', '', 'madhu.verma@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-055', 'REG-200055', '#255', 'Om', '', 'Kumar', 'Male', '1983-09-12', 42, 'B+', '2049 3920 1042', '+91 93456 12340', '', 'om.kumar@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-056', 'REG-200056', '#256', 'Asha', '', 'Patel', 'Female', '1987-12-05', 38, 'AB+', '3920 1042 2049', '+91 94567 23450', '', 'asha.patel@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-057', 'REG-200057', '#257', 'Raj', '', 'Nair', 'Male', '1977-04-18', 49, 'O-', '1042 2049 3920', '+91 95678 34560', '', 'raj.nair@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-058', 'REG-200058', '#258', 'Kiran', '', 'Reddy', 'Female', '1990-07-28', 35, 'A-', '2049 3920 1042', '+91 96789 45670', '', 'kiran.reddy@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-059', 'REG-200059', '#259', 'Hari', '', 'Rao', 'Male', '1978-02-15', 48, 'B-', '3920 1042 2049', '+91 99012 78900', '', 'hari.rao@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-060', 'REG-200060', '#260', 'Lata', '', 'Joshi', 'Female', '1955-08-10', 70, 'AB-', '1042 2049 3920', '+91 90123 89010', '', 'lata.joshi@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-061', 'REG-200061', '#261', 'Ram', '', 'Gupta', 'Male', '1990-11-20', 35, 'O+', '2049 3920 1042', '+91 91234 90120', '', 'ram.gupta@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-062', 'REG-200062', '#262', 'Madhu', '', 'Mehta', 'Female', '1998-05-15', 28, 'A+', '3920 1042 2049', '+91 92345 01230', '', 'madhu.mehta@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-063', 'REG-200063', '#263', 'Om', '', 'Mishra', 'Male', '1983-09-12', 42, 'B+', '1042 2049 3920', '+91 93456 12340', '', 'om.mishra@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-064', 'REG-200064', '#264', 'Asha', '', 'Pandey', 'Female', '1987-12-05', 38, 'AB+', '2049 3920 1042', '+91 94567 23450', '', 'asha.pandey@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-065', 'REG-200065', '#265', 'Raj', '', 'Trivedi', 'Male', '1977-04-18', 49, 'O-', '3920 1042 2049', '+91 95678 34560', '', 'raj.trivedi@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-066', 'REG-200066', '#266', 'Kiran', '', 'Gupta', 'Female', '1990-07-28', 35, 'A-', '1042 2049 3920', '+91 96789 45670', '', 'kiran.gupta@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-067', 'REG-200067', '#267', 'Hari', '', 'Chatterjee', 'Male', '1978-02-15', 48, 'B-', '2049 3920 1042', '+91 99012 78900', '', 'hari.chatterjee@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-068', 'REG-200068', '#268', 'Lata', '', 'Mukherjee', 'Female', '1955-08-10', 70, 'AB-', '3920 1042 2049', '+91 90123 89010', '', 'lata.mukherjee@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-069', 'REG-200069', '#269', 'Ram', '', 'Banerjee', 'Male', '1990-11-20', 35, 'O+', '1042 2049 3920', '+91 91234 90120', '', 'ram.banerjee@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-070', 'REG-200070', '#270', 'Madhu', '', 'Dutta', 'Female', '1998-05-15', 28, 'A+', '2049 3920 1042', '+91 92345 01230', '', 'madhu.dutta@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-071', 'REG-200071', '#271', 'Om', '', 'Sen', 'Male', '1983-09-12', 42, 'B+', '3920 1042 2049', '+91 93456 12340', '', 'om.sen@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-072', 'REG-200072', '#272', 'Asha', '', 'Das', 'Female', '1987-12-05', 38, 'AB+', '1042 2049 3920', '+91 94567 23450', '', 'asha.das@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-073', 'REG-200073', '#273', 'Raj', '', 'Choudhury', 'Male', '1977-04-18', 49, 'O-', '2049 3920 1042', '+91 95678 34560', '', 'raj.choudhury@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-074', 'REG-200074', '#274', 'Kiran', '', 'Bose', 'Female', '1990-07-28', 35, 'A-', '3920 1042 2049', '+91 96789 45670', '', 'kiran.bose@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-075', 'REG-200075', '#275', 'Hari', '', 'Roy', 'Male', '1978-02-15', 48, 'B-', '1042 2049 3920', '+91 99012 78900', '', 'hari.roy@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Verified', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-076', 'REG-200076', '#276', 'Lata', '', 'Prasad', 'Female', '1955-08-10', 70, 'AB-', '2049 3920 1042', '+91 90123 89010', '', 'lata.prasad@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Rejected', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-077', 'REG-200077', '#277', 'Ram', '', 'Sinha', 'Male', '1990-11-20', 35, 'O+', '3920 1042 2049', '+91 91234 90120', '', 'ram.sinha@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Rejected', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-078', 'REG-200078', '#278', 'Madhu', '', 'Yadav', 'Female', '1998-05-15', 28, 'A+', '1042 2049 3920', '+91 92345 01230', '', 'madhu.yadav@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Rejected', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-079', 'REG-200079', '#279', 'Om', '', 'Mathur', 'Male', '1983-09-12', 42, 'B+', '2049 3920 1042', '+91 93456 12340', '', 'om.mathur@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Rejected', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-080', 'REG-200080', '#280', 'Asha', '', 'Saxena', 'Female', '1987-12-05', 38, 'AB+', '3920 1042 2049', '+91 94567 23450', '', 'asha.saxena@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Rejected', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-081', 'REG-200081', '#281', 'Raj', '', 'Kapoor', 'Male', '1977-04-18', 49, 'O-', '1042 2049 3920', '+91 95678 34560', '', 'raj.kapoor@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-082', 'REG-200082', '#282', 'Kiran', '', 'Malhotra', 'Female', '1990-07-28', 35, 'A-', '2049 3920 1042', '+91 96789 45670', '', 'kiran.malhotra@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-083', 'REG-200083', '#283', 'Hari', '', 'Khanna', 'Male', '1978-02-15', 48, 'B-', '3920 1042 2049', '+91 99012 78900', '', 'hari.khanna@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-084', 'REG-200084', '#284', 'Lata', '', 'Bajaj', 'Female', '1955-08-10', 70, 'AB-', '1042 2049 3920', '+91 90123 89010', '', 'lata.bajaj@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-085', 'REG-200085', '#285', 'Ram', '', 'Bhasin', 'Male', '1990-11-20', 35, 'O+', '2049 3920 1042', '+91 91234 90120', '', 'ram.bhasin@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-086', 'REG-200086', '#286', 'Madhu', '', 'Chawla', 'Female', '1998-05-15', 28, 'A+', '3920 1042 2049', '+91 92345 01230', '', 'madhu.chawla@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-087', 'REG-200087', '#287', 'Om', '', 'Sood', 'Male', '1983-09-12', 42, 'B+', '1042 2049 3920', '+91 93456 12340', '', 'om.sood@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-088', 'REG-200088', '#288', 'Asha', '', 'Grover', 'Female', '1987-12-05', 38, 'AB+', '2049 3920 1042', '+91 94567 23450', '', 'asha.grover@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-089', 'REG-200089', '#289', 'Raj', '', 'Anand', 'Male', '1977-04-18', 49, 'O-', '3920 1042 2049', '+91 95678 34560', '', 'raj.anand@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-090', 'REG-200090', '#290', 'Kiran', '', 'Kohli', 'Female', '1990-07-28', 35, 'A-', '1042 2049 3920', '+91 96789 45670', '', 'kiran.kohli@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Pending ID', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-091', 'REG-200091', '#291', 'Yash', '', 'Kapoor', 'Male', '1982-11-15', 43, 'B-', '2049 3920 1042', '+91 97890 56780', '', 'yash.kapoor@gmail.com', 'Shopkeeper', 'House 42, Village 1, Palwal, Haryana', 'Village 1', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 72.0, 23.8, '120/80', 72, 98.4, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-092', 'REG-200092', '#292', 'Radha', '', 'Dhillon', 'Female', '1985-05-20', 41, 'AB-', '3920 1042 2049', '+91 98901 67890', '', 'radha.dhillon@gmail.com', 'Housewife', 'House 5, Village 15, Palwal, Haryana', 'Village 15', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 58.0, 22.7, '118/76', 69, 98.3, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-093', 'REG-200093', '#293', 'Hari', '', 'Gill', 'Male', '1978-02-15', 48, 'O+', '1042 2049 3920', '+91 99012 78900', '', 'hari.gill@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-094', 'REG-200094', '#294', 'Lata', '', 'Sandhu', 'Female', '1955-08-10', 70, 'A+', '2049 3920 1042', '+91 90123 89010', '', 'lata.sandhu@gmail.com', 'Retired', 'House 64, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 150.0, 44.0, 19.6, '145/92', 84, 98.2, 'Type-2 Diabetes', 'None', 'Metformin 500mg', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-095', 'REG-200095', '#295', 'Ram', '', 'Sidhu', 'Male', '1990-11-20', 35, 'B+', '3920 1042 2049', '+91 91234 90120', '', 'ram.sidhu@gmail.com', 'Shopkeeper', 'House 19, Village 17, Palwal, Haryana', 'Village 17', 'Hodal Block', 'Palwal', 'Haryana', '121102', 174.0, 70.0, 23.1, '120/78', 70, 98.4, 'None', 'Penicillin', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-096', 'REG-200096', '#296', 'Madhu', '', 'Grewal', 'Female', '1998-05-15', 28, 'AB+', '1042 2049 3920', '+91 92345 01230', '', 'madhu.grewal@gmail.com', 'Student', 'House 34, Village 20, Palwal, Haryana', 'Village 20', 'Hodal Block', 'Palwal', 'Haryana', '121102', 164.0, 55.0, 20.5, '114/72', 72, 98.1, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-097', 'REG-200097', '#297', 'Om', '', 'Johal', 'Male', '1983-09-12', 42, 'O-', '2049 3920 1042', '+91 93456 12340', '', 'om.johal@gmail.com', 'Teacher', 'House 112, Village 6, Palwal, Haryana', 'Village 6', 'Hodal Block', 'Palwal', 'Haryana', '121102', 176.0, 80.0, 25.8, '122/80', 71, 98.7, 'Acid reflux', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-098', 'REG-200098', '#298', 'Asha', '', 'Brar', 'Female', '1987-12-05', 38, 'A-', '3920 1042 2049', '+91 94567 23450', '', 'asha.brar@gmail.com', 'Housewife', 'House 78, Village 8, Palwal, Haryana', 'Village 8', 'Hodal Block', 'Palwal', 'Haryana', '121102', 160.0, 60.0, 23.4, '120/78', 70, 98.5, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80'),
('PAT-099', 'REG-200099', '#299', 'Raj', '', 'Mann', 'Male', '1977-04-18', 49, 'B-', '1042 2049 3920', '+91 95678 34560', '', 'raj.mann@gmail.com', 'Labourer', 'House 150, Village 10, Palwal, Haryana', 'Village 10', 'Hodal Block', 'Palwal', 'Haryana', '121102', 170.0, 74.0, 25.6, '130/86', 74, 98.8, 'None', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80'),
('PAT-100', 'REG-200100', '#300', 'Kiran', '', 'Toor', 'Female', '1990-07-28', 35, 'AB-', '2049 3920 1042', '+91 96789 45670', '', 'kiran.toor@gmail.com', 'Housewife', 'House 122, Village 12, Palwal, Haryana', 'Village 12', 'Hodal Block', 'Palwal', 'Haryana', '121102', 162.0, 56.0, 21.3, '116/74', 68, 98.2, 'Migraine', 'None', 'None', 'n/a', 'n/a', 3, 'Registered', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=80');

-- 5. Seeding 100 Verification records
INSERT INTO `patient_verification` (`patient_id`, `verification_status`, `verified_by`, `verification_date`, `remarks`) VALUES
('PAT-001', 'Verified', 3, '2026-07-19 09:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-002', 'Verified', 3, '2026-07-19 09:42:00', 'Aadhaar identity checked and match verified.'),
('PAT-003', 'Verified', 3, '2026-07-19 09:50:00', 'Aadhaar identity checked and match verified.'),
('PAT-004', 'Verified', 3, '2026-07-19 09:58:00', 'Aadhaar identity checked and match verified.'),
('PAT-005', 'Verified', 3, '2026-07-19 10:05:00', 'Aadhaar identity checked and match verified.'),
('PAT-006', 'Verified', 3, '2026-07-19 10:12:00', 'Aadhaar identity checked and match verified.'),
('PAT-007', 'Verified', 3, '2026-07-19 10:19:00', 'Aadhaar identity checked and match verified.'),
('PAT-008', 'Verified', 3, '2026-07-19 10:25:00', 'Aadhaar identity checked and match verified.'),
('PAT-009', 'Verified', 3, '2026-07-19 10:32:00', 'Aadhaar identity checked and match verified.'),
('PAT-010', 'Verified', 3, '2026-07-19 10:40:00', 'Aadhaar identity checked and match verified.'),
('PAT-011', 'Verified', 3, '2026-07-19 10:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-012', 'Verified', 3, '2026-07-19 10:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-013', 'Verified', 3, '2026-07-19 11:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-014', 'Verified', 3, '2026-07-19 11:05:00', 'Aadhaar identity checked and match verified.'),
('PAT-015', 'Verified', 3, '2026-07-19 11:12:00', 'Aadhaar identity checked and match verified.'),
('PAT-016', 'Verified', 3, '2026-07-19 11:20:00', 'Aadhaar identity checked and match verified.'),
('PAT-017', 'Verified', 3, '2026-07-19 11:25:00', 'Aadhaar identity checked and match verified.'),
('PAT-018', 'Verified', 3, '2026-07-19 11:32:00', 'Aadhaar identity checked and match verified.'),
('PAT-019', 'Verified', 3, '2026-07-19 11:40:00', 'Aadhaar identity checked and match verified.'),
('PAT-020', 'Verified', 3, '2026-07-19 11:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-021', 'Verified', 3, '2026-07-19 11:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-022', 'Verified', 3, '2026-07-19 12:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-023', 'Verified', 3, '2026-07-19 12:05:00', 'Aadhaar identity checked and match verified.'),
('PAT-024', 'Verified', 3, '2026-07-19 12:12:00', 'Aadhaar identity checked and match verified.'),
('PAT-025', 'Verified', 3, '2026-07-19 12:20:00', 'Aadhaar identity checked and match verified.'),
('PAT-026', 'Verified', 3, '2026-07-19 12:25:00', 'Aadhaar identity checked and match verified.'),
('PAT-027', 'Verified', 3, '2026-07-19 12:32:00', 'Aadhaar identity checked and match verified.'),
('PAT-028', 'Verified', 3, '2026-07-19 12:40:00', 'Aadhaar identity checked and match verified.'),
('PAT-029', 'Verified', 3, '2026-07-19 12:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-030', 'Verified', 3, '2026-07-19 12:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-031', 'Verified', 3, '2026-07-19 13:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-032', 'Verified', 3, '2026-07-19 13:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-033', 'Verified', 3, '2026-07-19 13:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-034', 'Verified', 3, '2026-07-19 13:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-035', 'Verified', 3, '2026-07-19 13:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-036', 'Verified', 3, '2026-07-19 13:38:00', 'Aadhaar identity checked and match verified.'),
('PAT-037', 'Verified', 3, '2026-07-19 13:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-038', 'Verified', 3, '2026-07-19 13:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-039', 'Verified', 3, '2026-07-19 14:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-040', 'Verified', 3, '2026-07-19 14:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-041', 'Verified', 3, '2026-07-19 14:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-042', 'Verified', 3, '2026-07-19 14:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-043', 'Verified', 3, '2026-07-19 14:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-044', 'Verified', 3, '2026-07-19 14:38:00', 'Aadhaar identity checked and match verified.'),
('PAT-045', 'Verified', 3, '2026-07-19 14:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-046', 'Verified', 3, '2026-07-19 14:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-047', 'Verified', 3, '2026-07-19 15:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-048', 'Verified', 3, '2026-07-19 15:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-049', 'Verified', 3, '2026-07-19 15:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-050', 'Verified', 3, '2026-07-19 15:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-051', 'Verified', 3, '2026-07-19 15:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-052', 'Verified', 3, '2026-07-19 15:38:00', 'Aadhaar identity checked and match verified.'),
('PAT-053', 'Verified', 3, '2026-07-19 15:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-054', 'Verified', 3, '2026-07-19 15:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-055', 'Verified', 3, '2026-07-19 16:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-056', 'Verified', 3, '2026-07-19 16:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-057', 'Verified', 3, '2026-07-19 16:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-058', 'Verified', 3, '2026-07-19 16:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-059', 'Verified', 3, '2026-07-19 16:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-060', 'Verified', 3, '2026-07-19 16:38:00', 'Aadhaar identity checked and match verified.'),
('PAT-061', 'Verified', 3, '2026-07-19 16:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-062', 'Verified', 3, '2026-07-19 16:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-063', 'Verified', 3, '2026-07-19 17:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-064', 'Verified', 3, '2026-07-19 17:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-065', 'Verified', 3, '2026-07-19 17:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-066', 'Verified', 3, '2026-07-19 17:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-067', 'Verified', 3, '2026-07-19 17:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-068', 'Verified', 3, '2026-07-19 17:38:00', 'Aadhaar identity checked and match verified.'),
('PAT-069', 'Verified', 3, '2026-07-19 17:45:00', 'Aadhaar identity checked and match verified.'),
('PAT-070', 'Verified', 3, '2026-07-19 17:52:00', 'Aadhaar identity checked and match verified.'),
('PAT-071', 'Verified', 3, '2026-07-19 18:00:00', 'Aadhaar identity checked and match verified.'),
('PAT-072', 'Verified', 3, '2026-07-19 18:08:00', 'Aadhaar identity checked and match verified.'),
('PAT-073', 'Verified', 3, '2026-07-19 18:15:00', 'Aadhaar identity checked and match verified.'),
('PAT-074', 'Verified', 3, '2026-07-19 18:22:00', 'Aadhaar identity checked and match verified.'),
('PAT-075', 'Verified', 3, '2026-07-19 18:30:00', 'Aadhaar identity checked and match verified.'),
('PAT-076', 'Rejected', 4, '2026-07-19 10:00:00', 'Biometric scanner thumbprint failure.'),
('PAT-077', 'Rejected', 4, '2026-07-19 10:15:00', 'Biometric scanner thumbprint failure.'),
('PAT-078', 'Rejected', 4, '2026-07-19 10:30:00', 'Biometric scanner thumbprint failure.'),
('PAT-079', 'Rejected', 4, '2026-07-19 10:45:00', 'Biometric scanner thumbprint failure.'),
('PAT-080', 'Rejected', 4, '2026-07-19 11:00:00', 'Biometric scanner thumbprint failure.'),
('PAT-081', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-082', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-083', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-084', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-085', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-086', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-087', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-088', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-089', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-090', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-091', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-092', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-093', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-094', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-095', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-096', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-097', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-098', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-099', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.'),
('PAT-100', 'Pending', NULL, NULL, 'Awaiting triage OTP checks.');

-- 6. Seeding 100 Attendance records
INSERT INTO `patient_attendance` (`patient_id`, `check_in`, `check_out`, `attendance_status`, `token_number`) VALUES
('PAT-001', '09:15 AM', '11:30 AM', 'Present', '#201'),
('PAT-002', '09:20 AM', '11:42 AM', 'Present', '#202'),
('PAT-003', '09:30 AM', '11:50 AM', 'Present', '#203'),
('PAT-004', '09:35 AM', '11:58 AM', 'Present', '#204'),
('PAT-005', '09:40 AM', '12:05 PM', 'Present', '#205'),
('PAT-006', '09:45 AM', '12:12 PM', 'Present', '#206'),
('PAT-007', '09:50 AM', '12:19 PM', 'Present', '#207'),
('PAT-008', '09:55 AM', '12:25 PM', 'Present', '#208'),
('PAT-009', '10:00 AM', '12:32 PM', 'Present', '#209'),
('PAT-010', '10:05 AM', '12:40 PM', 'Present', '#210'),
('PAT-011', '10:10 AM', '12:45 PM', 'Present', '#211'),
('PAT-012', '10:15 AM', '12:52 PM', 'Present', '#212'),
('PAT-013', '10:20 AM', '01:00 PM', 'Present', '#213'),
('PAT-014', '10:25 AM', '01:05 PM', 'Present', '#214'),
('PAT-015', '10:30 AM', '01:12 PM', 'Present', '#215'),
('PAT-016', '10:35 AM', '01:20 PM', 'Present', '#216'),
('PAT-017', '10:40 AM', '01:25 PM', 'Present', '#217'),
('PAT-018', '10:45 AM', '01:32 PM', 'Present', '#218'),
('PAT-019', '10:50 AM', '01:40 PM', 'Present', '#219'),
('PAT-020', '10:55 AM', '01:45 PM', 'Present', '#220'),
('PAT-021', '11:00 AM', '01:52 PM', 'Present', '#221'),
('PAT-022', '11:05 AM', '02:00 PM', 'Present', '#222'),
('PAT-023', '11:10 AM', '02:05 PM', 'Present', '#223'),
('PAT-024', '11:15 AM', '02:12 PM', 'Present', '#224'),
('PAT-025', '11:20 AM', '02:20 PM', 'Present', '#225'),
('PAT-026', '11:25 AM', '02:25 PM', 'Present', '#226'),
('PAT-027', '11:30 AM', '02:32 PM', 'Present', '#227'),
('PAT-028', '11:35 AM', '02:40 PM', 'Present', '#228'),
('PAT-029', '11:40 AM', '02:45 PM', 'Present', '#229'),
('PAT-030', '11:45 AM', '02:52 PM', 'Present', '#230'),
('PAT-031', '11:50 AM', '--', 'Present', '#231'),
('PAT-032', '11:55 AM', '--', 'Present', '#232'),
('PAT-033', '12:00 PM', '--', 'Present', '#233'),
('PAT-034', '12:05 PM', '--', 'Present', '#234'),
('PAT-035', '12:10 PM', '--', 'Present', '#235'),
('PAT-036', '12:15 PM', '--', 'Present', '#236'),
('PAT-037', '12:20 PM', '--', 'Present', '#237'),
('PAT-038', '12:25 PM', '--', 'Present', '#238'),
('PAT-039', '12:30 PM', '--', 'Present', '#239'),
('PAT-040', '12:35 PM', '--', 'Present', '#240'),
('PAT-041', '12:40 PM', '--', 'Present', '#241'),
('PAT-042', '12:45 PM', '--', 'Present', '#242'),
('PAT-043', '12:50 PM', '--', 'Present', '#243'),
('PAT-044', '12:55 PM', '--', 'Present', '#244'),
('PAT-045', '01:00 PM', '--', 'Present', '#245'),
('PAT-046', '01:05 PM', '--', 'Present', '#246'),
('PAT-047', '01:10 PM', '--', 'Present', '#247'),
('PAT-048', '01:15 PM', '--', 'Present', '#248'),
('PAT-049', '01:20 PM', '--', 'Present', '#249'),
('PAT-050', '01:25 PM', '--', 'Present', '#250'),
('PAT-051', '01:30 PM', '--', 'Present', '#251'),
('PAT-052', '01:35 PM', '--', 'Present', '#252'),
('PAT-053', '01:40 PM', '--', 'Present', '#253'),
('PAT-054', '01:45 PM', '--', 'Present', '#254'),
('PAT-055', '01:50 PM', '--', 'Present', '#255'),
('PAT-056', '01:55 PM', '--', 'Present', '#256'),
('PAT-057', '02:00 PM', '--', 'Present', '#257'),
('PAT-058', '02:05 PM', '--', 'Present', '#258'),
('PAT-059', '02:10 PM', '--', 'Present', '#259'),
('PAT-060', '02:15 PM', '--', 'Present', '#260'),
('PAT-061', '--', '--', 'Absent', '#261'),
('PAT-062', '--', '--', 'Absent', '#262'),
('PAT-063', '--', '--', 'Absent', '#263'),
('PAT-064', '--', '--', 'Absent', '#264'),
('PAT-065', '--', '--', 'Absent', '#265'),
('PAT-066', '--', '--', 'Absent', '#266'),
('PAT-067', '--', '--', 'Absent', '#267'),
('PAT-068', '--', '--', 'Absent', '#268'),
('PAT-069', '--', '--', 'Absent', '#269'),
('PAT-070', '--', '--', 'Absent', '#270'),
('PAT-071', '--', '--', 'Absent', '#271'),
('PAT-072', '--', '--', 'Absent', '#272'),
('PAT-073', '--', '--', 'Absent', '#273'),
('PAT-074', '--', '--', 'Absent', '#274'),
('PAT-075', '--', '--', 'Absent', '#275'),
('PAT-076', '--', '--', 'Absent', '#276'),
('PAT-077', '--', '--', 'Absent', '#277'),
('PAT-078', '--', '--', 'Absent', '#278'),
('PAT-079', '--', '--', 'Absent', '#279'),
('PAT-080', '--', '--', 'Absent', '#280'),
('PAT-081', '09:00 AM', '--', 'Late', '#281'),
('PAT-082', '09:05 AM', '--', 'Late', '#282'),
('PAT-083', '09:10 AM', '--', 'Late', '#283'),
('PAT-084', '09:15 AM', '--', 'Late', '#284'),
('PAT-085', '09:20 AM', '--', 'Late', '#285'),
('PAT-086', '09:25 AM', '--', 'Late', '#286'),
('PAT-087', '09:30 AM', '--', 'Late', '#287'),
('PAT-088', '09:35 AM', '--', 'Late', '#288'),
('PAT-089', '09:40 AM', '--', 'Late', '#289'),
('PAT-090', '09:45 AM', '--', 'Late', '#290'),
('PAT-091', '09:50 AM', '--', 'Late', '#291'),
('PAT-092', '09:55 AM', '--', 'Late', '#292'),
('PAT-093', '10:00 AM', '--', 'Late', '#293'),
('PAT-094', '10:05 AM', '--', 'Late', '#294'),
('PAT-095', '10:10 AM', '--', 'Late', '#295'),
('PAT-096', '10:15 AM', '--', 'Late', '#296'),
('PAT-097', '10:20 AM', '--', 'Late', '#297'),
('PAT-098', '10:25 AM', '--', 'Late', '#298'),
('PAT-099', '10:30 AM', '--', 'Late', '#299'),
('PAT-100', '10:35 AM', '--', 'Late', '#300');

-- 7. Seeding Notifications
INSERT INTO `notifications` (`title`, `message`, `type`, `status`) VALUES
('Medicine Stock Low', 'Paracetamol 650mg is below the critical threshold of 10% in Palwal Camp A.', 'danger', 'Unread'),
('Ambulance Dispatched', 'Ambulance dispatched to bay exit 1 for critical triage Patient Token #284.', 'danger', 'Read'),
('Outreach Camp Started', 'Apollo Rural Health Camp Phase 1 setups registered and operational.', 'success', 'Read'),
('Biometric System Failure Alert', 'OTP authentication fallback enabled for biometric sensor offline.', 'warning', 'Unread');

SET FOREIGN_KEY_CHECKS = 1;
=======
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
>>>>>>> origin/main
