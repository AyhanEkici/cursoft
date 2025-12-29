<?php
/**
 * Phase 2: Container Manager Test Interface
 * Simple test page for container operations
 */

require_once __DIR__ . '/../includes/ContainerManager.php';
require_once __DIR__ . '/../includes/ContainerOrchestrator.php';
require_once __DIR__ . '/../includes/Database.php';

$containerManager = new ContainerManager();
$orchestrator = new ContainerOrchestrator();
$db = new Database();

$testResults = [];

// Test Docker availability
$testResults[] = [
    'test' => 'Docker Availability',
    'result' => $containerManager->isDockerAvailable() ? '✅ PASS' : '❌ FAIL',
    'message' => $containerManager->isDockerAvailable() 
        ? 'Docker is available' 
        : 'Docker is not available. Please start Docker Desktop.'
];

// Test database connection
try {
    $containers = $containerManager->getAllContainers();
    $testResults[] = [
        'test' => 'Database Connection',
        'result' => '✅ PASS',
        'message' => 'Database connection successful. Found ' . count($containers) . ' containers.'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Database Connection',
        'result' => '❌ FAIL',
        'message' => 'Error: ' . $e->getMessage()
    ];
}

// Test getting projects
try {
    $projects = $db->fetchAll("SELECT id, name FROM projects LIMIT 5");
    $testResults[] = [
        'test' => 'Projects Available',
        'result' => '✅ PASS',
        'message' => 'Found ' . count($projects) . ' projects in database.'
    ];
} catch (Exception $e) {
    $testResults[] = [
        'test' => 'Projects Available',
        'result' => '❌ FAIL',
        'message' => 'Error: ' . $e->getMessage()
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 2: Container Manager Tests - Cursoft</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .test-result {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .test-result h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .test-result .result {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .test-result .message {
            color: #666;
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Cursoft - Phase 2</h1>
            <p>Container Manager Test Suite</p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <h3>📋 Test Checklist</h3>
                <ul>
                    <li>Docker Desktop must be installed and running</li>
                    <li>Database schema must be updated (run schema_phase2.sql)</li>
                    <li>At least one project should exist in the database</li>
                    <li>PHP exec() function must be enabled</li>
                </ul>
            </div>
            
            <h2>Test Results</h2>
            <?php foreach ($testResults as $test): ?>
                <div class="test-result">
                    <h3><?php echo htmlspecialchars($test['test']); ?></h3>
                    <div class="result"><?php echo $test['result']; ?></div>
                    <div class="message"><?php echo htmlspecialchars($test['message']); ?></div>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="container_manager.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Go to Container Manager →
                </a>
            </div>
        </div>
    </div>
</body>
</html>

