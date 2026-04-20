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
}

