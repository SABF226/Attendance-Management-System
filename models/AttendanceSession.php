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

