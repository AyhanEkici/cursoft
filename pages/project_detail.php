<?php
/**
 * Project Detail Page
 * Shows project details with real-time updates
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/ProjectPlanner.php';
require_once __DIR__ . '/../includes/DevelopmentPipeline.php';
require_once __DIR__ . '/../includes/ContainerOrchestrator.php';

$sessionManager = new SessionManager();
$sessionManager->requireLogin();

$user = $sessionManager->getCurrentUser();
$planner = new ProjectPlanner();
$pipeline = new DevelopmentPipeline();
$orchestrator = new ContainerOrchestrator();

$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    header('Location: dashboard.php');
    exit;
}

// Get project
$project = $planner->getProject($projectId);

if (!$project || $project['user_id'] != $user['id']) {
    header('Location: dashboard.php');
    exit;
}

// Get pipeline status
$pipelineStatus = $pipeline->getPipelineStatus($projectId);

// Get container status
$containerStatus = null;
try {
    $containerStatus = $orchestrator->getProjectContainerStatus($projectId);
} catch (Exception $e) {
    // Container might not exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['name']); ?> - Cursoft</title>
    <link rel="stylesheet" href="/cursoft/public/css/main.css">
    <script src="/cursoft/public/js/api.js"></script>
    <style>
        .pipeline-stages {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }
        
        .stage-card {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            text-align: center;
            transition: all 0.3s;
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
        
        .logs-container {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .log-entry {
            margin-bottom: 5px;
        }
        
        .log-info { color: #569cd6; }
        .log-success { color: #4ec9b0; }
        .log-warning { color: #dcdcaa; }
        .log-error { color: #f48771; }
    </style>
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
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="color: #667eea; text-decoration: none;">← Back to Dashboard</a>
        </div>
        
        <h1 style="margin-bottom: 10px;"><?php echo htmlspecialchars($project['name']); ?></h1>
        <p style="color: #666; margin-bottom: 30px;"><?php echo htmlspecialchars($project['prompt']); ?></p>
        
        <!-- Project Status -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="row">
                <div class="col">
                    <strong>Status:</strong>
                    <span class="badge badge-<?php 
                        echo $project['status'] === 'completed' ? 'success' : 
                            ($project['status'] === 'active' ? 'info' : 'warning'); 
                    ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </div>
                <div class="col">
                    <strong>Progress:</strong>
                    <div class="progress" style="margin-top: 10px;">
                        <div class="progress-bar" style="width: <?php echo $project['progress']; ?>%;">
                            <?php echo $project['progress']; ?>%
                        </div>
                    </div>
                </div>
                <div class="col">
                    <strong>Created:</strong><br>
                    <?php echo date('M d, Y H:i', strtotime($project['created_at'])); ?>
                </div>
            </div>
        </div>
        
        <!-- Pipeline Stages -->
        <?php if (!empty($pipelineStatus['stages'])): ?>
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2 class="card-title">Development Pipeline</h2>
                </div>
                <div class="pipeline-stages">
                    <?php foreach ($pipelineStatus['stages'] as $stage): ?>
                        <div class="stage-card <?php echo $stage['status']; ?>">
                            <div style="font-weight: 600; margin-bottom: 10px;">
                                <?php echo ucfirst(str_replace('_', ' ', $stage['stage_name'])); ?>
                            </div>
                            <div style="font-size: 0.9em; color: #666;">
                                <?php echo ucfirst($stage['status']); ?>
                            </div>
                            <?php if ($stage['started_at']): ?>
                                <div style="font-size: 0.8em; color: #666; margin-top: 5px;">
                                    <?php echo date('H:i:s', strtotime($stage['started_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="margin-bottom: 30px;">
                <p style="color: #666; margin-bottom: 15px;">Pipeline not started yet.</p>
                <button onclick="startPipeline()" class="btn btn-primary">Start Pipeline</button>
            </div>
        <?php endif; ?>
        
        <!-- Project Plan -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Development Tasks</h2>
            </div>
            <?php if (!empty($project['plan'])): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Description</th>
                            <th>Estimated Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($project['plan'] as $task): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($task['task_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                                <td><?php echo $task['estimated_time']; ?> min</td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $task['status'] === 'completed' ? 'success' : 
                                            ($task['status'] === 'in_progress' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666;">No tasks defined yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Project Logs -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Activity Logs</h2>
            </div>
            <div class="logs-container">
                <?php if (!empty($project['logs'])): ?>
                    <?php foreach (array_reverse($project['logs']) as $log): ?>
                        <div class="log-entry log-<?php echo $log['log_level']; ?>">
                            [<?php echo strtoupper($log['log_level']); ?>] 
                            <?php echo htmlspecialchars($log['message']); ?>
                            <span style="color: #808080;">
                                - <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color: #808080;">No logs yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function startPipeline() {
            if (confirm('Start the development pipeline for this project?')) {
                fetch('/cursoft/api/pipeline.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        project_id: <?php echo $projectId; ?>,
                        action: 'start',
                        user_id: <?php echo $user['id']; ?>
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to start pipeline'));
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
            }
        }
        
        // Auto-refresh every 5 seconds if pipeline is active
        <?php if ($project['status'] === 'active'): ?>
        setInterval(() => {
            location.reload();
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>

