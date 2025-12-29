<?php
/**
 * Production Configuration
 * Load this in production environment
 */

// Error reporting (disabled in production)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1'); // Enable in production with HTTPS
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Performance
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

// Timezone
date_default_timezone_set('UTC');

// Database configuration from environment
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'cursoft');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application configuration
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Logging
define('LOG_PATH', __DIR__ . '/../logs/');
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

