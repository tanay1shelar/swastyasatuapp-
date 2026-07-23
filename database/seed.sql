<<<<<<< HEAD
-- Healthcare & Medical Camp Management System (HMCMS)
-- Database Seed Data Script
-- Initial data for medical camps and users

INSERT INTO `camps` (`name`, `location`, `district`, `start_date`, `end_date`, `status`) VALUES
('Apollo Diabetes Checkup', 'Panchayat Bhawan, Palwal', 'Palwal', '2026-07-01', '2026-07-31', 'Active')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);
=======
-- Database seed script for Swasthya Setu
>>>>>>> origin/main
