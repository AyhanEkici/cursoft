<?php
/**
 * Project Planner Class
 * Decomposes user prompts into actionable development tasks
 */

require_once __DIR__ . '/Database.php';

class ProjectPlanner {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Decompose a user prompt into actionable development tasks
     */
    public function decomposePrompt($prompt, $projectId) {
        // Basic decomposition logic (can be enhanced with LLM integration later)
        $tasks = $this->analyzePrompt($prompt);
        
        // Save tasks to database
        foreach ($tasks as $index => $task) {
            $this->db->query(
                "INSERT INTO project_plans (project_id, task_order, task_name, task_description, estimated_time) 
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $projectId,
                    $index + 1,
                    $task['name'],
                    $task['description'],
                    $task['estimated_time']
                ]
            );
        }
        
        return $tasks;
    }
    
    /**
     * Analyze prompt and extract tasks
     * This is a basic implementation - Phase 3 will enhance with LLM
     */
    private function analyzePrompt($prompt) {
        $tasks = [];
        $promptLower = strtolower($prompt);
        
        // Detect project type
        $projectType = $this->detectProjectType($promptLower);
        
        // Base tasks for any project
        $tasks[] = [
            'name' => 'Project Setup',
            'description' => 'Initialize project structure and dependencies',
            'estimated_time' => 15
        ];
        
        // Web application tasks
        if (strpos($promptLower, 'web') !== false || 
            strpos($promptLower, 'website') !== false ||
            strpos($promptLower, 'app') !== false) {
            
            $tasks[] = [
                'name' => 'Frontend Setup',
                'description' => 'Create HTML/CSS/JavaScript structure',
                'estimated_time' => 30
            ];
            
            $tasks[] = [
                'name' => 'Backend API',
                'description' => 'Develop REST API endpoints',
                'estimated_time' => 45
            ];
            
            $tasks[] = [
                'name' => 'Database Design',
                'description' => 'Create database schema and migrations',
                'estimated_time' => 20
            ];
        }
        
        // Database tasks
        if (strpos($promptLower, 'database') !== false || 
            strpos($promptLower, 'mysql') !== false ||
            strpos($promptLower, 'data') !== false) {
            
            $tasks[] = [
                'name' => 'Database Schema',
                'description' => 'Design and implement database structure',
                'estimated_time' => 25
            ];
        }
        
        // Authentication tasks
        if (strpos($promptLower, 'login') !== false || 
            strpos($promptLower, 'auth') !== false ||
            strpos($promptLower, 'user') !== false ||
            strpos($promptLower, 'register') !== false) {
            
            $tasks[] = [
                'name' => 'Authentication System',
                'description' => 'Implement user registration and login',
                'estimated_time' => 40
            ];
        }
        
        // API tasks
        if (strpos($promptLower, 'api') !== false || 
            strpos($promptLower, 'rest') !== false) {
            
            $tasks[] = [
                'name' => 'API Development',
                'description' => 'Create RESTful API endpoints',
                'estimated_time' => 50
            ];
        }
        
        // Testing tasks
        $tasks[] = [
            'name' => 'Testing',
            'description' => 'Write and run unit and integration tests',
            'estimated_time' => 30
        ];
        
        // Documentation
        $tasks[] = [
            'name' => 'Documentation',
            'description' => 'Create README and API documentation',
            'estimated_time' => 15
        ];
        
        return $tasks;
    }
    
    private function detectProjectType($prompt) {
        if (strpos($prompt, 'api') !== false) return 'api';
        if (strpos($prompt, 'web') !== false || strpos($prompt, 'website') !== false) return 'web';
        if (strpos($prompt, 'mobile') !== false) return 'mobile';
        if (strpos($prompt, 'game') !== false) return 'game';
        return 'general';
    }
    
    /**
     * Get project plan for a project
     */
    public function getProjectPlan($projectId) {
        return $this->db->fetchAll(
            "SELECT * FROM project_plans WHERE project_id = ? ORDER BY task_order",
            [$projectId]
        );
    }
    
    /**
     * Create a new project
     */
    public function createProject($userId, $name, $prompt) {
        $this->db->query(
            "INSERT INTO projects (user_id, name, prompt, status) VALUES (?, ?, ?, 'planning')",
            [$userId, $name, $prompt]
        );
        
        $projectId = $this->db->lastInsertId();
        
        // Decompose prompt into tasks
        $this->decomposePrompt($prompt, $projectId);
        
        // Log creation
        $this->addLog($projectId, 'info', "Project created: {$name}");
        $this->addLog($projectId, 'info', "Prompt analyzed and decomposed into tasks");
        
        return $projectId;
    }
    
    /**
     * Add log entry
     */
    public function addLog($projectId, $level, $message) {
        $this->db->query(
            "INSERT INTO project_logs (project_id, log_level, message) VALUES (?, ?, ?)",
            [$projectId, $level, $message]
        );
    }
    
    /**
     * Get project with plan
     */
    public function getProject($projectId) {
        $project = $this->db->fetchOne(
            "SELECT * FROM projects WHERE id = ?",
            [$projectId]
        );
        
        if ($project) {
            $project['plan'] = $this->getProjectPlan($projectId);
            $project['logs'] = $this->db->fetchAll(
                "SELECT * FROM project_logs WHERE project_id = ? ORDER BY created_at DESC LIMIT 50",
                [$projectId]
            );
        }
        
        return $project;
    }
    
    /**
     * Get all projects for a user
     */
    public function getUserProjects($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }
}
?>

