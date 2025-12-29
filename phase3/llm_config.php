<?php
/**
 * LLM Configuration Interface
 * Add and manage LLM provider API keys
 */

require_once __DIR__ . '/../includes/Database.php';

$db = new Database();
$userId = 1; // Test user
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $provider = $_POST['provider'] ?? '';
        $apiKey = trim($_POST['api_key'] ?? '');
        $model = $_POST['model'] ?? '';
        
        if (empty($provider) || empty($apiKey)) {
            $message = "Provider and API key are required.";
            $messageType = 'error';
        } elseif ($provider !== 'ollama' && isTestKey($apiKey)) {
            $message = "⚠️ Test/placeholder API key detected. Please enter a real API key from the provider.";
            $messageType = 'error';
        } else {
            try {
                // Check if config already exists
                $existing = $db->fetchOne(
                    "SELECT id FROM llm_configs WHERE user_id = ? AND provider = ?",
                    [$userId, $provider]
                );
                
                if ($existing) {
                    // Update existing
                    $db->query(
                        "UPDATE llm_configs SET api_key = ?, model = ?, enabled = 1 WHERE id = ?",
                        [$apiKey, $model, $existing['id']]
                    );
                    $message = "API key updated for " . ucfirst($provider);
                } else {
                    // Insert new
                    $db->query(
                        "INSERT INTO llm_configs (user_id, provider, api_key, model, enabled) VALUES (?, ?, ?, ?, 1)",
                        [$userId, $provider, $apiKey, $model]
                    );
                    $message = "API key added for " . ucfirst($provider);
                }
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'toggle') {
        $configId = $_POST['config_id'] ?? null;
        $enabled = $_POST['enabled'] ?? 0;
        
        if ($configId) {
            $db->query(
                "UPDATE llm_configs SET enabled = ? WHERE id = ?",
                [$enabled, $configId]
            );
            $message = "Configuration updated.";
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $configId = $_POST['config_id'] ?? null;
        
        if ($configId) {
            $db->query("DELETE FROM llm_configs WHERE id = ?", [$configId]);
            $message = "Configuration deleted.";
            $messageType = 'success';
        }
    }
}

// Get all configurations
$configs = $db->fetchAll(
    "SELECT * FROM llm_configs WHERE user_id = ? ORDER BY provider",
    [$userId]
);

// Available providers
$providers = [
    'openai' => ['name' => 'OpenAI', 'models' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']],
    'anthropic' => ['name' => 'Anthropic Claude', 'models' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku']],
    'google' => ['name' => 'Google Gemini', 'models' => ['gemini-pro', 'gemini-pro-vision']],
    'deepseek' => ['name' => 'DeepSeek', 'models' => ['deepseek-chat', 'deepseek-coder']],
    'ollama' => ['name' => 'Ollama (Local)', 'models' => ['llama2', 'codellama', 'mistral']]
];

// Helper function to check if key is a test key
function isTestKey($apiKey) {
    $testPatterns = [
        'test-key',
        'test-key-',
        'replace',
        'placeholder',
        'your-api-key',
        'sk-test',
        'sk-0000'
    ];
    
    $apiKeyLower = strtolower($apiKey);
    foreach ($testPatterns as $pattern) {
        if (strpos($apiKeyLower, $pattern) !== false) {
            return true;
        }
    }
    
    if (strlen($apiKey) < 10 && $apiKey !== 'local') {
        return true;
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LLM Configuration - Cursoft</title>
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
            max-width: 1200px;
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
        
        .add-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        select, input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.9em;
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
        
        .api-key-display {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #666;
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .info-box h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 LLM Configuration</h1>
            <p>Manage API Keys for LLM Providers</p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <h4>ℹ️ How to Get API Keys</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a></li>
                    <li><strong>Anthropic:</strong> <a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a></li>
                    <li><strong>Google:</strong> <a href="https://makersuite.google.com/app/apikey" target="_blank">makersuite.google.com</a></li>
                    <li><strong>DeepSeek:</strong> <a href="https://platform.deepseek.com/" target="_blank">platform.deepseek.com</a></li>
                    <li><strong>Ollama:</strong> No API key needed (local installation)</li>
                </ul>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="add-form">
                <h2>Add/Update API Key</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Provider:</label>
                        <select name="provider" required>
                            <option value="">Select a provider...</option>
                            <?php foreach ($providers as $key => $provider): ?>
                                <option value="<?php echo $key; ?>">
                                    <?php echo $provider['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>API Key:</label>
                        <input type="text" name="api_key" placeholder="Enter your API key" required>
                    </div>
                    <div class="form-group">
                        <label>Default Model (optional):</label>
                        <input type="text" name="model" placeholder="e.g., gpt-3.5-turbo">
                    </div>
                    <button type="submit" class="btn">💾 Save API Key</button>
                </form>
            </div>
            
            <h2>Configured Providers</h2>
            <?php if (empty($configs)): ?>
                <p style="padding: 20px; background: #fff3cd; border-radius: 8px;">
                    No API keys configured yet. Add one above to start using LLM features.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>API Key</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($configs as $config): ?>
                            <tr>
                                <td><?php echo ucfirst($config['provider']); ?></td>
                                <td class="api-key-display">
                                    <?php 
                                    $key = $config['api_key'];
                                    if (strlen($key) > 20) {
                                        echo substr($key, 0, 8) . '...' . substr($key, -4);
                                    } else {
                                        echo str_repeat('*', strlen($key));
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($config['model'] ?? 'Default'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $config['enabled'] ? 'status-enabled' : 'status-disabled'; ?>">
                                        <?php echo $config['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="config_id" value="<?php echo $config['id']; ?>">
                                        <input type="hidden" name="enabled" value="<?php echo $config['enabled'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-small">
                                            <?php echo $config['enabled'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this API key?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="config_id" value="<?php echo $config['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="llm_test.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-right: 10px;">
                    Test LLM →
                </a>
                <a href="../tests/test_phase2_phase3.php" style="display: inline-block; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Run Tests →
                </a>
            </div>
        </div>
    </div>
</body>
</html>

