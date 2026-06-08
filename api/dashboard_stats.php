<?php
/**
 * API Endpoint for Dashboard Statistics
 * Returns JSON data for charts
 */

// Disable error display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Error log file
$logFile = __DIR__ . '/../logs/api_errors.log';

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

session_start();

// Set JSON header and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../config/',
        __DIR__ . '/../helpers/',
        __DIR__ . '/../controllers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/../config/database.php';

try {
    $recordModel = new AttendanceRecord();
    
    $data = [
        'trend' => $recordModel->getTrendData(5),
        'statusDistribution' => $recordModel->getStatusDistribution(),
        'topAttendees' => $recordModel->getTopAttendees(5)
    ];
    
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    // Log error to file
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, $logFile);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
