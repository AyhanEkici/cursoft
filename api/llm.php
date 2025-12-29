<?php
/**
 * LLM API Endpoint
 * Handles LLM provider requests
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/LLMBridge.php';

$method = $_SERVER['REQUEST_METHOD'];
$llmBridge = new LLMBridge();

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['user_id']) || !isset($data['provider']) || !isset($data['prompt'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields: user_id, provider, prompt']);
                exit;
            }
            
            $response = $llmBridge->makeRequest(
                $data['user_id'],
                $data['provider'],
                $data['prompt'],
                [
                    'project_id' => $data['project_id'] ?? null,
                    'model' => $data['model'] ?? null,
                    'temperature' => $data['temperature'] ?? 0.7,
                    'max_tokens' => $data['max_tokens'] ?? 1000
                ]
            );
            
            echo json_encode([
                'success' => true,
                'response' => $response
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'GET':
            if (isset($_GET['providers'])) {
                // Get available providers
                $providers = $llmBridge->getAvailableProviders();
                echo json_encode(['providers' => $providers], JSON_PRETTY_PRINT);
            } elseif (isset($_GET['models']) && isset($_GET['provider'])) {
                // Get models for provider
                $models = $llmBridge->getModels($_GET['provider']);
                echo json_encode(['models' => $models], JSON_PRETTY_PRINT);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request. Use ?providers or ?models&provider=xxx']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>

