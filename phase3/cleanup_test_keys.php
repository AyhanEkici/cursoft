<?php
/**
 * Quick Cleanup: Remove or Disable Test API Keys
 * This script helps clean up test/placeholder API keys
 */

require_once __DIR__ . '/../includes/Database.php';

$db = new Database();
$userId = 1;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'disable_all_test_keys') {
        // Disable all test keys
        $testPatterns = ['test-key', 'replace', 'placeholder', 'your-api-key', 'sk-test'];
        $patterns = implode("', '", $testPatterns);
        
        $db->query(
            "UPDATE llm_configs 
             SET enabled = 0 
             WHERE user_id = ? 
             AND (
                 api_key LIKE '%test-key%' 
                 OR api_key LIKE '%replace%' 
                 OR api_key LIKE '%placeholder%'
                 OR api_key LIKE '%your-api-key%'
                 OR api_key LIKE 'sk-test%'
                 OR (LENGTH(api_key) < 10 AND api_key != 'local')
             )",
            [$userId]
        );
        
        $message = "All test keys have been disabled.";
        $messageType = 'success';
    } elseif ($action === 'delete_all_test_keys') {
        // Delete all test keys
        $db->query(
            "DELETE FROM llm_configs 
             WHERE user_id = ? 
             AND (
                 api_key LIKE '%test-key%' 
                 OR api_key LIKE '%replace%' 
                 OR api_key LIKE '%placeholder%'
                 OR api_key LIKE '%your-api-key%'
                 OR api_key LIKE 'sk-test%'
                 OR (LENGTH(api_key) < 10 AND api_key != 'local')
             )",
            [$userId]
        );
        
        $message = "All test keys have been deleted.";
        $messageType = 'success';
    } elseif ($action === 'delete_single') {
        $configId = $_POST['config_id'] ?? null;
        if ($configId) {
            $db->query("DELETE FROM llm_configs WHERE id = ?", [$configId]);
            $message = "Test key deleted.";
            $messageType = 'success';
        }
    }
}

// Get all test keys
$testKeys = $db->fetchAll(
    "SELECT * FROM llm_configs 
     WHERE user_id = ? 
     AND (
         api_key LIKE '%test-key%' 
         OR api_key LIKE '%replace%' 
         OR api_key LIKE '%placeholder%'
         OR api_key LIKE '%your-api-key%'
         OR api_key LIKE 'sk-test%'
         OR (LENGTH(api_key) < 10 AND api_key != 'local')
     )
     ORDER BY provider",
    [$userId]
);

// Get all configs
$allConfigs = $db->fetchAll(
    "SELECT * FROM llm_configs WHERE user_id = ? ORDER BY provider",
    [$userId]
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Test Keys - Cursoft</title>
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
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .warning-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 30px;
        }
        
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }
        
        .api-key-display {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #666;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-disabled {
            background: #fff3cd;
            color: #856404;
        }
        
        .links {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 8px;
        }
        
        .links a {
            display: inline-block;
            margin: 5px 10px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧹 Cleanup Test Keys</h1>
            <p>Remove or Disable Invalid API Keys</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="warning-box">
                <h3>⚠️ Test Keys Detected</h3>
                <p>Found <strong><?php echo count($testKeys); ?></strong> test/placeholder API key(s). These cannot be used for LLM requests.</p>
                <p><strong>Options:</strong></p>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li><strong>Disable:</strong> Keep the entries but disable them (you can update later)</li>
                    <li><strong>Delete:</strong> Remove the entries completely</li>
                    <li><strong>Update:</strong> Go to LLM Configuration and replace with real keys</li>
                </ul>
            </div>
            
            <?php if (!empty($testKeys)): ?>
                <div class="action-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="disable_all_test_keys">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Disable all test keys?')">
                            🚫 Disable All Test Keys
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_all_test_keys">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete all test keys? This cannot be undone.')">
                            🗑️ Delete All Test Keys
                        </button>
                    </form>
                </div>
                
                <h2>Test Keys Found:</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>API Key (masked)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testKeys as $key): ?>
                            <tr>
                                <td><?php echo ucfirst($key['provider']); ?></td>
                                <td class="api-key-display">
                                    <?php 
                                    $keyValue = $key['api_key'];
                                    if (strlen($keyValue) > 20) {
                                        echo substr($keyValue, 0, 8) . '...' . substr($keyValue, -4);
                                    } else {
                                        echo str_repeat('*', min(strlen($keyValue), 20));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $key['enabled'] ? 'status-enabled' : 'status-disabled'; ?>">
                                        <?php echo $key['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_single">
                                        <input type="hidden" name="config_id" value="<?php echo $key['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.9em;" onclick="return confirm('Delete this test key?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 20px; background: #d4edda; border-radius: 8px; text-align: center;">
                    <h3 style="color: #155724;">✅ No Test Keys Found!</h3>
                    <p style="margin-top: 10px;">All API keys appear to be valid.</p>
                </div>
            <?php endif; ?>
            
            <div class="links">
                <h3>Next Steps</h3>
                <a href="llm_config.php">⚙️ Manage API Keys</a>
                <a href="llm_test.php">🧪 Test LLM</a>
                <a href="../tests/test_phase2_phase3.php">✅ Run Tests</a>
            </div>
        </div>
    </div>
</body>
</html>

