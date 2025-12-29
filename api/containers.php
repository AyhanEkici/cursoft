<?php
/**
 * Containers API Endpoint
 * Handles container operations via REST API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/ContainerManager.php';
require_once __DIR__ . '/../includes/ContainerOrchestrator.php';

$method = $_SERVER['REQUEST_METHOD'];
$containerManager = new ContainerManager();
$orchestrator = new ContainerOrchestrator();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get specific container
                $container = $containerManager->getContainer($_GET['id']);
                if (!$container) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Container not found']);
                    exit;
                }
                
                // Get real-time status
                $container['docker_status'] = $containerManager->getContainerStatus($_GET['id']);
                $container['logs'] = $containerManager->getContainerLogsFromDb($_GET['id'], 50);
                
                echo json_encode($container, JSON_PRETTY_PRINT);
            } elseif (isset($_GET['project_id'])) {
                // Get containers for a project
                $containers = $containerManager->getProjectContainers($_GET['project_id']);
                echo json_encode($containers, JSON_PRETTY_PRINT);
            } else {
                // Get all containers
                $containers = $containerManager->getAllContainers();
                echo json_encode($containers, JSON_PRETTY_PRINT);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['project_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'project_id is required']);
                exit;
            }
            
            // Create container for project
            $container = $orchestrator->createProjectContainer(
                $data['project_id'],
                [
                    'image' => $data['image'] ?? 'cursoft-dev:latest',
                    'port_mapping' => $data['port_mapping'] ?? null,
                    'env_vars' => $data['env_vars'] ?? [],
                    'auto_start' => $data['auto_start'] ?? false
                ]
            );
            
            echo json_encode([
                'success' => true,
                'container' => $container
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['action'])) {
                http_response_code(400);
                echo json_encode(['error' => 'id and action are required']);
                exit;
            }
            
            $action = $data['action'];
            $containerId = $data['id'];
            
            switch ($action) {
                case 'start':
                    $containerManager->startContainer($containerId);
                    echo json_encode(['success' => true, 'message' => 'Container started']);
                    break;
                    
                case 'stop':
                    $containerManager->stopContainer($containerId);
                    echo json_encode(['success' => true, 'message' => 'Container stopped']);
                    break;
                    
                case 'restart':
                    $containerManager->restartContainer($containerId);
                    echo json_encode(['success' => true, 'message' => 'Container restarted']);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action. Use: start, stop, or restart']);
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Container ID required']);
                exit;
            }
            
            $containerManager->removeContainer($_GET['id']);
            echo json_encode(['success' => true, 'message' => 'Container removed']);
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

