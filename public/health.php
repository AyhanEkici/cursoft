<?php
/**
 * Health Check Endpoint for Render
 * Must be in public/ directory
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'service' => 'cursoft',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => getenv('APP_ENV') ?: 'development',
    'render' => getenv('RENDER') ? true : false,
    'checks' => []
];

// Check database connection
try {
    // Try both possible paths (root or public/)
    $dbPath = __DIR__ . '/../includes/Database.php';
    if (!file_exists($dbPath)) {
        $dbPath = __DIR__ . '/includes/Database.php';
    }
    if (file_exists($dbPath)) {
        require_once $dbPath;
        $db = new Database();
        $db->fetchOne("SELECT 1");
        $health['checks']['database'] = 'ok';
        $health['database_driver'] = $db->getDriver();
    } else {
        $health['checks']['database'] = 'skipped (Database.php not found)';
    }
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = 'failed: ' . $e->getMessage();
}

// Check disk space
$diskFree = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);
if ($diskTotal > 0) {
    $diskPercent = ($diskTotal - $diskFree) / $diskTotal * 100;
    $health['checks']['disk'] = [
        'status' => $diskPercent > 90 ? 'warning' : 'ok',
        'usage_percent' => round($diskPercent, 2)
    ];
}

// Check memory
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$health['checks']['memory'] = [
    'status' => 'ok',
    'usage' => $memoryUsage,
    'limit' => $memoryLimit
];

// Check PHP version
$health['checks']['php'] = [
    'status' => 'ok',
    'version' => phpversion()
];

// Set HTTP status code
if ($health['status'] === 'healthy') {
    http_response_code(200);
} else {
    http_response_code(503);
}

echo json_encode($health, JSON_PRETTY_PRINT);

