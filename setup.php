<?php
/**
 * Database Setup Script
 * Run this file to initialize the database
 * 
 * Usage: php setup.php
 * Or access via browser: http://localhost/attendance-list/setup.php
 */

$host = 'localhost';
$username = 'root';
$password = 'sabf2005';
$dbname = 'english_club';

echo "<h1>English Club Attendance - Database Setup</h1>";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>✓ Database '$dbname' created successfully</p>";
    
    // Select database
    $pdo->exec("USE `$dbname`");
    echo "<p style='color:green;'>✓ Using database '$dbname'</p>";
    
    // Create members table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            field VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green;'>✓ Members table created</p>";
    
    // Create attendance_sessions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_date DATE NOT NULL,
            session_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session_date (session_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green;'>✓ Attendance sessions table created</p>";
    
    // Create attendance_records table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL,
            member_id INT NOT NULL,
            status ENUM('present', 'absent', 'excused') DEFAULT 'present',
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            UNIQUE KEY unique_session_member (session_id, member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color:green;'>✓ Attendance records table created</p>";
    
    // Insert sample data
    $pdo->exec("
        INSERT INTO members (name, field, phone, email) VALUES
        ('John Smith', 'Computer Science', '+1234567890', 'john.smith@example.com'),
        ('Sarah Johnson', 'English Literature', '+1234567891', 'sarah.j@example.com'),
        ('Michael Brown', 'Business Administration', '+1234567892', 'm.brown@example.com'),
        ('Emily Davis', 'Engineering', '+1234567893', 'emily.d@example.com'),
        ('David Wilson', 'Mathematics', '+1234567894', 'd.wilson@example.com')
    ");
    echo "<p style='color:green;'>✓ Sample members added</p>";
    
    $pdo->exec("
        INSERT INTO attendance_sessions (session_date, session_name) VALUES
        ('2024-01-15', 'English Club Meeting - Week 1'),
        ('2024-01-22', 'English Club Meeting - Week 2'),
        ('2024-01-29', 'English Club Meeting - Week 3')
    ");
    echo "<p style='color:green;'>✓ Sample sessions added</p>";
    
    echo "<h2 style='color:green;'>Setup Complete!</h2>";
    echo "<p>You can now access the application at: <a href='index.php'>index.php</a></p>";
    echo "<p><strong>Note:</strong> Delete this file after setup for security.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config/database.php</p>";
}

