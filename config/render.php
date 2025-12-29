<?php
/**
 * Render.com Production Configuration
 * Load this when running on Render
 */

// Detect Render environment
define('IS_RENDER', getenv('RENDER') !== false);
define('IS_PRODUCTION', IS_RENDER || getenv('APP_ENV') === 'production');

// Base URL - Render provides this automatically
$basePath = getenv('BASE_PATH') ?: '';
if (IS_RENDER) {
    // Render provides RENDER_EXTERNAL_URL
    $baseUrl = getenv('RENDER_EXTERNAL_URL') ?: 'https://cursoft-app.onrender.com';
    define('BASE_URL', rtrim($baseUrl, '/') . $basePath);
} else {
    define('BASE_URL', 'http://localhost' . $basePath);
}

// Error reporting
if (IS_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// Logging
ini_set('log_errors', '1');
$logPath = __DIR__ . '/../logs/php-errors.log';
if (!is_dir(dirname($logPath))) {
    mkdir(dirname($logPath), 0755, true);
}
ini_set('error_log', $logPath);

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', IS_RENDER ? '1' : '0'); // HTTPS on Render
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

// Performance
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

// Timezone
date_default_timezone_set('UTC');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('LOG_PATH', ROOT_PATH . '/logs/');
define('WORKSPACE_PATH', ROOT_PATH . '/workspaces/');

// Create necessary directories
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}
if (!is_dir(WORKSPACE_PATH)) {
    mkdir(WORKSPACE_PATH, 0755, true);
}

