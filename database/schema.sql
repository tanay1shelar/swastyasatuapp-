-- Swasthya Setu HMCMS Database Schema

CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    expiry_date DATE NOT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    created_at VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stock_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    update_type ENUM('add', 'remove') NOT NULL,
    update_qty INT NOT NULL,
    created_at VARCHAR(50) NOT NULL,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS doctor_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    doctor_id VARCHAR(100) NOT NULL,
    assignment_date DATE NOT NULL,
    created_at VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worker_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    worker_id VARCHAR(100) NOT NULL,
    shift_date DATE NOT NULL,
    created_at VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    audience VARCHAR(50) NOT NULL DEFAULT 'all',
    created_at VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
