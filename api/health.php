<?php
/**
 * Health Check Endpoint
 * Used for monitoring and load balancer health checks
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check database connection
try {
    require_once __DIR__ . '/../includes/Database.php';
    $db = new Database();
    $db->fetchOne("SELECT 1");
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = 'failed: ' . $e->getMessage();
}

// Check disk space
$diskFree = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);
$diskPercent = ($diskTotal - $diskFree) / $diskTotal * 100;

$health['checks']['disk'] = [
    'status' => $diskPercent > 90 ? 'warning' : 'ok',
    'usage_percent' => round($diskPercent, 2)
];

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
?>

