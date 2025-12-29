<?php
/**
 * Phase 3: Development Pipeline Demo
 * Demonstrate the complete development pipeline
 */

require_once __DIR__ . '/../includes/DevelopmentPipeline.php';
require_once __DIR__ . '/../includes/Database.php';

$pipeline = new DevelopmentPipeline();
$db = new Database();

$message = '';
$messageType = '';
$pipelineStatus = null;

// Get projects
$projects = $db->fetchAll("SELECT id, name FROM projects ORDER BY created_at DESC LIMIT 10");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $projectId = $_POST['project_id'] ?? null;
    
    if (!$projectId) {
        $message = "Please select a project.";
        $messageType = 'error';
    } else {
        try {
            switch ($action) {
                case 'start':
                    $result = $pipeline->startPipeline($projectId, [
                        'user_id' => 1,
                        'llm_provider' => 'openai'
                    ]);
                    $message = "Pipeline started successfully!";
                    $messageType = 'success';
                    break;
                    
                case 'status':
                    $pipelineStatus = $pipeline->getPipelineStatus($projectId);
                    $message = "Pipeline status retrieved.";
                    $messageType = 'success';
                    break;
                    
                case 'execute_stage':
                    $stage = $_POST['stage'] ?? '';
                    if ($stage) {
                        $result = $pipeline->executeStage($projectId, $stage, [
                            'user_id' => 1,
                            'llm_provider' => 'openai'
                        ]);
                        $message = "Stage '{$stage}' executed successfully!";
                        $messageType = 'success';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 3: Pipeline Demo - Cursoft</title>
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
            max-width: 1400px;
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
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        select, button {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
        }
        
        select {
            width: 100%;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .pipeline-visual {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stage-card {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            text-align: center;
        }
        
        .stage-card.pending {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .stage-card.running {
            background: #fff3cd;
            border-color: #ffc107;
            animation: pulse 2s infinite;
        }
        
        .stage-card.completed {
            background: #d4edda;
            border-color: #28a745;
        }
        
        .stage-card.failed {
            background: #f8d7da;
            border-color: #dc3545;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .stage-name {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .stage-status {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Cursoft - Phase 3</h1>
            <p>Development Pipeline Demo</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
                <input type="hidden" name="action" value="start">
                <div class="form-group">
                    <label>Select Project:</label>
                    <select name="project_id" required>
                        <option value="">Choose a project...</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>">
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">🚀 Start Pipeline</button>
            </form>
            
            <?php if (!empty($projects)): ?>
                <form method="POST" style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
                    <input type="hidden" name="action" value="status">
                    <div class="form-group">
                        <label>Check Pipeline Status:</label>
                        <select name="project_id" required>
                            <option value="">Choose a project...</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>">
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">📊 Get Status</button>
                </form>
            <?php endif; ?>
            
            <?php if ($pipelineStatus): ?>
                <div style="margin-top: 30px;">
                    <h2>Pipeline Status</h2>
                    <div class="pipeline-visual">
                        <?php foreach ($pipelineStatus['stages'] as $stage): ?>
                            <div class="stage-card <?php echo $stage['status']; ?>">
                                <div class="stage-name"><?php echo ucfirst($stage['stage_name']); ?></div>
                                <div class="stage-status"><?php echo ucfirst($stage['status']); ?></div>
                                <?php if ($stage['started_at']): ?>
                                    <div style="font-size: 0.8em; margin-top: 5px; color: #666;">
                                        Started: <?php echo date('H:i:s', strtotime($stage['started_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 8px;">
                        <strong>Project Progress:</strong> <?php echo $pipelineStatus['project']['progress']; ?>%<br>
                        <strong>Status:</strong> <?php echo ucfirst($pipelineStatus['project']['status']); ?><br>
                        <strong>Current Stage:</strong> <?php echo ucfirst($pipelineStatus['current_stage'] ?? 'N/A'); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

