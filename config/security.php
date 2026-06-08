<?php
/**
 * Security Configuration
 * Session hardening and security headers
 */

// Session Security Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 0); // Browser session

// Regenerate session ID periodically for security
function regenerateSessionIfNeeded() {
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
    
    // Regenerate every 15 minutes
    if (time() - $_SESSION['last_regeneration'] > 900) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Security Headers Function
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Type sniffing prevention
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions Policy (formerly Feature Policy)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Content Security Policy (CSP) - Adjust as needed
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline'; "; // Allow inline scripts for now
    $csp .= "style-src 'self' 'unsafe-inline'; ";
    $csp .= "img-src 'self' data:; ";
    $csp .= "font-src 'self'; ";
    $csp .= "connect-src 'self'; ";
    $csp .= "frame-ancestors 'none'; ";
    header("Content-Security-Policy: $csp");
}

// Call on every request
regenerateSessionIfNeeded();
setSecurityHeaders();
