-- ============================================================
-- BIT English Club — Attendance System
-- Consolidated install schema (QR rotating + geofence + reminders + auth)
-- ============================================================
-- For shared hosts (e.g. InfinityFree / MariaDB):
--   1) Create/choose your database in the control panel.
--   2) Open phpMyAdmin for that database and IMPORT this file.
--   3) There is NO "CREATE DATABASE" here on purpose — it imports into the
--      database you already selected (its name is fixed by the host).
-- Charset/collation are MariaDB-friendly (utf8mb4_unicode_ci).
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------- members (also the auth/identity table) ----------
CREATE TABLE IF NOT EXISTS `members` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `field`      VARCHAR(100) NOT NULL,
  `phone`      VARCHAR(20)  NOT NULL,
  `email`      VARCHAR(100) NULL UNIQUE,
  `password`   VARCHAR(255) NULL,
  `role`       ENUM('admin','member') NOT NULL DEFAULT 'member',
  `points`     INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- attendance_sessions ----------
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id`                 INT AUTO_INCREMENT PRIMARY KEY,
  `session_date`       DATE NOT NULL,
  `session_name`       VARCHAR(100) NOT NULL,
  `session_time`       TIME NULL,
  `session_team`       VARCHAR(100) NULL,
  -- QR (rotating tokens)
  `qr_code_token`      VARCHAR(64) NULL UNIQUE,
  `qr_secret`          VARCHAR(64) NULL,
  `qr_code_expires_at` DATETIME NULL,
  `is_qr_active`       TINYINT(1) DEFAULT 0,
  -- Geofence
  `geo_lat`            DECIMAL(10,7) NULL,
  `geo_lng`            DECIMAL(10,7) NULL,
  `geo_radius`         SMALLINT UNSIGNED NULL,
  `geo_accuracy`       SMALLINT UNSIGNED NULL,
  -- Email reminders
  `reminder_sent_at`   DATETIME NULL,
  `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session_date` (`session_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- attendance_records ----------
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `session_id`    INT NOT NULL,
  `member_id`     INT NOT NULL,
  `status`        ENUM('present','absent','excused') DEFAULT 'present',
  `notes`         TEXT NULL,
  `check_in_time` DATETIME NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_session_member` (`session_id`,`member_id`),
  INDEX `idx_member` (`member_id`),
  CONSTRAINT `fk_record_session` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_record_member`  FOREIGN KEY (`member_id`)  REFERENCES `members`(`id`)            ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- After importing: open setup_auth.php in your browser ONCE to
-- seed the admin account, then DELETE setup_auth.php and setup.php.
-- ============================================================
