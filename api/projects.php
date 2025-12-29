<?php
/**
 * Projects API Endpoint
 * Handles project creation and retrieval
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/ProjectPlanner.php';

$method = $_SERVER['REQUEST_METHOD'];
$planner = new ProjectPlanner();

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['name']) || !isset($data['prompt'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: user_id, name, prompt']);
            exit;
        }
        
        $projectId = $planner->createProject(
            $data['user_id'],
            $data['name'],
            $data['prompt']
        );
        
        $project = $planner->getProject($projectId);
        
        echo json_encode([
            'success' => true,
            'project_id' => $projectId,
            'project' => $project
        ], JSON_PRETTY_PRINT);
        break;
        
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific project
            $project = $planner->getProject($_GET['id']);
            
            if (!$project) {
                http_response_code(404);
                echo json_encode(['error' => 'Project not found']);
                exit;
            }
            
            echo json_encode($project, JSON_PRETTY_PRINT);
        } elseif (isset($_GET['user_id'])) {
            // Get all projects for a user
            $projects = $planner->getUserProjects($_GET['user_id']);
            echo json_encode($projects, JSON_PRETTY_PRINT);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Project ID or user_id required']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>

