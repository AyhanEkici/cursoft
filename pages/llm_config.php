<?php
/**
 * LLM Configuration Page (Frontend)
 * Manage LLM provider API keys
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Database.php';

$sessionManager = new SessionManager();
$sessionManager->requireLogin();

$user = $sessionManager->getCurrentUser();
$db = new Database();

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
        } else {
            try {
                // Check if config already exists
                $existing = $db->fetchOne(
                    "SELECT id FROM llm_configs WHERE user_id = ? AND provider = ?",
                    [$user['id'], $provider]
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
                        [$user['id'], $provider, $apiKey, $model]
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
    [$user['id']]
);

// Available providers
$providers = [
    'openai' => ['name' => 'OpenAI', 'models' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']],
    'anthropic' => ['name' => 'Anthropic Claude', 'models' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku']],
    'google' => ['name' => 'Google Gemini', 'models' => ['gemini-pro', 'gemini-pro-vision']],
    'deepseek' => ['name' => 'DeepSeek', 'models' => ['deepseek-chat', 'deepseek-coder']],
    'ollama' => ['name' => 'Ollama (Local)', 'models' => ['llama2', 'codellama', 'mistral']]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LLM Configuration - Cursoft</title>
    <link rel="stylesheet" href="/cursoft/public/css/main.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🚀 Cursoft</div>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="new_project.php">New Project</a>
                <a href="llm_config.php">LLM Config</a>
                <span style="color: white;"><?php echo htmlspecialchars($user['name']); ?></span>
                <a href="../api/auth.php" onclick="event.preventDefault(); fetch('../api/auth.php', {method: 'POST', body: JSON.stringify({action: 'logout'}), headers: {'Content-Type': 'application/json'}}).then(() => window.location.href='login.php'); return false;">Logout</a>
            </nav>
        </div>
    </div>
    
    <div class="container">
        <h1 style="margin-bottom: 30px;">LLM Configuration</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Add New Key -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Add/Update API Key</h2>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Provider:</label>
                    <select name="provider" class="form-control" required>
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
                    <input type="text" name="api_key" class="form-control" placeholder="Enter your API key" required>
                </div>
                <div class="form-group">
                    <label>Default Model (optional):</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g., gpt-3.5-turbo">
                </div>
                <button type="submit" class="btn btn-primary">💾 Save API Key</button>
            </form>
        </div>
        
        <!-- Configured Providers -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Configured Providers</h2>
            </div>
            <?php if (empty($configs)): ?>
                <p style="color: #666; padding: 20px;">No API keys configured yet. Add one above to start using LLM features.</p>
            <?php else: ?>
                <table class="table">
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
                                <td style="font-family: 'Courier New', monospace; font-size: 0.9em;">
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
                                    <span class="badge badge-<?php echo $config['enabled'] ? 'success' : 'warning'; ?>">
                                        <?php echo $config['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="config_id" value="<?php echo $config['id']; ?>">
                                        <input type="hidden" name="enabled" value="<?php echo $config['enabled'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9em;">
                                            <?php echo $config['enabled'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this API key?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="config_id" value="<?php echo $config['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.9em;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

