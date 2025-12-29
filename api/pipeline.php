<?php
/**
 * Pipeline API Endpoint
 * Handles development pipeline operations
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/DevelopmentPipeline.php';

$method = $_SERVER['REQUEST_METHOD'];
$pipeline = new DevelopmentPipeline();

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['project_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'project_id is required']);
                exit;
            }
            
            if (isset($data['action']) && $data['action'] === 'start') {
                // Start pipeline
                $result = $pipeline->startPipeline($data['project_id'], [
                    'user_id' => $data['user_id'] ?? 1,
                    'llm_provider' => $data['llm_provider'] ?? 'openai'
                ]);
                
                echo json_encode($result, JSON_PRETTY_PRINT);
            } elseif (isset($data['action']) && $data['action'] === 'execute_stage') {
                // Execute specific stage
                if (!isset($data['stage'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'stage is required']);
                    exit;
                }
                
                $result = $pipeline->executeStage($data['project_id'], $data['stage'], [
                    'user_id' => $data['user_id'] ?? 1,
                    'llm_provider' => $data['llm_provider'] ?? 'openai'
                ]);
                
                echo json_encode($result, JSON_PRETTY_PRINT);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action. Use: start or execute_stage']);
            }
            break;
            
        case 'GET':
            if (!isset($_GET['project_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'project_id is required']);
                exit;
            }
            
            $status = $pipeline->getPipelineStatus($_GET['project_id']);
            echo json_encode($status, JSON_PRETTY_PRINT);
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

