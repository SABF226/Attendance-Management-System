-- QR Geofence Migration
-- Run once. Lets an admin pin a session's class location so only members
-- physically within geo_radius metres can check in. When geo_lat is NULL the
-- geofence is OFF for that session (backward compatible).

ALTER TABLE attendance_sessions
  ADD COLUMN geo_lat      DECIMAL(10,7) NULL AFTER is_qr_active,
  ADD COLUMN geo_lng      DECIMAL(10,7) NULL AFTER geo_lat,
  ADD COLUMN geo_radius   SMALLINT UNSIGNED NULL AFTER geo_lng,
  -- the admin device's GPS accuracy (metres) when the location was pinned,
  -- so the scan tolerance can account for error on BOTH ends.
  ADD COLUMN geo_accuracy SMALLINT UNSIGNED NULL AFTER geo_radius;
