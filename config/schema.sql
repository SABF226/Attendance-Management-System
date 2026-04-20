-- English Club Attendance List Database Schema
-- Run this SQL to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS english_club 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE english_club;

-- Members table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance sessions table
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_date DATE NOT NULL,
    session_time TIME NULL,
    session_team VARCHAR(100) NULL,
    session_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_date (session_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance records table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing
INSERT INTO members (name, field, phone, email) VALUES
('Abdoul B. SANON', 'Computer Science', '+1234567890', 'john.smith@example.com'),
('Sarah Johnson', 'Computer Science', '+1234567891', 'sarah.j@example.com'),
('Michael Brown', 'Business Administration', '+1234567892', 'm.brown@example.com'),
('Emily Davis', 'Electrical Engineering', '+1234567893', 'emily.d@example.com'),
('David Wilson', 'Mechanical Engineering', '+1234567894', 'd.wilson@example.com');

INSERT INTO attendance_sessions (session_date, session_name) VALUES
('2024-01-15', 'English Club Meeting - Week 1'),
('2024-01-22', 'English Club Meeting - Week 2'),
('2024-01-29', 'English Club Meeting - Week 3');

