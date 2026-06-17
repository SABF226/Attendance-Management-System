<?php
/**
 * English Club Attendance List - Main Entry Point
 */

session_start();

// Error reporting for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Autoload classes
spl_autoload_register(function ($class) {
    // Define class paths
    $paths = [
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/config/',
        __DIR__ . '/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Get current page and action
$page = Security::string($_GET['page'] ?? 'dashboard', 50);
$action = Security::string($_GET['action'] ?? 'index', 50);
$id = Security::int($_GET['id'] ?? null);

// Validate CSRF token for all POST requests
// Exception: QR scan endpoint uses JSON body with its own CSRF verification
$isQrScan = ($page === 'qr' && $action === 'scan');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isQrScan) {
    Security::requireCsrf();
}

// Check if this is an export action (skip headers)
$isExport = ($page === 'sessions' && $action === 'export');

// Include database config
require_once __DIR__ . '/config/database.php';

// Include security configuration (headers, session hardening)
// Skip headers for export actions (FPDF requires raw output)
if (!$isExport) {
    require_once __DIR__ . '/config/security.php';
}

// Determine current page for navigation
$currentPage = $page;

// Initialize breadcrumbs variable
$breadcrumbs = null;

// Session-based authentication middleware
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    // If not logged in, force redirect to auth page (unless performing auth actions)
    if ($page !== 'auth' || !in_array($action, ['index', 'login', 'register'])) {
        header('Location: index.php?page=auth');
        exit;
    }
} else {
    // If logged in, restrict normal members to dashboard, leaderboard, and logout
    $userRole = $_SESSION['user_role'] ?? 'member';
    if ($userRole === 'member') {
        if (!in_array($page, ['dashboard', 'leaderboard', 'qr']) && !($page === 'auth' && $action === 'logout')) {
            $_SESSION['error_message'] = 'Access Denied: Administrative permissions required.';
            header('Location: index.php?page=dashboard');
            exit;
        }
    }
}

try {
    // Route handling
    switch ($page) {
        // Auth routing
        case 'auth':
            $controller = new AuthController();
            switch ($action) {
                case 'index':
                    $result = $controller->index();
                    include $result['view'];
                    exit;
                case 'login':
                    $controller->login();
                    break;
                case 'register':
                    $controller->register();
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                default:
                    $result = $controller->index();
                    include $result['view'];
                    exit;
            }
            break;
            
        // Leaderboard routing
        case 'leaderboard':
            $recordModel = new AttendanceRecord();
            $topAttendees = $recordModel->getTopAttendees(20);
            require_once __DIR__ . '/views/header.php';
            include __DIR__ . '/views/leaderboard.php';
            break;

        // Dashboard
        case 'dashboard':
            $memberModel = new Member();
            $sessionModel = new AttendanceSession();
            $recordModel = new AttendanceRecord();
            
            if ($_SESSION['user_role'] === 'member') {
                $myId = $_SESSION['user_id'];
                $myStats = $recordModel->getMemberStats($myId);
                $myHistory = $recordModel->getByMember($myId);
                $overallStats = $recordModel->getOverallStats();
            } else {
                $totalMembers = $memberModel->count();
                $totalSessions = $sessionModel->count();
                $recentSessions = $sessionModel->getRecent(5);
                $overallStats = $recordModel->getOverallStats();
            }
            
            require_once __DIR__ . '/views/header.php';
            include __DIR__ . '/views/dashboard.php';
            break;
            
        // Members
        case 'members':
            $controller = new MemberController();
            
            switch ($action) {
                case 'index':
                case 'list':
                    $members = $controller->index();
                    require_once __DIR__ . '/views/header.php';
                    include __DIR__ . '/views/members/index.php';
                    break;
                    
                case 'create':
                    $result = $controller->create();
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    include $result['view'];
                    break;
                    
                case 'store':
                    $result = $controller->store($_POST);
                    if ($result['view']) {
                        if (isset($result['data']['breadcrumbs'])) {
                            $breadcrumbs = $result['data']['breadcrumbs'];
                        }
                        require_once __DIR__ . '/views/header.php';
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'edit':
                    $result = $controller->edit($id);
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'view':
                    $result = $controller->show($id);
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'update':
                    $result = $controller->update($id, $_POST);
                    if ($result['view']) {
                        if (isset($result['data']['breadcrumbs'])) {
                            $breadcrumbs = $result['data']['breadcrumbs'];
                        }
                        require_once __DIR__ . '/views/header.php';
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'delete':
                    $controller->destroy($id);
                    break;
                    
                case 'search':
                    $members = $controller->index();
                    require_once __DIR__ . '/views/header.php';
                    include __DIR__ . '/views/members/index.php';
                    break;
                    
                default:
                    $members = $controller->index();
                    require_once __DIR__ . '/views/header.php';
                    include __DIR__ . '/views/members/index.php';
            }
            break;
            
        // Sessions
        case 'sessions':
            $controller = new AttendanceController();
            
            switch ($action) {
                case 'index':
                case 'list':
                    $result = $controller->sessionIndex();
                    $sessions = $result['sessions'];
                    $monthlyStats = $result['monthlyStats'];
                    $filters = $result['filters'] ?? [];
                    $sort = $result['sort'] ?? 'date_desc';
                    require_once __DIR__ . '/views/header.php';
                    include __DIR__ . '/views/sessions/index.php';
                    break;
                    
                case 'create':
                    $result = $controller->create();
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'store':
                    $result = $controller->storeSession($_POST);
                    if ($result['view']) {
                        if (isset($result['data']['breadcrumbs'])) {
                            $breadcrumbs = $result['data']['breadcrumbs'];
                        }
                        require_once __DIR__ . '/views/header.php';
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'take':
                    $result = $controller->takeAttendance($id);
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'save':
                    $controller->saveAttendance($id, $_POST);
                    break;
                    
                case 'view':
                    $result = $controller->viewSession($id);
                    if (isset($result['data']['breadcrumbs'])) {
                        $breadcrumbs = $result['data']['breadcrumbs'];
                    }
                    require_once __DIR__ . '/views/header.php';
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'export':
                    $format = $_GET['format'] ?? 'pdf';
                    $controller->exportSession($id, $format);
                    break;
                    
                case 'delete':
                    $controller->deleteSession($id);
                    break;
                    
                default:
                    $result = $controller->sessionIndex();
                    $sessions = $result['sessions'];
                    $monthlyStats = $result['monthlyStats'];
                    require_once __DIR__ . '/views/header.php';
                    include __DIR__ . '/views/sessions/index.php';
            }
            break;
            
        // QR Code attendance
        case 'qr':
            $controller = new QRController();
            switch ($action) {
                case 'display':
                    $controller->display($id);
                    break;
                case 'deactivate':
                    $controller->deactivate($id);
                    break;
                case 'scan':
                    $controller->scan();
                    exit;
                case 'scanner':
                    $controller->scannerPage();
                    break;
                default:
                    header('Location: index.php?page=sessions');
                    exit;
            }
            break;

        // Default to dashboard
        default:
            $memberModel = new Member();
            $sessionModel = new AttendanceSession();
            $recordModel = new AttendanceRecord();
            
            if ($_SESSION['user_role'] === 'member') {
                $myId = $_SESSION['user_id'];
                $myStats = $recordModel->getMemberStats($myId);
                $myHistory = $recordModel->getByMember($myId);
                $overallStats = $recordModel->getOverallStats();
            } else {
                $totalMembers = $memberModel->count();
                $totalSessions = $sessionModel->count();
                $recentSessions = $sessionModel->getRecent(5);
                $overallStats = $recordModel->getOverallStats();
            }
            
            require_once __DIR__ . '/views/header.php';
            include __DIR__ . '/views/dashboard.php';
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-error">';
    echo '<h3>Database Error</h3>';
    echo '<p>Please make sure the database is set up correctly.</p>';
    echo '<p>Run the SQL schema in config/schema.sql to create the required tables.</p>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
} catch (Exception $e) {
    echo '<div class="alert alert-error">';
    echo '<h3>Error</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

// Include footer
require_once __DIR__ . '/views/footer.php';

