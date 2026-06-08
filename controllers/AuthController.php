<?php
/**
 * AuthController
 * Handles authentication, registration and logout processes
 */

require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../helpers/Security.php';

class AuthController {
    private $memberModel;
    
    public function __construct() {
        $this->memberModel = new Member();
    }
    
    /**
     * Show the sliding Login/Register page
     */
    public function index() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        
        $pageTitle = 'Sign In / Sign Up - BIT English Club';
        return [
            'view' => __DIR__ . '/../views/auth.php',
            'data' => [
                'pageTitle' => $pageTitle
            ]
        ];
    }
    
    /**
     * Handle Login POST Request
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=auth');
            exit;
        }
        
        // Rate limit login attempts (max 10 attempts per 5 minutes)
        if (!Security::checkRateLimit('login', 10, 300)) {
            $_SESSION['error_message'] = 'Too many login attempts. Please try again after 5 minutes.';
            header('Location: index.php?page=auth');
            exit;
        }
        
        $email = Security::string($_POST['email'] ?? '', 100);
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'Please enter both email and password.';
            header('Location: index.php?page=auth');
            exit;
        }
        
        // Find member by email
        $member = $this->memberModel->getByEmail($email);
        
        if ($member && !empty($member['password']) && password_verify($password, $member['password'])) {
            // Regeneration of session for security (session hijacking prevention)
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $member['id'];
            $_SESSION['user_name'] = $member['name'];
            $_SESSION['user_email'] = $member['email'];
            $_SESSION['user_role'] = $member['role'];
            $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($member['name']) . "!";
            
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = 'Invalid email or password.';
            header('Location: index.php?page=auth');
            exit;
        }
    }
    
    /**
     * Handle Registration POST Request
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=auth');
            exit;
        }
        
        // Rate limit registration attempts (max 5 attempts per 5 minutes)
        if (!Security::checkRateLimit('register', 5, 300)) {
            $_SESSION['error_message'] = 'Too many registration attempts. Please try again after 5 minutes.';
            header('Location: index.php?page=auth&active=register');
            exit;
        }
        
        $name = Security::string($_POST['name'] ?? '', 100);
        $email = Security::string($_POST['email'] ?? '', 100);
        $phone = Security::string($_POST['phone'] ?? '', 20);
        $field = Security::string($_POST['field'] ?? '', 100);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($phone) || empty($field) || empty($password)) {
            $_SESSION['error_message'] = 'All fields are required for registration.';
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=auth&active=register');
            exit;
        }
        
        if (!Security::isValidEmail($email)) {
            $_SESSION['error_message'] = 'Invalid email format.';
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=auth&active=register');
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=auth&active=register');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters long.';
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=auth&active=register');
            exit;
        }
        
        // Register member (claims profile or creates a new one)
        $memberId = $this->memberModel->registerMember([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'field' => $field,
            'password' => $password
        ]);
        
        if ($memberId === false) {
            $_SESSION['error_message'] = 'This email is already fully registered. Please sign in instead.';
            $_SESSION['old_input'] = $_POST;
            header('Location: index.php?page=auth');
            exit;
        }
        
        // Retrieve newly registered member details and log them in
        $member = $this->memberModel->getById($memberId);
        
        session_regenerate_id(true);
        $_SESSION['user_id'] = $member['id'];
        $_SESSION['user_name'] = $member['name'];
        $_SESSION['user_email'] = $member['email'];
        $_SESSION['user_role'] = $member['role'];
        $_SESSION['success_message'] = "Account registered successfully! Welcome to the club, " . htmlspecialchars($member['name']) . "!";
        
        header('Location: index.php');
        exit;
    }
    
    /**
     * Handle Logout Request
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Start a new session for messages
        session_start();
        $_SESSION['success_message'] = 'You have logged out successfully.';
        
        header('Location: index.php?page=auth');
        exit;
    }
}
