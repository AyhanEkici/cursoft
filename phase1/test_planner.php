<?php
/**
 * Phase 1: Project Planner Test Interface
 * Simple web interface to test the project planner functionality
 */

require_once __DIR__ . '/../includes/ProjectPlanner.php';

// For testing, use user ID 1 (test user from schema.sql)
$testUserId = 1;
$planner = new ProjectPlanner();
$message = '';
$project = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Test Project';
    $prompt = $_POST['prompt'] ?? '';
    
    if (!empty($prompt)) {
        try {
            $projectId = $planner->createProject($testUserId, $name, $prompt);
            $project = $planner->getProject($projectId);
            $message = "Project created successfully! ID: {$projectId}";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Please enter a project prompt.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 1: Project Planner Test - Cursoft</title>
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
            max-width: 1000px;
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
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
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
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
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
        
        .project-result {
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .project-result h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        
        .project-result h3 {
            color: #333;
            margin: 20px 0 10px 0;
            font-size: 1.3em;
        }
        
        .tasks-list {
            list-style: none;
            margin: 15px 0;
        }
        
        .tasks-list li {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 3px solid #667eea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .tasks-list li strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
            font-size: 1.1em;
        }
        
        .logs-list {
            list-style: none;
            margin: 15px 0;
        }
        
        .logs-list li {
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .logs-list li[data-level="info"] {
            background: #e7f3ff;
            color: #004085;
        }
        
        .logs-list li[data-level="success"] {
            background: #d4edda;
            color: #155724;
        }
        
        .logs-list li[data-level="warning"] {
            background: #fff3cd;
            color: #856404;
        }
        
        .logs-list li[data-level="error"] {
            background: #f8d7da;
            color: #721c24;
        }
        
        .example-prompts {
            margin-top: 20px;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 8px;
        }
        
        .example-prompts h4 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .example-btn {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.2s;
        }
        
        .example-btn:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Cursoft - Phase 1</h1>
            <p>Project Planner Test Interface</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="projectForm">
                <div class="form-group">
                    <label for="name">Project Name:</label>
                    <input type="text" id="name" name="name" placeholder="My Awesome Project" required>
                </div>
                
                <div class="form-group">
                    <label for="prompt">Project Prompt:</label>
                    <textarea id="prompt" name="prompt" placeholder="Describe your project in detail...&#10;&#10;Example: Build a todo web app with user authentication, MySQL database, and REST API" required></textarea>
                </div>
                
                <div class="example-prompts">
                    <h4>💡 Try these example prompts:</h4>
                    <button type="button" class="example-btn" onclick="fillPrompt('Build a todo web app with user authentication and MySQL database')">Todo App</button>
                    <button type="button" class="example-btn" onclick="fillPrompt('Create a REST API for a blog system with user posts and comments')">Blog API</button>
                    <button type="button" class="example-btn" onclick="fillPrompt('Build a simple e-commerce website with product catalog and shopping cart')">E-commerce</button>
                    <button type="button" class="example-btn" onclick="fillPrompt('Create a user management system with registration, login, and profile pages')">User System</button>
                </div>
                
                <button type="submit" class="btn">✨ Create Project Plan</button>
            </form>
            
            <?php if ($project): ?>
                <div class="project-result">
                    <h2>✅ Project Created Successfully!</h2>
                    <h3>Project: <?php echo htmlspecialchars($project['name']); ?></h3>
                    <p><strong>Status:</strong> <?php echo ucfirst($project['status']); ?></p>
                    <p><strong>Prompt:</strong> <?php echo htmlspecialchars($project['prompt']); ?></p>
                    
                    <h3>📋 Development Tasks:</h3>
                    <ul class="tasks-list">
                        <?php foreach ($project['plan'] as $task): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($task['task_name']); ?></strong>
                                <?php echo htmlspecialchars($task['task_description']); ?>
                                <br>
                                <small style="color: #666;">⏱️ Estimated time: <?php echo $task['estimated_time']; ?> minutes</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h3>📝 Activity Logs:</h3>
                    <ul class="logs-list">
                        <?php foreach ($project['logs'] as $log): ?>
                            <li data-level="<?php echo htmlspecialchars($log['log_level']); ?>">
                                [<?php echo strtoupper($log['log_level']); ?>] <?php echo htmlspecialchars($log['message']); ?>
                                <small style="opacity: 0.7;"> - <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function fillPrompt(text) {
            document.getElementById('prompt').value = text;
        }
    </script>
</body>
</html>

