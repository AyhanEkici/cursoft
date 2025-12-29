<?php
/**
 * New Project Page
 * Create a new project with prompt
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/ProjectPlanner.php';
require_once __DIR__ . '/../includes/Security.php';

$sessionManager = new SessionManager();
$sessionManager->requireLogin();

$security = new Security();
$csrfToken = $security->generateCSRFToken();

$user = $sessionManager->getCurrentUser();
$planner = new ProjectPlanner();

$message = '';
$messageType = '';
$projectId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!$security->verifyCSRFToken($token)) {
        $message = "Invalid security token. Please try again.";
        $messageType = 'error';
        $security->logSecurityEvent('csrf_token_invalid', ['page' => 'new_project', 'user_id' => $user['id']]);
    } else {
        // Rate limiting
        if (!$security->checkRateLimit('create_project', 10, 3600)) { // 10 projects per hour
            $message = "Too many project creation attempts. Please try again later.";
            $messageType = 'error';
        } else {
            $name = $security->sanitizeInput($_POST['name'] ?? '', 'string');
            $prompt = $security->sanitizeInput($_POST['prompt'] ?? '', 'string');
            
            if (empty($name) || empty($prompt)) {
        $message = "Project name and prompt are required.";
        $messageType = 'error';
    } elseif (strlen($prompt) < 20) {
        $message = "Prompt must be at least 20 characters.";
        $messageType = 'error';
    } else {
        try {
            $projectId = $planner->createProject($user['id'], $name, $prompt);
            $message = "Project created successfully!";
            $messageType = 'success';
            
            // Redirect after 2 seconds
            header("Refresh: 2; url=project_detail.php?id={$projectId}");
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Project - Cursoft</title>
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
        <h1 style="margin-bottom: 30px;">Create New Project</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($messageType === 'success' && $projectId): ?>
                    <br>Redirecting to project details...
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Project Details</h2>
            </div>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label for="name">Project Name:</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="My Awesome Project" required>
                </div>
                
                <div class="form-group">
                    <label for="prompt">Project Prompt:</label>
                    <textarea id="prompt" name="prompt" class="form-control" 
                              placeholder="Describe your project in detail...&#10;&#10;Example: Build a todo web app with user authentication, MySQL database, and REST API" 
                              required minlength="20" rows="10"></textarea>
                    <small style="color: #666;">Minimum 20 characters. Be as detailed as possible.</small>
                </div>
                
                <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="color: #667eea; margin-bottom: 10px;">💡 Example Prompts:</h4>
                    <ul style="margin-left: 20px; color: #666;">
                        <li>"Build a todo web app with user authentication and MySQL database"</li>
                        <li>"Create a REST API for a blog system with posts and comments"</li>
                        <li>"Build an e-commerce website with product catalog and shopping cart"</li>
                        <li>"Create a user management system with registration, login, and profile pages"</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-primary">✨ Create Project</button>
                <a href="dashboard.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>

