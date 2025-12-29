<?php
/**
 * Phase 3: LLM Bridge Test Interface
 * Test LLM provider integration
 */

require_once __DIR__ . '/../includes/LLMBridge.php';
require_once __DIR__ . '/../includes/Database.php';

$llmBridge = new LLMBridge();
$db = new Database();

$message = '';
$messageType = '';
$result = null;

// Get user's LLM configs
$userId = 1; // Test user
$llmConfigs = $db->fetchAll(
    "SELECT * FROM llm_configs WHERE user_id = ? AND enabled = 1",
    [$userId]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider = $_POST['provider'] ?? '';
    $prompt = $_POST['prompt'] ?? '';
    $model = $_POST['model'] ?? '';
    
    if (empty($provider) || empty($prompt)) {
        $message = "Please select a provider and enter a prompt.";
        $messageType = 'error';
    } else {
        try {
            $requestOptions = [
                'max_tokens' => 500
            ];
            
            // Always ensure model is set (will use default if empty)
            if (!empty($model)) {
                $requestOptions['model'] = $model;
            }
            // If model is empty, makeRequest will use default from provider config or database
            
            $result = $llmBridge->makeRequest($userId, $provider, $prompt, $requestOptions);
            $message = "LLM request successful!";
            $messageType = 'success';
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Check if it's an API key error
            if (strpos($errorMsg, 'API key') !== false || 
                strpos($errorMsg, 'invalid') !== false || 
                strpos($errorMsg, 'Test API key') !== false) {
                $message = "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 15px;'>" .
                          "<strong>⚠️ API Key Issue:</strong><br>" . htmlspecialchars($errorMsg) . 
                          "</div>" .
                          "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;'>" .
                          "<strong>✅ Solution:</strong><br>" .
                          "1. <a href='llm_config.php' style='color: #667eea; font-weight: 600; text-decoration: underline;'>Go to LLM Configuration</a><br>" .
                          "2. Update the API key for " . ucfirst($provider) . "<br>" .
                          "3. Get your API key from: ";
                
                $keyLinks = [
                    'openai' => 'https://platform.openai.com/api-keys',
                    'anthropic' => 'https://console.anthropic.com/',
                    'google' => 'https://makersuite.google.com/app/apikey',
                    'deepseek' => 'https://platform.deepseek.com/',
                    'ollama' => '(No key needed - use "local")'
                ];
                
                if (isset($keyLinks[$provider])) {
                    if ($provider === 'ollama') {
                        $message .= $keyLinks[$provider];
                    } else {
                        $message .= "<a href='{$keyLinks[$provider]}' target='_blank' style='color: #667eea;'>{$keyLinks[$provider]}</a>";
                    }
                }
                
                $message .= "</div>" .
                          "<div style='background: #f0f4ff; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea; margin-top: 15px;'>" .
                          "<strong>💡 Quick Fix:</strong> " .
                          "<a href='cleanup_test_keys.php' style='color: #667eea; font-weight: 600; text-decoration: underline;'>Cleanup Test Keys</a> " .
                          "to automatically remove or disable invalid keys." .
                          "</div>";
            } else {
                $message = "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;'>" .
                          "<strong>Error:</strong> " . htmlspecialchars($errorMsg) .
                          "</div>";
            }
            $messageType = 'error';
        }
    }
}

// Get available providers
$availableProviders = $llmBridge->getAvailableProviders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 3: LLM Test - Cursoft</title>
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .result-box {
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .result-box h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .result-text {
            background: white;
            padding: 15px;
            border-radius: 6px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            max-height: 400px;
            overflow-y: auto;
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
            <h1>🤖 Cursoft - Phase 3</h1>
            <p>LLM Bridge Test Interface</p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <h4>ℹ️ Setup Required</h4>
                <p>To test LLM providers, you need to:</p>
                <ol style="margin-left: 20px; margin-top: 10px;">
                    <li>Add API keys in the LLM Config page</li>
                    <li>Ensure the provider is enabled</li>
                    <li>Select a provider and model below</li>
                </ol>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>LLM Provider:</label>
                    <select name="provider" required>
                        <option value="">Select a provider...</option>
                        <?php foreach ($availableProviders as $provider): ?>
                            <option value="<?php echo $provider; ?>">
                                <?php echo ucfirst($provider); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Model (optional, uses default if empty):</label>
                    <input type="text" name="model" placeholder="e.g., gpt-3.5-turbo">
                </div>
                
                <div class="form-group">
                    <label>Prompt:</label>
                    <textarea name="prompt" placeholder="Enter your prompt here..." required>Write a simple PHP function that calculates the factorial of a number.</textarea>
                </div>
                
                <button type="submit" class="btn">🚀 Send Request</button>
            </form>
            
            <?php if ($result): ?>
                <div class="result-box">
                    <h3>Response:</h3>
                    <div class="result-text"><?php echo htmlspecialchars($result['text']); ?></div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                        <strong>Tokens:</strong> Input: <?php echo $result['tokens']['input']; ?>, 
                        Output: <?php echo $result['tokens']['output']; ?>, 
                        Total: <?php echo $result['tokens']['total']; ?><br>
                        <strong>Cost:</strong> $<?php echo number_format($result['cost'], 6); ?><br>
                        <strong>Provider:</strong> <?php echo ucfirst($result['provider']); ?><br>
                        <strong>Model:</strong> <?php echo $result['model']; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

