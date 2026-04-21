<?php
/**
 * API Endpoint for Dashboard Statistics
 * Returns JSON data for charts
 */

session_start();

// Set JSON header
header('Content-Type: application/json');

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../config/'
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
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
