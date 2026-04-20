<?php
/**
 * Member Model
 * Data access layer for members table
 */

require_once __DIR__ . '/../config/database.php';

class Member {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all members ordered by name
     */
    public function getAll() {
        $sql = "SELECT * FROM members ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get member by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM members WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new member
     */
    public function create($data) {
        $sql = "INSERT INTO members (name, field, phone, email) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['field'],
            $data['phone'],
            $data['email']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update existing member
     */
    public function update($id, $data) {
        $sql = "UPDATE members SET name = ?, field = ?, phone = ?, email = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [
            $data['name'],
            $data['field'],
            $data['phone'],
            $data['email'],
            $id
        ]);
        return $stmt->rowCount();
    }
    
    /**
     * Delete member
     */
    public function delete($id) {
        $sql = "DELETE FROM members WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount();
    }
    
    /**
     * Search members by name or email
     */
    public function search($query) {
        $sql = "SELECT * FROM members WHERE name LIKE ? OR email LIKE ? ORDER BY name ASC";
        $searchTerm = "%{$query}%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if email exists (for validation)
     */
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT id FROM members WHERE email = ? AND id != ?";
            $stmt = $this->db->query($sql, [$email, $excludeId]);
        } else {
            $sql = "SELECT id FROM members WHERE email = ?";
            $stmt = $this->db->query($sql, [$email]);
        }
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get total member count
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM members";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
}

