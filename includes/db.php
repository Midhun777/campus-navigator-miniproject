<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'campus_navigator';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Ensure audit_logs table exists (idempotent)
$conn->query("CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Ensure lost & found tables exist
$conn->query("CREATE TABLE IF NOT EXISTS lost_found (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('lost','found') NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    location VARCHAR(150),
    event_date DATE NULL,
    contact VARCHAR(150) NULL,
    image VARCHAR(255) NULL,
    status ENUM('open','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");
$conn->query("CREATE TABLE IF NOT EXISTS lost_found_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lf_id INT NOT NULL,
    user_id INT NOT NULL,
    response_type ENUM('found_report','lost_report','comment') DEFAULT 'comment',
    message TEXT,
    contact VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lf_id) REFERENCES lost_found(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");
?> 