<?php
/**
 * LLM Bridge Class
 * Handles integration with multiple LLM providers
 */

require_once __DIR__ . '/Database.php';

class LLMBridge {
    private $db;
    private $providers = [
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'models' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'default_model' => 'gpt-3.5-turbo'
        ],
        'anthropic' => [
            'base_url' => 'https://api.anthropic.com/v1',
            'models' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'],
            'default_model' => 'claude-3-haiku'
        ],
        'google' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1',
            'models' => ['gemini-pro', 'gemini-pro-vision'],
            'default_model' => 'gemini-pro'
        ],
        'deepseek' => [
            'base_url' => 'https://api.deepseek.com/v1',
            'models' => ['deepseek-chat', 'deepseek-coder'],
            'default_model' => 'deepseek-chat'
        ],
        'ollama' => [
            'base_url' => 'http://localhost:11434/api',
            'models' => ['llama2', 'codellama', 'mistral'],
            'default_model' => 'llama2'
        ]
    ];
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get API key for a provider from database
     */
    private function getApiKey($userId, $provider) {
        $config = $this->db->fetchOne(
            "SELECT api_key FROM llm_configs WHERE user_id = ? AND provider = ? AND enabled = 1",
            [$userId, $provider]
        );
        
        $apiKey = $config['api_key'] ?? null;
        
        // Validate API key format
        if ($apiKey && $this->isTestKey($apiKey)) {
            throw new Exception("Test API key detected. Please replace it with a real API key in the LLM Configuration page.");
        }
        
        return $apiKey;
    }
    
    /**
     * Check if API key is a test/placeholder key
     */
    private function isTestKey($apiKey) {
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
        
        // Check for obviously invalid keys
        if (strlen($apiKey) < 10) {
            return true;
        }
        
        // Ollama can use 'local' as a valid key
        if ($apiKey === 'local') {
            return false;
        }
        
        return false;
    }
    
    /**
     * Make request to LLM provider
     */
    public function makeRequest($userId, $provider, $prompt, $options = []) {
        $projectId = $options['project_id'] ?? null;
        
        // Get model - check options first, then database config, then default
        $model = null;
        if (!empty($options['model'])) {
            $model = $options['model'];
        } else {
            // Try to get from database config
            $config = $this->db->fetchOne(
                "SELECT model FROM llm_configs WHERE user_id = ? AND provider = ? AND enabled = 1",
                [$userId, $provider]
            );
            if (!empty($config['model'])) {
                $model = $config['model'];
            }
        }
        
        // Use default if still no model
        if (empty($model)) {
            $model = $this->providers[$provider]['default_model'] ?? 'gpt-3.5-turbo';
        }
        
        // Validate model is not empty
        if (empty($model)) {
            throw new Exception("Model parameter is required for provider: {$provider}");
        }
        
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 1000;
        
        // Get API key
        $apiKey = $this->getApiKey($userId, $provider);
        if (!$apiKey) {
            throw new Exception("API key not found for provider: {$provider}");
        }
        
        // Final validation - ensure model is never empty
        if (empty($model) || trim($model) === '') {
            // Last resort: use hardcoded default based on provider
            $defaultModels = [
                'openai' => 'gpt-3.5-turbo',
                'anthropic' => 'claude-3-haiku',
                'google' => 'gemini-pro',
                'deepseek' => 'deepseek-chat',
                'ollama' => 'llama2'
            ];
            $model = $defaultModels[$provider] ?? 'gpt-3.5-turbo';
        }
        
        // Log request
        $requestId = $this->logRequest($projectId, $provider, $model, $prompt);
        
        try {
            $response = $this->callProvider($provider, $apiKey, $model, $prompt, $temperature, $maxTokens);
            
            // Extract response data
            $responseText = $response['text'] ?? '';
            $tokensInput = $response['tokens_input'] ?? 0;
            $tokensOutput = $response['tokens_output'] ?? 0;
            $tokensTotal = $tokensInput + $tokensOutput;
            $cost = $this->calculateCost($provider, $model, $tokensInput, $tokensOutput);
            
            // Update request log
            $this->updateRequest($requestId, 'completed', $responseText, $tokensInput, $tokensOutput, $tokensTotal, $cost);
            
            return [
                'request_id' => $requestId,
                'text' => $responseText,
                'tokens' => [
                    'input' => $tokensInput,
                    'output' => $tokensOutput,
                    'total' => $tokensTotal
                ],
                'cost' => $cost,
                'provider' => $provider,
                'model' => $model
            ];
        } catch (Exception $e) {
            $this->updateRequest($requestId, 'failed', null, 0, 0, 0, 0, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Call specific LLM provider
     */
    private function callProvider($provider, $apiKey, $model, $prompt, $temperature, $maxTokens) {
        switch ($provider) {
            case 'openai':
                return $this->callOpenAI($apiKey, $model, $prompt, $temperature, $maxTokens);
            case 'anthropic':
                return $this->callAnthropic($apiKey, $model, $prompt, $temperature, $maxTokens);
            case 'google':
                return $this->callGoogle($apiKey, $model, $prompt, $temperature, $maxTokens);
            case 'deepseek':
                return $this->callDeepSeek($apiKey, $model, $prompt, $temperature, $maxTokens);
            case 'ollama':
                return $this->callOllama($apiKey, $model, $prompt, $temperature, $maxTokens);
            default:
                throw new Exception("Unsupported provider: {$provider}");
        }
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($apiKey, $model, $prompt, $temperature, $maxTokens) {
        // Ensure model is set
        if (empty($model)) {
            $model = 'gpt-3.5-turbo'; // Default fallback
        }
        
        $url = $this->providers['openai']['base_url'] . '/chat/completions';
        
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];
        
        // Remove max_tokens if it's 0 or invalid
        if (empty($maxTokens) || $maxTokens <= 0) {
            unset($data['max_tokens']);
        }
        
        $response = $this->httpPost($url, $data, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        $text = $response['choices'][0]['message']['content'] ?? '';
        $tokensInput = $response['usage']['prompt_tokens'] ?? 0;
        $tokensOutput = $response['usage']['completion_tokens'] ?? 0;
        
        return [
            'text' => $text,
            'tokens_input' => $tokensInput,
            'tokens_output' => $tokensOutput
        ];
    }
    
    /**
     * Call Anthropic API
     */
    private function callAnthropic($apiKey, $model, $prompt, $temperature, $maxTokens) {
        $url = $this->providers['anthropic']['base_url'] . '/messages';
        
        $data = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];
        
        $response = $this->httpPost($url, $data, [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json'
        ]);
        
        $text = $response['content'][0]['text'] ?? '';
        $tokensInput = $response['usage']['input_tokens'] ?? 0;
        $tokensOutput = $response['usage']['output_tokens'] ?? 0;
        
        return [
            'text' => $text,
            'tokens_input' => $tokensInput,
            'tokens_output' => $tokensOutput
        ];
    }
    
    /**
     * Call Google API
     */
    private function callGoogle($apiKey, $model, $prompt, $temperature, $maxTokens) {
        $url = $this->providers['google']['base_url'] . '/' . $model . ':generateContent?key=' . $apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens
            ]
        ];
        
        $response = $this->httpPost($url, $data, [
            'Content-Type: application/json'
        ]);
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        // Google doesn't always return token counts in the same format
        $tokensInput = strlen($prompt) / 4; // Rough estimate
        $tokensOutput = strlen($text) / 4; // Rough estimate
        
        return [
            'text' => $text,
            'tokens_input' => (int)$tokensInput,
            'tokens_output' => (int)$tokensOutput
        ];
    }
    
    /**
     * Call DeepSeek API (similar to OpenAI)
     */
    private function callDeepSeek($apiKey, $model, $prompt, $temperature, $maxTokens) {
        $url = $this->providers['deepseek']['base_url'] . '/chat/completions';
        
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];
        
        $response = $this->httpPost($url, $data, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        $text = $response['choices'][0]['message']['content'] ?? '';
        $tokensInput = $response['usage']['prompt_tokens'] ?? 0;
        $tokensOutput = $response['usage']['completion_tokens'] ?? 0;
        
        return [
            'text' => $text,
            'tokens_input' => $tokensInput,
            'tokens_output' => $tokensOutput
        ];
    }
    
    /**
     * Call Ollama API (local)
     */
    private function callOllama($apiKey, $model, $prompt, $temperature, $maxTokens) {
        $url = $this->providers['ollama']['base_url'] . '/generate';
        
        $data = [
            'model' => $model,
            'prompt' => $prompt,
            'options' => [
                'temperature' => $temperature,
                'num_predict' => $maxTokens
            ]
        ];
        
        $response = $this->httpPost($url, $data, [
            'Content-Type: application/json'
        ]);
        
        $text = $response['response'] ?? '';
        $tokensInput = strlen($prompt) / 4; // Rough estimate for Ollama
        $tokensOutput = strlen($text) / 4;
        
        return [
            'text' => $text,
            'tokens_input' => (int)$tokensInput,
            'tokens_output' => (int)$tokensOutput
        ];
    }
    
    /**
     * HTTP POST request helper
     */
    private function httpPost($url, $data, $headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: {$error}");
        }
        
        if ($httpCode >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = "API error (HTTP {$httpCode})";
            
            // Extract more helpful error message
            if (isset($errorData['error']['message'])) {
                $errorMessage = $errorData['error']['message'];
                
                // Check for common errors
                if (strpos($errorMessage, 'API key') !== false || strpos($errorMessage, 'invalid_api_key') !== false) {
                    $errorMessage = "Invalid API key. Please check your API key in the LLM Configuration page. " . 
                                   "If you're using a test key, replace it with a real API key from the provider.";
                } elseif (strpos($errorMessage, 'insufficient_quota') !== false) {
                    $errorMessage = "API quota exceeded. Please check your account balance or upgrade your plan.";
                } elseif (strpos($errorMessage, 'rate_limit') !== false) {
                    $errorMessage = "Rate limit exceeded. Please wait a moment and try again.";
                }
            } else {
                $errorMessage = "API error (HTTP {$httpCode}): " . substr($response, 0, 200);
            }
            
            throw new Exception($errorMessage);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Calculate cost based on provider and tokens
     */
    private function calculateCost($provider, $model, $tokensInput, $tokensOutput) {
        // Cost per 1K tokens (approximate, update with current rates)
        $costs = [
            'openai' => [
                'gpt-4' => ['input' => 0.03, 'output' => 0.06],
                'gpt-4-turbo' => ['input' => 0.01, 'output' => 0.03],
                'gpt-3.5-turbo' => ['input' => 0.0015, 'output' => 0.002]
            ],
            'anthropic' => [
                'claude-3-opus' => ['input' => 0.015, 'output' => 0.075],
                'claude-3-sonnet' => ['input' => 0.003, 'output' => 0.015],
                'claude-3-haiku' => ['input' => 0.00025, 'output' => 0.00125]
            ],
            'deepseek' => [
                'deepseek-chat' => ['input' => 0.00014, 'output' => 0.00028],
                'deepseek-coder' => ['input' => 0.00014, 'output' => 0.00028]
            ],
            'google' => [
                'gemini-pro' => ['input' => 0.0005, 'output' => 0.0015]
            ],
            'ollama' => [
                'default' => ['input' => 0, 'output' => 0] // Free, local
            ]
        ];
        
        $providerCosts = $costs[$provider] ?? [];
        $modelCosts = $providerCosts[$model] ?? $providerCosts['default'] ?? ['input' => 0, 'output' => 0];
        
        $inputCost = ($tokensInput / 1000) * ($modelCosts['input'] ?? 0);
        $outputCost = ($tokensOutput / 1000) * ($modelCosts['output'] ?? 0);
        
        return $inputCost + $outputCost;
    }
    
    /**
     * Log LLM request
     */
    private function logRequest($projectId, $provider, $model, $prompt) {
        $this->db->query(
            "INSERT INTO llm_requests (project_id, provider, model, prompt, status) 
             VALUES (?, ?, ?, ?, 'pending')",
            [$projectId, $provider, $model, $prompt]
        );
        return $this->db->lastInsertId();
    }
    
    /**
     * Update LLM request
     */
    private function updateRequest($requestId, $status, $response, $tokensInput, $tokensOutput, $tokensTotal, $cost, $error = null) {
        $this->db->query(
            "UPDATE llm_requests SET 
             status = ?, 
             response = ?, 
             tokens_input = ?, 
             tokens_output = ?, 
             tokens_total = ?, 
             cost_usd = ?, 
             completed_at = NOW(),
             error_message = ?
             WHERE id = ?",
            [$status, $response, $tokensInput, $tokensOutput, $tokensTotal, $cost, $error, $requestId]
        );
    }
    
    /**
     * Get available providers
     */
    public function getAvailableProviders() {
        return array_keys($this->providers);
    }
    
    /**
     * Get models for a provider
     */
    public function getModels($provider) {
        return $this->providers[$provider]['models'] ?? [];
    }
}
?>

