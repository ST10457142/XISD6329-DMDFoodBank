-- DMD Food Bank Database
CREATE DATABASE IF NOT EXISTS dmd_foodbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dmd_foodbank;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('donor','beneficiary','volunteer','admin') DEFAULT 'donor',
    donor_type VARCHAR(100) DEFAULT 'Individual',
    phone VARCHAR(30),
    total_donated DECIMAL(12,2) DEFAULT 0.00,
    donation_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(12,2) NOT NULL,
    donation_type ENUM('money','food') DEFAULT 'money',
    food_description TEXT,
    frequency ENUM('once','monthly') DEFAULT 'once',
    status ENUM('pending','completed','failed') DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS beneficiaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    household_size VARCHAR(30),
    nearest_hub VARCHAR(100),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    skills VARCHAR(150),
    availability VARCHAR(100),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (password: Admin@1234)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@dmdfoodbank.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;
