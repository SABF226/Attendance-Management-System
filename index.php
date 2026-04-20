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
        __DIR__ . '/config/'
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
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Check if this is an export action (skip headers)
$isExport = ($page === 'sessions' && $action === 'export');

// Include database config
require_once __DIR__ . '/config/database.php';

// Determine current page for navigation
$currentPage = $page;

// Include header only if not exporting
if (!$isExport) {
    $pageTitle = 'English Club Attendance';
    require_once __DIR__ . '/views/header.php';
}

try {
    // Route handling
    switch ($page) {
        // Dashboard
        case 'dashboard':
            $memberModel = new Member();
            $sessionModel = new AttendanceSession();
            $recordModel = new AttendanceRecord();
            
            $totalMembers = $memberModel->count();
            $totalSessions = $sessionModel->count();
            $recentSessions = $sessionModel->getRecent(5);
            $overallStats = $recordModel->getOverallStats();
            
            include __DIR__ . '/views/dashboard.php';
            break;
            
        // Members
        case 'members':
            $controller = new MemberController();
            
            switch ($action) {
                case 'index':
                case 'list':
                    $members = $controller->index();
                    include __DIR__ . '/views/members/index.php';
                    break;
                    
                case 'create':
                    $result = $controller->create();
                    include $result['view'];
                    break;
                    
                case 'store':
                    $result = $controller->store($_POST);
                    if ($result['view']) {
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'edit':
                    $result = $controller->edit($id);
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'update':
                    $result = $controller->update($id, $_POST);
                    if ($result['view']) {
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'delete':
                    $controller->destroy($id);
                    break;
                    
                case 'search':
                    $members = $controller->index();
                    include __DIR__ . '/views/members/index.php';
                    break;
                    
                default:
                    $members = $controller->index();
                    include __DIR__ . '/views/members/index.php';
            }
            break;
            
        // Sessions
        case 'sessions':
            $controller = new AttendanceController();
            
            switch ($action) {
                case 'index':
                case 'list':
                    $sessions = $controller->sessionIndex();
                    include __DIR__ . '/views/sessions/index.php';
                    break;
                    
                case 'create':
                    $result = $controller->create();
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'store':
                    $result = $controller->storeSession($_POST);
                    if ($result['view']) {
                        extract($result['data']);
                        include $result['view'];
                    }
                    break;
                    
                case 'take':
                    $result = $controller->takeAttendance($id);
                    extract($result['data']);
                    include $result['view'];
                    break;
                    
                case 'save':
                    $controller->saveAttendance($id, $_POST);
                    break;
                    
                case 'view':
                    $result = $controller->viewSession($id);
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
                    $sessions = $controller->sessionIndex();
                    include __DIR__ . '/views/sessions/index.php';
            }
            break;
            
        // Default to dashboard
        default:
            $memberModel = new Member();
            $sessionModel = new AttendanceSession();
            $recordModel = new AttendanceRecord();
            
            $totalMembers = $memberModel->count();
            $totalSessions = $sessionModel->count();
            $recentSessions = $sessionModel->getRecent(5);
            $overallStats = $recordModel->getOverallStats();
            
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

