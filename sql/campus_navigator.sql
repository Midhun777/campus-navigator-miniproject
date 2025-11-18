CREATE DATABASE IF NOT EXISTS campus_navigator;
USE campus_navigator;

-- Colleges master
CREATE TABLE IF NOT EXISTS colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) DEFAULT NULL,
    UNIQUE KEY uniq_college_name (name),
    UNIQUE KEY uniq_college_code (code)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('user','faculty','admin') DEFAULT 'user',
    profile_pic VARCHAR(255) DEFAULT NULL,
    college_id INT NULL,
    FOREIGN KEY (college_id) REFERENCES colleges(id)
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    category_id INT,
    timing VARCHAR(100),
    direction VARCHAR(255),
    distance VARCHAR(100),
    status ENUM('pending','approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    college_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (college_id) REFERENCES colleges(id)
);

CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    spot_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    spot_id INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    spot_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    spot_id INT,
    reason TEXT,
    status ENUM('open','resolved','removed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

CREATE TABLE IF NOT EXISTS suggested_edits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    spot_id INT,
    suggestion TEXT,
    status ENUM('open','resolved','removed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

-- Ensure new status columns exist when running against older databases
ALTER TABLE reports
    ADD COLUMN IF NOT EXISTS status ENUM('open','resolved','removed') DEFAULT 'open' AFTER reason;
ALTER TABLE suggested_edits
    ADD COLUMN IF NOT EXISTS status ENUM('open','resolved','removed') DEFAULT 'open' AFTER suggestion;

-- Audit logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed categories with stable IDs so foreign keys from spots work
INSERT INTO categories (id, name, icon) VALUES
    (2,  'Cafeteria', '🍽️'),
    (7,  'ATM',       '🏧'),
    (8,  'Hangout',   '🎉'),
    (10, 'Bus stop',  '🚌'),
    (13, 'Shopping',  '🛒'),
    (15, 'Stadium',   '⚽')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    icon = VALUES(icon);

INSERT INTO colleges (name, code) VALUES
    ('College of Engineering', 'COE'),
    ('Cochin Arts and Science College', 'CASC'),
    ('College of Science', 'SCI')
ON DUPLICATE KEY UPDATE code = VALUES(code);

-- For testing/demo: align existing records with an example college if present
UPDATE users
SET college_id = (SELECT id FROM colleges WHERE code = 'CASC')
WHERE college_id IS NULL;

UPDATE spots
SET college_id = (SELECT id FROM colleges WHERE code = 'CASC')
WHERE college_id IS NULL;