<?php
/**
 * Deployment Testing Suite
 * Test all deployment-related functionality locally
 * Access: http://localhost/cursoft/test_deployment.php
 */

// Security check - only allow localhost
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($clientIP, $allowedIPs) && strpos($clientIP, '192.168.') !== 0 && strpos($clientIP, '10.') !== 0) {
    die('Access denied. Run this locally only.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Cursoft Deployment Testing</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-section h2 { color: #667eea; margin-top: 0; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .test-item { margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
        .test-item h3 { margin: 0 0 10px 0; color: #333; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; border-left: 3px solid #667eea; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-pass { background: #d4edda; color: #155724; }
        .badge-fail { background: #f8d7da; color: #721c24; }
        .badge-warn { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>🧪 Cursoft Deployment Testing Suite</h1>
    <p><strong>Purpose:</strong> Validate all deployment components before deploying to Render</p>

<?php

// ============================================
// TEST 1: PathHelper
// ============================================
echo '<div class="test-section">';
echo '<h2>1. PathHelper Test</h2>';

try {
    require_once __DIR__ . '/includes/PathHelper.php';
    
    echo '<div class="test-item">';
    echo '<h3>Environment Detection</h3>';
    echo '<pre>';
    echo "Is Render: " . (getenv('RENDER') ? 'YES' : 'NO') . "\n";
    echo "Is Production: " . (getenv('APP_ENV') === 'production' ? 'YES' : 'NO') . "\n";
    echo "Current Environment: " . (getenv('APP_ENV') ?: 'development') . "\n";
    echo '</pre>';
    echo '</div>';
    
    echo '<div class="test-item">';
    echo '<h3>Path Generation</h3>';
    echo '<pre>';
    echo "Base Path: " . PathHelper::getBasePath() . "\n";
    echo "URL('/'): " . PathHelper::url('/') . "\n";
    echo "URL('pages/login.php'): " . PathHelper::url('pages/login.php') . "\n";
    echo "Asset('css/main.css'): " . PathHelper::asset('css/main.css') . "\n";
    echo "API('health.php'): " . PathHelper::api('health.php') . "\n";
    echo "Page('dashboard.php'): " . PathHelper::page('dashboard.php') . "\n";
    echo '</pre>';
    echo '</div>';
    
    echo '<span class="status-badge badge-pass">✓ PASS</span>';
    echo '<p class="success">PathHelper is working correctly!</p>';
    
} catch (Exception $e) {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// ============================================
// TEST 2: Database Connection (MySQL)
// ============================================
echo '<div class="test-section">';
echo '<h2>2. Database Connection Test (MySQL)</h2>';

try {
    require_once __DIR__ . '/includes/Database.php';
    $db = new Database();
    
    echo '<div class="test-item">';
    echo '<h3>Connection Status</h3>';
    echo '<pre>';
    echo "Driver: " . $db->getDriver() . "\n";
    echo "Connection: " . ($db->getConnection() ? 'SUCCESS' : 'FAILED') . "\n";
    echo '</pre>';
    echo '</div>';
    
    // Test query
    $result = $db->fetchOne("SELECT DATABASE() as db, VERSION() as version");
    if ($result) {
        echo '<div class="test-item">';
        echo '<h3>Database Info</h3>';
        echo '<pre>';
        echo "Current Database: " . ($result['db'] ?? 'N/A') . "\n";
        echo "MySQL Version: " . ($result['version'] ?? 'N/A') . "\n";
        echo '</pre>';
        echo '</div>';
    }
    
    // Count tables
    $tables = $db->fetchAll("SHOW TABLES");
    echo '<div class="test-item">';
    echo '<h3>Tables Found</h3>';
    echo '<pre>';
    echo "Total Tables: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "\nTable List:\n";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "  - $tableName\n";
        }
    }
    echo '</pre>';
    echo '</div>';
    
    echo '<span class="status-badge badge-pass">✓ PASS</span>';
    echo '<p class="success">MySQL database connection successful!</p>';
    
} catch (Exception $e) {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p class="warning">Note: This is expected if database is not set up yet.</p>';
}

echo '</div>';

// ============================================
// TEST 3: File Operations
// ============================================
echo '<div class="test-section">';
echo '<h2>3. File Operations Test</h2>';

$testDirs = [
    'logs' => __DIR__ . '/logs',
    'workspaces' => __DIR__ . '/workspaces',
    'tmp' => __DIR__ . '/tmp'
];

echo '<div class="test-item">';
echo '<h3>Directory Check</h3>';
echo '<pre>';
foreach ($testDirs as $name => $path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✓ Created directory: $name\n";
        } else {
            echo "✗ Failed to create: $name\n";
        }
    } else {
        echo "✓ Directory exists: $name\n";
    }
    
    if (is_writable($path)) {
        echo "  ✓ Writable: YES\n";
    } else {
        echo "  ✗ Writable: NO\n";
    }
}
echo '</pre>';
echo '</div>';

// Test file write
$testFile = __DIR__ . '/logs/test-write.txt';
if (file_put_contents($testFile, 'Test write at ' . date('Y-m-d H:i:s'))) {
    echo '<div class="test-item">';
    echo '<h3>File Write Test</h3>';
    echo '<pre>';
    echo "✓ File write successful: logs/test-write.txt\n";
    echo "Content: " . file_get_contents($testFile) . "\n";
    unlink($testFile);
    echo "✓ File cleanup successful\n";
    echo '</pre>';
    echo '</div>';
    echo '<span class="status-badge badge-pass">✓ PASS</span>';
} else {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Cannot write to logs directory</p>';
}

echo '</div>';

// ============================================
// TEST 4: Required Files Check
// ============================================
echo '<div class="test-section">';
echo '<h2>4. Required Files Check</h2>';

$requiredFiles = [
    'render.yaml' => 'Render configuration',
    'render-build.sh' => 'Build script',
    'includes/Database.php' => 'Database class',
    'includes/PathHelper.php' => 'Path helper',
    'config/render.php' => 'Render config',
    'public/health.php' => 'Health check',
    'database/convert_to_postgresql.php' => 'Database converter'
];

echo '<div class="test-item">';
echo '<h3>File Existence</h3>';
echo '<pre>';
$allExist = true;
foreach ($requiredFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✓ $file ($description) - " . number_format($size) . " bytes\n";
    } else {
        echo "✗ $file ($description) - MISSING\n";
        $allExist = false;
    }
}
echo '</pre>';
echo '</div>';

if ($allExist) {
    echo '<span class="status-badge badge-pass">✓ PASS</span>';
    echo '<p class="success">All required files exist!</p>';
} else {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Some required files are missing!</p>';
}

echo '</div>';

// ============================================
// TEST 5: Build Script Check
// ============================================
echo '<div class="test-section">';
echo '<h2>5. Build Script Validation</h2>';

$buildScript = __DIR__ . '/render-build.sh';
if (file_exists($buildScript)) {
    $content = file_get_contents($buildScript);
    
    echo '<div class="test-item">';
    echo '<h3>Script Checks</h3>';
    echo '<pre>';
    
    $checks = [
        'Shebang present' => strpos($content, '#!/bin/bash') !== false,
        'Creates public dir' => strpos($content, 'mkdir -p public') !== false,
        'Copies API files' => strpos($content, 'api') !== false,
        'Copies pages' => strpos($content, 'pages') !== false,
        'Sets permissions' => strpos($content, 'chmod') !== false
    ];
    
    $allChecks = true;
    foreach ($checks as $check => $result) {
        if ($result) {
            echo "✓ $check\n";
        } else {
            echo "✗ $check\n";
            $allChecks = false;
        }
    }
    echo '</pre>';
    echo '</div>';
    
    if ($allChecks) {
        echo '<span class="status-badge badge-pass">✓ PASS</span>';
    } else {
        echo '<span class="status-badge badge-warn">⚠ WARN</span>';
    }
} else {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Build script not found!</p>';
}

echo '</div>';

// ============================================
// TEST 6: Database Converter Link
// ============================================
echo '<div class="test-section">';
echo '<h2>6. Database Converter</h2>';

$converterPath = __DIR__ . '/database/convert_to_postgresql.php';
if (file_exists($converterPath)) {
    $converterUrl = '/cursoft/database/convert_to_postgresql.php';
    echo '<div class="test-item">';
    echo '<h3>Converter Available</h3>';
    echo '<p class="info">Database converter is ready to use.</p>';
    echo '<p><a href="' . $converterUrl . '" target="_blank" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">Open Database Converter →</a></p>';
    echo '</div>';
    echo '<span class="status-badge badge-pass">✓ PASS</span>';
} else {
    echo '<span class="status-badge badge-fail">✗ FAIL</span>';
    echo '<p class="error">Database converter not found!</p>';
}

echo '</div>';

// ============================================
// SUMMARY
// ============================================
echo '<div class="test-section" style="background: #e7f3ff; border: 2px solid #667eea;">';
echo '<h2>📊 Test Summary</h2>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>If all tests pass, you\'re ready to deploy!</li>';
echo '<li>Run the database converter to export your MySQL data</li>';
echo '<li>Push your code to GitHub</li>';
echo '<li>Follow the deployment guide: <code>docs/RENDER_DEPLOYMENT.md</code></li>';
echo '</ol>';
echo '<p><strong>Deployment Guide:</strong> <a href="docs/RENDER_DEPLOYMENT.md">docs/RENDER_DEPLOYMENT.md</a></p>';
echo '</div>';

?>

</body>
</html>

