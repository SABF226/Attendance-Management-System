<?php
/**
 * Security Helper Class
 * Provides CSRF protection and security utilities
 */
class Security {
    
    /**
     * Generate or retrieve CSRF token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token from request
     */
    public static function verifyCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token field HTML
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate CSRF token or die
     */
    public static function requireCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::verifyCsrfToken($token)) {
            http_response_code(403);
            die('Security token validation failed. Please refresh the page and try again.');
        }
    }
    
    /**
     * Sanitize integer input
     */
    public static function int($value, $default = 0) {
        return is_numeric($value) ? (int)$value : $default;
    }
    
    /**
     * Sanitize string input
     */
    public static function string($value, $maxLength = 255) {
        $value = trim($value ?? '');
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        return $value;
    }
    
    /**
     * Validate email format
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Rate limiting check (simple session-based)
     */
    public static function checkRateLimit($action, $maxAttempts = 10, $window = 300) {
        $key = 'rate_limit_' . $action;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => $now];
        }
        
        $data = $_SESSION[$key];
        
        // Reset if window expired
        if ($now - $data['first_attempt'] > $window) {
            $_SESSION[$key] = ['attempts' => 1, 'first_attempt' => $now];
            return true;
        }
        
        // Check limit
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['attempts']++;
        return true;
    }
    
    /**
     * Get base URL for the application
     */
    public static function baseUrl($path = '') {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($scriptDir === '/') {
            $basePath = '';
        } else {
            $basePath = rtrim($scriptDir, '/');
        }
        return $basePath . '/' . ltrim($path, '/');
    }
}
