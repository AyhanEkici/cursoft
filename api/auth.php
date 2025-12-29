<?php
/**
 * Authentication API Endpoint
 * Handles login, signup, logout
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Security.php';

$method = $_SERVER['REQUEST_METHOD'];
$security = new Security();
$auth = new Auth();

// Rate limiting for auth endpoints
$action = json_decode(file_get_contents('php://input'), true)['action'] ?? '';
$rateLimitKey = 'api_auth_' . $action;
if (!$security->checkRateLimit($rateLimitKey, 10, 300)) { // 10 requests per 5 minutes
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.'], JSON_PRETTY_PRINT);
    exit;
}

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? '';
            
            switch ($action) {
                case 'login':
                    if (!isset($data['email']) || !isset($data['password'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Email and password are required']);
                        exit;
                    }
                    
                    $user = $auth->login(
                        $data['email'],
                        $data['password'],
                        $data['remember_me'] ?? false
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'name' => $user['name']
                        ]
                    ], JSON_PRETTY_PRINT);
                    break;
                    
                case 'signup':
                    if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Email, password, and name are required']);
                        exit;
                    }
                    
                    $userId = $auth->register(
                        $data['email'],
                        $data['password'],
                        $data['name']
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'user_id' => $userId,
                        'message' => 'Account created successfully'
                    ], JSON_PRETTY_PRINT);
                    break;
                    
                case 'logout':
                    $auth->logout();
                    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        case 'GET':
            if (isset($_GET['check'])) {
                // Check if user is logged in
                $user = $auth->getCurrentUser();
                if ($user) {
                    echo json_encode(['logged_in' => true, 'user' => $user], JSON_PRETTY_PRINT);
                } else {
                    echo json_encode(['logged_in' => false], JSON_PRETTY_PRINT);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
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

