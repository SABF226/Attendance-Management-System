<?php
/**
 * Database Schema Migration & Seeding Script
 * Adds password and role columns to members table and seeds test accounts
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "=== English Club Attendance List: Database Migration & Seeding ===\n\n";

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "1. Connecting to database successful.\n";
    
    // Check if columns already exist
    $checkQuery = "SHOW COLUMNS FROM members";
    $stmt = $conn->query($checkQuery);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $hasPassword = in_array('password', $columns);
    $hasRole = in_array('role', $columns);
    
    // Alter table if password column is missing
    if (!$hasPassword) {
        echo "2. Adding 'password' column to 'members' table...\n";
        $conn->exec("ALTER TABLE members ADD COLUMN password VARCHAR(255) NULL AFTER email");
        echo "   -> 'password' column added successfully.\n";
    } else {
        echo "2. 'password' column already exists in 'members' table.\n";
    }
    
    // Alter table if role column is missing
    if (!$hasRole) {
        echo "3. Adding 'role' column to 'members' table...\n";
        $conn->exec("ALTER TABLE members ADD COLUMN role ENUM('admin', 'member') DEFAULT 'member' NOT NULL AFTER password");
        echo "   -> 'role' column added successfully.\n";
    } else {
        echo "3. 'role' column already exists in 'members' table.\n";
    }
    
    // 4. Update the executive chairman's account to be admin
    $chairmanEmail = 'sabfsanon@gmail.com';
    $adminPasswordHash = password_hash('admin123', PASSWORD_BCRYPT);
    
    echo "4. Setting executive chairman ($chairmanEmail) as 'admin'...\n";
    $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$chairmanEmail]);
    $chairmanExists = $stmt->fetch();
    
    if ($chairmanExists) {
        $stmt = $conn->prepare("UPDATE members SET role = 'admin', password = ? WHERE email = ?");
        $stmt->execute([$adminPasswordHash, $chairmanEmail]);
        echo "   -> Chairman profile successfully set as Admin with default password 'admin123'.\n";
    } else {
        // If chairman is not in DB, create him
        $stmt = $conn->prepare("INSERT INTO members (name, field, phone, email, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([
            'Abdoul Ben F. SANON',
            'CS 27',
            '+226 06262545',
            $chairmanEmail,
            $adminPasswordHash
        ]);
        echo "   -> Created chairman profile as Admin with password 'admin123'.\n";
    }
    
    // 5. Seed general admin account (admin@bit.bf)
    $adminEmail = 'admin@bit.bf';
    echo "5. Seeding admin account ($adminEmail)...\n";
    $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if ($stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE members SET role = 'admin', password = ? WHERE email = ?");
        $stmt->execute([$adminPasswordHash, $adminEmail]);
        echo "   -> Admin account already exists, password updated to 'admin123'.\n";
    } else {
        $stmt = $conn->prepare("INSERT INTO members (name, field, phone, email, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([
            'BIT Club Admin',
            'Administration',
            '+226 00000000',
            $adminEmail,
            $adminPasswordHash
        ]);
        echo "   -> Admin account seeded successfully with password 'admin123'.\n";
    }
    
    // 6. Seed general member account (member@bit.bf)
    $memberEmail = 'member@bit.bf';
    $memberPasswordHash = password_hash('member123', PASSWORD_BCRYPT);
    echo "6. Seeding test member account ($memberEmail)...\n";
    $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$memberEmail]);
    if ($stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE members SET role = 'member', password = ? WHERE email = ?");
        $stmt->execute([$memberPasswordHash, $memberEmail]);
        echo "   -> Member account already exists, password updated to 'member123'.\n";
    } else {
        $stmt = $conn->prepare("INSERT INTO members (name, field, phone, email, password, role) VALUES (?, ?, ?, ?, ?, 'member')");
        $stmt->execute([
            'Test Member',
            'Computer Science',
            '+226 11111111',
            $memberEmail,
            $memberPasswordHash
        ]);
        echo "   -> Member account seeded successfully with password 'member123'.\n";
    }
    
    echo "\n=== Migration and Seeding Completed Successfully! ===\n";
    
} catch (PDOException $e) {
    echo "\n[ERROR] Database Migration Failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n[ERROR] General Exception: " . $e->getMessage() . "\n";
}
