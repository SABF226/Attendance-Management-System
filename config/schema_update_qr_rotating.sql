-- QR Rotating-Token Migration
-- Run once. Adds a per-session secret used to derive short-lived rotating QR tokens.
-- The displayed QR code now changes every few seconds (see AttendanceSession::QR_STEP_SECONDS),
-- so a screenshot shared with someone outside the class is stale on arrival.

ALTER TABLE attendance_sessions
  ADD COLUMN qr_secret VARCHAR(64) NULL AFTER qr_code_token;
