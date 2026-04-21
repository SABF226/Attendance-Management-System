<?php
/**
 * Member Controller
 * Handles all member-related operations
 */

require_once __DIR__ . '/../models/Member.php';

class MemberController {
    private $memberModel;
    
    public function __construct() {
        $this->memberModel = new Member();
    }
    
    /**
     * Display all members
     */
    public function index() {
        $search = $_GET['search'] ?? null;
        
        if ($search) {
            $members = $this->memberModel->search($search);
        } else {
            $members = $this->memberModel->getAll();
        }
        
        return $members;
    }
    
    /**
     * Show create form
     */
    public function create() {
        return [
            'view' => 'views/members/form.php',
            'data' => [
                'breadcrumbs' => [
                    ['label' => 'Members', 'url' => '?page=members'],
                    ['label' => 'Add New Member']
                ]
            ]
        ];
    }
    
    /**
     * Store new member
     */
    public function store($postData) {
        $errors = $this->validate($postData);
        
        if (!empty($errors)) {
            return [
                'view' => 'views/members/form.php',
                'data' => ['errors' => $errors, 'old' => $postData]
            ];
        }
        
        try {
            $this->memberModel->create($postData);
            $_SESSION['message'] = 'Member added successfully!';
            $_SESSION['message_type'] = 'success';
            $this->redirect('?page=members');
        } catch (Exception $e) {
            return [
                'view' => 'views/members/form.php',
                'data' => ['errors' => ['Failed to add member: ' . $e->getMessage()], 'old' => $postData]
            ];
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $member = $this->memberModel->getById($id);
        
        if (!$member) {
            $_SESSION['message'] = 'Member not found!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=members');
        }
        
        return [
            'view' => 'views/members/form.php',
            'data' => [
                'member' => $member,
                'breadcrumbs' => [
                    ['label' => 'Members', 'url' => '?page=members'],
                    ['label' => 'Edit Member']
                ]
            ]
        ];
    }
    
    /**
     * Update member
     */
    public function update($id, $postData) {
        $errors = $this->validate($postData, $id);
        
        if (!empty($errors)) {
            $postData['id'] = $id;
            return [
                'view' => 'views/members/form.php',
                'data' => ['errors' => $errors, 'member' => $postData]
            ];
        }
        
        try {
            $this->memberModel->update($id, $postData);
            $_SESSION['message'] = 'Member updated successfully!';
            $_SESSION['message_type'] = 'success';
            $this->redirect('?page=members');
        } catch (Exception $e) {
            $postData['id'] = $id;
            return [
                'view' => 'views/members/form.php',
                'data' => ['errors' => ['Failed to update member: ' . $e->getMessage()], 'member' => $postData]
            ];
        }
    }
    
    /**
     * Display member profile with attendance history
     */
    public function show($id) {
        $member = $this->memberModel->getById($id);
        
        if (!$member) {
            $_SESSION['message'] = 'Member not found!';
            $_SESSION['message_type'] = 'error';
            $this->redirect('?page=members');
        }
        
        // Get attendance history
        $attendanceRecord = new AttendanceRecord();
        $attendanceHistory = $attendanceRecord->getByMember($id);
        $memberStats = $attendanceRecord->getMemberStats($id);
        
        // Calculate attendance rate
        $total = $memberStats['total_sessions'] ?? 0;
        $present = $memberStats['present'] ?? 0;
        $memberStats['attendance_rate'] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        
        return [
            'view' => 'views/members/view.php',
            'data' => [
                'member' => $member,
                'attendanceHistory' => $attendanceHistory,
                'memberStats' => $memberStats,
                'currentPage' => 'members'
            ]
        ];
    }
    
    /**
     * Delete member
     */
    public function destroy($id) {
        try {
            $this->memberModel->delete($id);
            $_SESSION['message'] = 'Member deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Failed to delete member!';
            $_SESSION['message_type'] = 'error';
        }
        
        $this->redirect('?page=members');
    }
    
    /**
     * Helper method to redirect
     */
    private function redirect($url) {
        if (headers_sent($file, $line)) {
            echo "<script>window.location.href='$url';</script>";
            exit;
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Validate member data
     */
    private function validate($data, $excludeId = null) {
        $errors = [];
        
        // Name validation
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'Name must be less than 100 characters';
        }
        
        // Field validation
        if (empty(trim($data['field'] ?? ''))) {
            $errors[] = 'Field is required';
        } elseif (strlen($data['field']) > 100) {
            $errors[] = 'Field must be less than 100 characters';
        }
        
        // Phone validation
        if (empty(trim($data['phone'] ?? ''))) {
            $errors[] = 'Phone number is required';
        } elseif (strlen($data['phone']) > 20) {
            $errors[] = 'Phone number must be less than 20 characters';
        }
        
        // Email validation
        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->memberModel->emailExists($data['email'], $excludeId)) {
            $errors[] = 'Email already exists';
        }
        
        return $errors;
    }
}

