-- QR Code Module – Database Migration
-- Run once to add QR attendance columns

ALTER TABLE attendance_sessions
  ADD COLUMN qr_code_token VARCHAR(64) UNIQUE NULL,
  ADD COLUMN qr_code_expires_at DATETIME NULL,
  ADD COLUMN is_qr_active BOOLEAN DEFAULT FALSE;

ALTER TABLE members
  ADD COLUMN points INT DEFAULT 0;

ALTER TABLE attendance_records
  ADD COLUMN check_in_time DATETIME NULL;
