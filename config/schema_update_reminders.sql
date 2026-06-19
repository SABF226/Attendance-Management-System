-- Email Reminders Migration
-- Run once. Tracks when a session's reminder email was last sent, so the cron
-- job doesn't email members twice for the same session.

ALTER TABLE attendance_sessions
  ADD COLUMN reminder_sent_at DATETIME NULL AFTER geo_accuracy;
