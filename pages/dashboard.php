<?php
/**
 * Dashboard Page
 * Main dashboard showing all projects
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/ProjectPlanner.php';

$sessionManager = new SessionManager();
$sessionManager->requireLogin();

$user = $sessionManager->getCurrentUser();
$db = new Database();
$planner = new ProjectPlanner();

// Get user's projects
$projects = $planner->getUserProjects($user['id']);

// Get statistics
$stats = [
    'total' => count($projects),
    'active' => count(array_filter($projects, fn($p) => $p['status'] === 'active')),
    'completed' => count(array_filter($projects, fn($p) => $p['status'] === 'completed')),
    'planning' => count(array_filter($projects, fn($p) => $p['status'] === 'planning'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cursoft</title>
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
        <h1 style="margin-bottom: 30px;">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        
        <!-- Statistics -->
        <div class="row" style="margin-bottom: 30px;">
            <div class="col-3">
                <div class="card">
                    <h3 style="color: #667eea; margin-bottom: 10px;"><?php echo $stats['total']; ?></h3>
                    <p style="color: #666;">Total Projects</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <h3 style="color: #28a745; margin-bottom: 10px;"><?php echo $stats['active']; ?></h3>
                    <p style="color: #666;">Active</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <h3 style="color: #17a2b8; margin-bottom: 10px;"><?php echo $stats['completed']; ?></h3>
                    <p style="color: #666;">Completed</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <h3 style="color: #ffc107; margin-bottom: 10px;"><?php echo $stats['planning']; ?></h3>
                    <p style="color: #666;">Planning</p>
                </div>
            </div>
        </div>
        
        <!-- Projects List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Your Projects</h2>
            </div>
            
            <?php if (empty($projects)): ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="font-size: 1.2em; color: #666; margin-bottom: 20px;">No projects yet.</p>
                    <a href="new_project.php" class="btn btn-primary">Create Your First Project</a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($project['name']); ?></strong><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars(substr($project['prompt'], 0, 60)); ?>...</small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $project['status'] === 'completed' ? 'success' : 
                                            ($project['status'] === 'active' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $project['progress']; ?>%;">
                                            <?php echo $project['progress']; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <a href="project_detail.php?id=<?php echo $project['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.9em;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="new_project.php" class="btn btn-primary">+ Create New Project</a>
        </div>
    </div>
</body>
</html>

