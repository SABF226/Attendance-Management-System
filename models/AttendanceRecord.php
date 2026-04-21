<?php
/**
 * Attendance Record Model
 * Data access layer for attendance_records table
 */

require_once __DIR__ . '/../config/database.php';

class AttendanceRecord {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all attendance records for a session
     */
    public function getBySession($sessionId) {
        $sql = "SELECT 
                    ar.*,
                    m.name,
                    m.field,
                    m.phone,
                    m.email
                FROM attendance_records ar
                JOIN members m ON ar.member_id = m.id
                WHERE ar.session_id = ?
                ORDER BY m.name ASC";
        $stmt = $this->db->query($sql, [$sessionId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all attendance records for a member
     */
    public function getByMember($memberId) {
        $sql = "SELECT 
                    ar.*,
                    s.session_date,
                    s.session_name
                FROM attendance_records ar
                JOIN attendance_sessions s ON ar.session_id = s.id
                WHERE ar.member_id = ?
                ORDER BY s.session_date DESC";
        $stmt = $this->db->query($sql, [$memberId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get record for specific session and member
     */
    public function getBySessionAndMember($sessionId, $memberId) {
        $sql = "SELECT * FROM attendance_records WHERE session_id = ? AND member_id = ?";
        $stmt = $this->db->query($sql, [$sessionId, $memberId]);
        return $stmt->fetch();
    }
    
    /**
     * Set or update attendance for a member in a session
     */
    public function setAttendance($sessionId, $memberId, $status, $notes = null) {
        $existing = $this->getBySessionAndMember($sessionId, $memberId);
        
        if ($existing) {
            $sql = "UPDATE attendance_records SET status = ?, notes = ? WHERE session_id = ? AND member_id = ?";
            $stmt = $this->db->query($sql, [$status, $notes, $sessionId, $memberId]);
        } else {
            $sql = "INSERT INTO attendance_records (session_id, member_id, status, notes) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [$sessionId, $memberId, $status, $notes]);
        }
        
        return $stmt->rowCount();
    }
    
    /**
     * Save multiple attendance records in a transaction
     */
    public function saveBatch($sessionId, $records) {
        $this->db->beginTransaction();
        
        try {
            foreach ($records as $memberId => $data) {
                $this->setAttendance(
                    $sessionId, 
                    $memberId, 
                    $data['status'], 
                    $data['notes'] ?? null
                );
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete all records for a session
     */
    public function deleteBySession($sessionId) {
        $sql = "DELETE FROM attendance_records WHERE session_id = ?";
        $stmt = $this->db->query($sql, [$sessionId]);
        return $stmt->rowCount();
    }
    
    /**
     * Delete all records for a member
     */
    public function deleteByMember($memberId) {
        $sql = "DELETE FROM attendance_records WHERE member_id = ?";
        $stmt = $this->db->query($sql, [$memberId]);
        return $stmt->rowCount();
    }
    
    /**
     * Get attendance statistics for a member
     */
    public function getMemberStats($memberId) {
        $sql = "SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
                FROM attendance_records
                WHERE member_id = ?";
        $stmt = $this->db->query($sql, [$memberId]);
        return $stmt->fetch();
    }
    
    /**
     * Get overall attendance statistics
     */
    public function getOverallStats() {
        $sql = "SELECT 
                    COUNT(DISTINCT session_id) as total_sessions,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
                FROM attendance_records";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Get attendance trend data for the last 5 sessions
     */
    public function getTrendData($limit = 5) {
        $sql = "SELECT 
                    s.id,
                    s.session_name,
                    s.session_date,
                    COUNT(*) as total_members,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                    ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as attendance_rate
                FROM attendance_sessions s
                LEFT JOIN attendance_records ar ON s.id = ar.session_id
                GROUP BY s.id, s.session_name, s.session_date
                ORDER BY s.session_date DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$limit]);
        return array_reverse($stmt->fetchAll());
    }
    
    /**
     * Get top attendees with highest attendance rates
     */
    public function getTopAttendees($limit = 5) {
        $sql = "SELECT 
                    m.id,
                    m.name,
                    m.field,
                    COUNT(ar.id) as total_sessions,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(ar.id)) * 100, 1) as attendance_rate
                FROM members m
                JOIN attendance_records ar ON m.id = ar.member_id
                GROUP BY m.id, m.name, m.field
                HAVING COUNT(ar.id) >= 2
                ORDER BY attendance_rate DESC, present_count DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get status distribution for pie chart
     */
    public function getStatusDistribution() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM attendance_records
                GROUP BY status";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        $distribution = [
            'present' => 0,
            'absent' => 0,
            'excused' => 0
        ];
        
        foreach ($results as $row) {
            $distribution[$row['status']] = (int)$row['count'];
        }
        
        return $distribution;
    }
}

