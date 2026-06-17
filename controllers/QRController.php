<?php
class QRController {
    private $db;
    private $sessionModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->sessionModel = new AttendanceSession();
    }

    /**
     * GET ?page=qr&action=display&id=X
     * Admin: view/generate the QR code for a session.
     * Adding ?generate=1 forces a new token even if one is active.
     */
    public function display($sessionId) {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }

        $session = $this->sessionModel->getById($sessionId);
        if (!$session) {
            $_SESSION['error_message'] = 'Session not found.';
            header('Location: index.php?page=sessions');
            exit;
        }

        // Generate a fresh QR if none active or explicitly requested.
        // Admin may choose how long the code stays valid (allow-listed minutes).
        if (!$session['is_qr_active'] || isset($_GET['generate'])) {
            $allowedDurations = [5, 15, 30, 60];
            $duration = (int)($_GET['duration'] ?? 30);
            if (!in_array($duration, $allowedDurations, true)) {
                $duration = 30;
            }
            $this->sessionModel->generateQRCode($sessionId, $duration);
            $session = $this->sessionModel->getById($sessionId);
        }

        $pageTitle = 'QR Code – ' . htmlspecialchars($session['session_name']);
        $breadcrumbs = [
            ['label' => 'Sessions', 'url' => 'index.php?page=sessions'],
            ['label' => htmlspecialchars($session['session_name']), 'url' => 'index.php?page=sessions&action=view&id=' . $sessionId],
            ['label' => 'QR Code'],
        ];
        require_once __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/qr/display.php';
    }

    /**
     * POST ?page=qr&action=deactivate&id=X
     * Admin: manually deactivate the QR code.
     * CSRF is verified globally in index.php.
     */
    public function deactivate($sessionId) {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $this->sessionModel->deactivateQRCode($sessionId);
        header('Location: index.php?page=qr&action=display&id=' . $sessionId);
        exit;
    }

    /**
     * POST ?page=qr&action=scan  (JSON body)
     * Member: validate QR token and record attendance.
     * Global CSRF is skipped for this endpoint; token verified from JSON body.
     */
    public function scan() {
        header('Content-Type: application/json');

        if (($_SESSION['user_role'] ?? '') !== 'member') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Member access required']);
            return;
        }

        // Rate limit: max 5 scan attempts per minute per member
        if (!Security::checkRateLimit('qr_scan_' . ($_SESSION['user_id'] ?? ''), 5, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many attempts. Please wait a moment.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }

        // Verify CSRF token from JSON body (global check is skipped for this endpoint)
        $csrfToken = $input['csrf_token'] ?? '';
        if (!Security::verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Security token invalid. Please reload the page.']);
            return;
        }

        $token = trim($input['token'] ?? '');
        if (empty($token)) {
            echo json_encode(['success' => false, 'error' => 'No token received']);
            return;
        }

        // Find the session for this token
        $stmt = $this->db->query(
            "SELECT id FROM attendance_sessions WHERE qr_code_token = ? AND is_qr_active = 1 AND qr_code_expires_at > NOW()",
            [$token]
        );
        $sessionRow = $stmt->fetch();

        if (!$sessionRow) {
            echo json_encode(['success' => false, 'error' => 'QR code is invalid or has expired']);
            return;
        }

        $sessionId = $sessionRow['id'];
        $memberId  = (int)$_SESSION['user_id'];

        // Prevent duplicate attendance
        $existing = $this->db->query(
            "SELECT id FROM attendance_records WHERE session_id = ? AND member_id = ?",
            [$sessionId, $memberId]
        )->fetch();

        if ($existing) {
            echo json_encode(['success' => false, 'error' => 'Your attendance is already recorded for this session']);
            return;
        }

        // Record attendance
        $this->db->query(
            "INSERT INTO attendance_records (session_id, member_id, status, check_in_time) VALUES (?, ?, 'present', NOW())",
            [$sessionId, $memberId]
        );

        // Award 10 XP
        $this->db->query("UPDATE members SET points = points + 10 WHERE id = ?", [$memberId]);

        $newPoints = (int)$this->db->query("SELECT points FROM members WHERE id = ?", [$memberId])->fetchColumn();

        echo json_encode([
            'success'   => true,
            'message'   => 'Attendance recorded! +10 XP',
            'newPoints' => $newPoints,
        ]);
    }

    /**
     * GET ?page=qr&action=scanner
     * Member: camera scanner page.
     */
    public function scannerPage() {
        if (($_SESSION['user_role'] ?? '') !== 'member') {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $pageTitle = 'Scan QR Code';
        require_once __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/qr/scanner.php';
    }
}
