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
// Ensure faculty_status column exists on users so we can track pending faculty requests
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS faculty_status ENUM('none','pending') DEFAULT 'none'");
?> 