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
     * Get member by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM members WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    /**
     * Create new member
     */
    public function create($data) {
        $password = isset($data['password']) && !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null;
        $role = $data['role'] ?? 'member';
        
        $sql = "INSERT INTO members (name, field, phone, email, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['field'],
            $data['phone'],
            $data['email'],
            $password,
            $role
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update existing member
     */
    public function update($id, $data) {
        $role = $data['role'] ?? 'member';
        
        if (isset($data['password']) && !empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $sql = "UPDATE members SET name = ?, field = ?, phone = ?, email = ?, password = ?, role = ? WHERE id = ?";
            $params = [
                $data['name'],
                $data['field'],
                $data['phone'],
                $data['email'],
                $passwordHash,
                $role,
                $id
            ];
        } else {
            $sql = "UPDATE members SET name = ?, field = ?, phone = ?, email = ?, role = ? WHERE id = ?";
            $params = [
                $data['name'],
                $data['field'],
                $data['phone'],
                $data['email'],
                $role,
                $id
            ];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Register a new member or claim an existing member profile
     */
    public function registerMember($data) {
        $email = $data['email'];
        $existing = $this->getByEmail($email);
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        if ($existing) {
            // If they already have a password set, they are already registered!
            if (!empty($existing['password'])) {
                return false;
            }
            
            // Otherwise, they are claiming an existing profile created by an admin
            $sql = "UPDATE members SET name = ?, field = ?, phone = ?, password = ?, role = 'member' WHERE id = ?";
            $this->db->query($sql, [
                $data['name'],
                $data['field'],
                $data['phone'],
                $passwordHash,
                $existing['id']
            ]);
            return $existing['id'];
        } else {
            // Register as a brand new member
            $sql = "INSERT INTO members (name, field, phone, email, password, role) VALUES (?, ?, ?, ?, ?, 'member')";
            $this->db->query($sql, [
                $data['name'],
                $data['field'],
                $data['phone'],
                $email,
                $passwordHash
            ]);
            return $this->db->lastInsertId();
        }
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

