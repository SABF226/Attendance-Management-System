<?php
/**
 * Attendance Session Model
 * Data access layer for attendance_sessions table
 */

require_once __DIR__ . '/../config/database.php';

class AttendanceSession {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all sessions ordered by date descending
     */
    public function getAll() {
        $sql = "SELECT * FROM attendance_sessions ORDER BY session_date DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get session by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM attendance_sessions WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get session with attendance statistics
     */
    public function getWithStats($id) {
        $sql = "SELECT 
                    s.*,
                    COUNT(DISTINCT ar.member_id) as total_records,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count
                FROM attendance_sessions s
                LEFT JOIN attendance_records ar ON s.id = ar.session_id
                WHERE s.id = ?
                GROUP BY s.id";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new session
     */
    public function create($data) {
        $sql = "INSERT INTO attendance_sessions (session_date, session_time, session_team, session_name) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['session_date'],
            $data['session_time'] ?? null,
            $data['session_team'] ?? null,
            $data['session_name']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update session
     */
    public function update($id, $data) {
        $sql = "UPDATE attendance_sessions SET session_date = ?, session_time = ?, session_team = ?, session_name = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [
            $data['session_date'],
            $data['session_time'] ?? null,
            $data['session_team'] ?? null,
            $data['session_name'],
            $id
        ]);
        return $stmt->rowCount();
    }
    
    /**
     * Delete session (cascades to records)
     */
    public function delete($id) {
        $sql = "DELETE FROM attendance_sessions WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount();
    }
    
    /**
     * Get total session count
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM attendance_sessions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Get recent sessions
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT * FROM attendance_sessions ORDER BY session_date DESC LIMIT ?";
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /** Sessions on a given date (Y-m-d). Used by the reminder cron. */
    public function getByDate($date) {
        $sql = "SELECT * FROM attendance_sessions WHERE session_date = ? ORDER BY session_time ASC";
        return $this->db->query($sql, [$date])->fetchAll();
    }

    /** Mark a session's reminder email as sent now. */
    public function markReminderSent($sessionId) {
        $this->db->query(
            "UPDATE attendance_sessions SET reminder_sent_at = NOW() WHERE id = ?",
            [$sessionId]
        );
    }
    
    /**
     * Get filtered sessions with sorting
     */
    public function getFiltered($filters = [], $sort = 'date_desc') {
        $sql = "SELECT * FROM attendance_sessions WHERE 1=1";
        $params = [];
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $sql .= " AND session_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND session_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Status filter - sessions with absences
        if (!empty($filters['has_absences'])) {
            $sql .= " AND id IN (SELECT session_id FROM attendance_records WHERE status = 'absent')";
        }
        
        // Sorting
        switch ($sort) {
            case 'date_asc':
                $sql .= " ORDER BY session_date ASC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY session_name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY session_name DESC";
                break;
            default: // date_desc
                $sql .= " ORDER BY session_date DESC";
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Rotating-QR settings: the displayed token changes every QR_STEP_SECONDS,
    // and a scan also accepts up to QR_GRACE_STEPS previous steps to tolerate
    // clock skew and scan latency. A screenshot older than ~(STEP * (GRACE+1))
    // seconds is therefore rejected — which defeats screenshot-and-share.
    const QR_STEP_SECONDS = 20;
    const QR_GRACE_STEPS  = 1;

    /**
     * Open a session for QR check-in for $durationMinutes (the outer window).
     * Stores a per-session secret; the visible token is derived from it and
     * rotates every QR_STEP_SECONDS (see rotatingToken()).
     * Duration is clamped to a sane range (1–1440 min); defaults to 30.
     */
    public function generateQRCode($sessionId, $durationMinutes = 30) {
        $durationMinutes = (int)$durationMinutes;
        if ($durationMinutes < 1 || $durationMinutes > 1440) {
            $durationMinutes = 30;
        }
        $secret = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$durationMinutes} minutes"));
        $this->db->query(
            "UPDATE attendance_sessions SET qr_secret = ?, qr_code_token = NULL, qr_code_expires_at = ?, is_qr_active = 1 WHERE id = ?",
            [$secret, $expiresAt, $sessionId]
        );
        return $secret;
    }

    /**
     * Derive the rotating token for a secret at a given time-step offset.
     * token = first 32 hex chars of HMAC-SHA256(secret, step-counter).
     */
    public function rotatingToken($secret, $offset = 0) {
        $counter = intdiv(time(), self::QR_STEP_SECONDS) + $offset;
        return substr(hash_hmac('sha256', (string)$counter, $secret), 0, 32);
    }

    /**
     * The token to display right now for a session, or null if the session is
     * not active / expired / has no secret.
     */
    public function currentToken($session) {
        if (empty($session['qr_secret']) || empty($session['is_qr_active'])) {
            return null;
        }
        if (empty($session['qr_code_expires_at']) || strtotime($session['qr_code_expires_at']) <= time()) {
            return null;
        }
        return $this->rotatingToken($session['qr_secret'], 0);
    }

    /**
     * Validate a scanned token for a session row: must be active, inside the
     * outer expiry window, and match the current or a recent (grace) token.
     */
    public function isScanTokenValid($session, $token) {
        if (!$session || empty($session['qr_secret']) || empty($session['is_qr_active'])) {
            return false;
        }
        if (empty($session['qr_code_expires_at']) || strtotime($session['qr_code_expires_at']) <= time()) {
            return false;
        }
        for ($offset = 0; $offset >= -self::QR_GRACE_STEPS; $offset--) {
            if (hash_equals($this->rotatingToken($session['qr_secret'], $offset), (string)$token)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deactivate a session's QR code
     */
    public function deactivateQRCode($sessionId) {
        $this->db->query("UPDATE attendance_sessions SET is_qr_active = 0 WHERE id = ?", [$sessionId]);
    }

    /**
     * Pin a session's class location. Members must be within $radius metres to
     * check in. Radius is clamped to 20–2000 m. Returns false on bad coords.
     */
    public function setGeofence($sessionId, $lat, $lng, $radius, $accuracy = 0) {
        $lat = (float)$lat;
        $lng = (float)$lng;
        $radius = (int)$radius;
        $accuracy = max(0, min((int)$accuracy, 2000));
        if ($radius < 20)   { $radius = 20; }
        if ($radius > 2000) { $radius = 2000; }
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return false;
        }
        $this->db->query(
            "UPDATE attendance_sessions SET geo_lat = ?, geo_lng = ?, geo_radius = ?, geo_accuracy = ? WHERE id = ?",
            [$lat, $lng, $radius, $accuracy, $sessionId]
        );
        return true;
    }

    /** Remove a session's geofence (check-in then allowed from anywhere). */
    public function clearGeofence($sessionId) {
        $this->db->query(
            "UPDATE attendance_sessions SET geo_lat = NULL, geo_lng = NULL, geo_radius = NULL WHERE id = ?",
            [$sessionId]
        );
    }

    /** Does this session have a location pinned? */
    public function hasGeofence($session) {
        return $session && $session['geo_lat'] !== null && $session['geo_lng'] !== null;
    }

    /** Great-circle distance in metres between two points (haversine). */
    public function haversineMeters($lat1, $lng1, $lat2, $lng2) {
        $R = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Distance (metres) from the session's pinned centre, or null if N/A. */
    public function geofenceDistance($session, $lat, $lng) {
        if (!$this->hasGeofence($session) || $lat === null || $lng === null || $lat === '' || $lng === '') {
            return null;
        }
        return $this->haversineMeters(
            (float)$session['geo_lat'], (float)$session['geo_lng'],
            (float)$lat, (float)$lng
        );
    }

    /**
     * The effective allowed distance: the chosen radius plus a credit for the GPS
     * uncertainty of BOTH the admin's pin and the member's scan (each capped at
     * 200 m). This keeps real attendees in despite noisy indoor GPS — the rotating
     * token already defeats sharing, so we bias toward acceptance.
     */
    public function geofenceAllowance($session, $memberAccuracy = 0) {
        $memberAcc = max(0, min((float)$memberAccuracy, 200));
        $adminAcc  = max(0, min((float)($session['geo_accuracy'] ?? 0), 200));
        return (float)$session['geo_radius'] + $memberAcc + $adminAcc;
    }

    /**
     * Is the member (at $lat,$lng with GPS $accuracy metres) inside the session's
     * geofence? Returns true when no geofence is set.
     */
    public function isWithinGeofence($session, $lat, $lng, $accuracy = 0) {
        if (!$this->hasGeofence($session)) {
            return true;
        }
        $dist = $this->geofenceDistance($session, $lat, $lng);
        if ($dist === null) {
            return false;
        }
        return $dist <= $this->geofenceAllowance($session, $accuracy);
    }

    /**
     * Get monthly statistics for sessions
     */
    public function getMonthlyStats() {
        // Get total sessions this month
        $sql = "SELECT COUNT(*) as total_this_month 
                FROM attendance_sessions 
                WHERE MONTH(session_date) = MONTH(CURRENT_DATE) 
                AND YEAR(session_date) = YEAR(CURRENT_DATE)";
        $stmt = $this->db->query($sql);
        $monthlyCount = $stmt->fetch()['total_this_month'] ?? 0;
        
        // Get average attendance rate across all sessions
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_sessions,
                    COUNT(ar.id) as total_records,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
                FROM attendance_sessions s
                LEFT JOIN attendance_records ar ON s.id = ar.session_id";
        $stmt = $this->db->query($sql);
        $attendanceStats = $stmt->fetch();
        
        $avgAttendanceRate = 0;
        if ($attendanceStats['total_records'] > 0) {
            $avgAttendanceRate = round(($attendanceStats['present_count'] / $attendanceStats['total_records']) * 100, 1);
        }
        
        // Get most active session type (team)
        $sql = "SELECT session_team, COUNT(*) as count 
                FROM attendance_sessions 
                WHERE session_team IS NOT NULL AND session_team != ''
                GROUP BY session_team 
                ORDER BY count DESC 
                LIMIT 1";
        $stmt = $this->db->query($sql);
        $topTeam = $stmt->fetch();
        
        return [
            'total_this_month' => (int)$monthlyCount,
            'avg_attendance_rate' => $avgAttendanceRate,
            'most_active_team' => $topTeam ? $topTeam['session_team'] : 'N/A',
            'total_sessions' => (int)($attendanceStats['total_sessions'] ?? 0)
        ];
    }
}

