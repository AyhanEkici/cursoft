<?php
/**
 * Phase 2: Container Manager Web Interface
 * Manage Docker containers for projects
 */

require_once __DIR__ . '/../includes/ContainerManager.php';
require_once __DIR__ . '/../includes/ContainerOrchestrator.php';
require_once __DIR__ . '/../includes/Database.php';

$containerManager = new ContainerManager();
$orchestrator = new ContainerOrchestrator();
$db = new Database();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $containerId = $_POST['container_id'] ?? null;
    $projectId = $_POST['project_id'] ?? null;
    
    try {
        switch ($action) {
            case 'create':
                if ($projectId) {
                    $container = $orchestrator->createProjectContainer($projectId, [
                        'auto_start' => isset($_POST['auto_start'])
                    ]);
                    $message = "Container created successfully!";
                    $messageType = 'success';
                }
                break;
                
            case 'start':
                if ($containerId) {
                    $containerManager->startContainer($containerId);
                    $message = "Container started!";
                    $messageType = 'success';
                }
                break;
                
            case 'stop':
                if ($containerId) {
                    $containerManager->stopContainer($containerId);
                    $message = "Container stopped!";
                    $messageType = 'success';
                }
                break;
                
            case 'restart':
                if ($containerId) {
                    $containerManager->restartContainer($containerId);
                    $message = "Container restarted!";
                    $messageType = 'success';
                }
                break;
                
            case 'remove':
                if ($containerId) {
                    $containerManager->removeContainer($containerId);
                    $message = "Container removed!";
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all containers
$containers = $containerManager->getAllContainers();

// Get all projects for dropdown
$projects = $db->fetchAll("SELECT id, name FROM projects ORDER BY created_at DESC");

// Check Docker availability
$dockerAvailable = $containerManager->isDockerAvailable();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 2: Container Manager - Cursoft</title>
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
        
        .docker-status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .docker-status.available {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .docker-status.unavailable {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        
        .create-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        select, input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 0.9em;
            margin: 2px;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-running {
            background: #d4edda;
            color: #155724;
        }
        
        .status-stopped {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-created {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐳 Cursoft - Phase 2</h1>
            <p>Container Manager</p>
        </div>
        
        <div class="content">
            <div class="docker-status <?php echo $dockerAvailable ? 'available' : 'unavailable'; ?>">
                <?php if ($dockerAvailable): ?>
                    ✅ Docker is available and running
                <?php else: ?>
                    ❌ Docker is not available. Please ensure Docker Desktop is running.
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="create-form">
                <h2>Create New Container</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label>Project:</label>
                        <select name="project_id" required>
                            <option value="">Select a project...</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>">
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="auto_start" checked>
                            Auto-start container after creation
                        </label>
                    </div>
                    <button type="submit" class="btn">Create Container</button>
                </form>
            </div>
            
            <h2>All Containers</h2>
            <?php if (empty($containers)): ?>
                <p>No containers found. Create one above!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Container Name</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Port Mapping</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($containers as $container): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($container['container_name']); ?></td>
                                <td><?php echo htmlspecialchars($container['project_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $container['status']; ?>">
                                        <?php echo ucfirst($container['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($container['port_mapping'] ?? 'N/A'); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($container['created_at'])); ?></td>
                                <td class="actions">
                                    <?php if ($container['status'] === 'stopped' || $container['status'] === 'created'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="start">
                                            <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                            <button type="submit" class="btn btn-small btn-success">Start</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($container['status'] === 'running'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="stop">
                                            <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                            <button type="submit" class="btn btn-small btn-warning">Stop</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="restart">
                                            <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                            <button type="submit" class="btn btn-small">Restart</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this container?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

