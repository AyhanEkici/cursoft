<?php
/**
 * Agent Toolkit Class
 * Provides safe execution tools for agents
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ContainerManager.php';

class AgentToolkit {
    private $db;
    private $containerManager;
    private $workspaceBase = 'E:/xampp/htdocs/cursoft/workspaces';
    
    public function __construct() {
        $this->db = new Database();
        $this->containerManager = new ContainerManager();
    }
    
    /**
     * Create a file safely
     */
    public function createFile($projectId, $filePath, $content, $options = []) {
        $workspacePath = $this->getWorkspacePath($projectId);
        $fullPath = $workspacePath . '/' . ltrim($filePath, '/');
        
        // Validate path (prevent directory traversal)
        $fullPath = realpath(dirname($fullPath)) . '/' . basename($fullPath);
        if (strpos($fullPath, $workspacePath) !== 0) {
            throw new Exception("Invalid file path: {$filePath}");
        }
        
        // Create directory if needed
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Write file
        $result = file_put_contents($fullPath, $content);
        
        if ($result === false) {
            throw new Exception("Failed to create file: {$filePath}");
        }
        
        // Log action
        $this->logAction($projectId, 'file_create', "Created file: {$filePath}", [
            'file_path' => $filePath,
            'size' => strlen($content)
        ]);
        
        return [
            'success' => true,
            'file_path' => $filePath,
            'size' => $result
        ];
    }
    
    /**
     * Edit a file safely
     */
    public function editFile($projectId, $filePath, $newContent, $options = []) {
        $workspacePath = $this->getWorkspacePath($projectId);
        $fullPath = $workspacePath . '/' . ltrim($filePath, '/');
        
        // Validate path
        $fullPath = realpath(dirname($fullPath)) . '/' . basename($fullPath);
        if (strpos($fullPath, $workspacePath) !== 0) {
            throw new Exception("Invalid file path: {$filePath}");
        }
        
        if (!file_exists($fullPath)) {
            throw new Exception("File does not exist: {$filePath}");
        }
        
        // Backup original if requested
        if (isset($options['backup']) && $options['backup']) {
            $backupPath = $fullPath . '.backup.' . time();
            copy($fullPath, $backupPath);
        }
        
        // Write file
        $result = file_put_contents($fullPath, $newContent);
        
        if ($result === false) {
            throw new Exception("Failed to edit file: {$filePath}");
        }
        
        // Log action
        $this->logAction($projectId, 'file_edit', "Edited file: {$filePath}", [
            'file_path' => $filePath,
            'size' => strlen($newContent)
        ]);
        
        return [
            'success' => true,
            'file_path' => $filePath,
            'size' => $result
        ];
    }
    
    /**
     * Read a file safely
     */
    public function readFile($projectId, $filePath) {
        $workspacePath = $this->getWorkspacePath($projectId);
        $fullPath = $workspacePath . '/' . ltrim($filePath, '/');
        
        // Validate path
        $fullPath = realpath($fullPath);
        if (!$fullPath || strpos($fullPath, $workspacePath) !== 0) {
            throw new Exception("Invalid file path: {$filePath}");
        }
        
        if (!file_exists($fullPath)) {
            throw new Exception("File does not exist: {$filePath}");
        }
        
        return file_get_contents($fullPath);
    }
    
    /**
     * Execute code in container
     */
    public function executeCode($projectId, $command, $options = []) {
        // Get container for project
        $containers = $this->containerManager->getProjectContainers($projectId);
        
        if (empty($containers)) {
            throw new Exception("No container found for project {$projectId}");
        }
        
        $container = $containers[0];
        
        // Sanitize command (basic security)
        $command = escapeshellarg($command);
        
        // Execute in container
        $dockerCmd = "docker exec {$container['container_id']} sh -c {$command}";
        $output = [];
        $returnVar = 0;
        exec($dockerCmd . ' 2>&1', $output, $returnVar);
        
        // Log action
        $this->logAction($projectId, 'code_execute', "Executed command in container", [
            'command' => $command,
            'return_code' => $returnVar,
            'output_lines' => count($output)
        ]);
        
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output),
            'return_code' => $returnVar
        ];
    }
    
    /**
     * List files in workspace
     */
    public function listFiles($projectId, $directory = '') {
        $workspacePath = $this->getWorkspacePath($projectId);
        $fullPath = $workspacePath . '/' . ltrim($directory, '/');
        
        // Validate path
        $fullPath = realpath($fullPath);
        if (!$fullPath || strpos($fullPath, $workspacePath) !== 0) {
            throw new Exception("Invalid directory path: {$directory}");
        }
        
        if (!is_dir($fullPath)) {
            throw new Exception("Directory does not exist: {$directory}");
        }
        
        $files = [];
        $items = scandir($fullPath);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $itemPath = $fullPath . '/' . $item;
            $relativePath = $directory . '/' . $item;
            
            $files[] = [
                'name' => $item,
                'path' => ltrim($relativePath, '/'),
                'type' => is_dir($itemPath) ? 'directory' : 'file',
                'size' => is_file($itemPath) ? filesize($itemPath) : 0,
                'modified' => date('Y-m-d H:i:s', filemtime($itemPath))
            ];
        }
        
        return $files;
    }
    
    /**
     * Get workspace path for project
     */
    private function getWorkspacePath($projectId) {
        $path = $this->workspaceBase . '/project-' . $projectId;
        
        // Create if doesn't exist
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        return $path;
    }
    
    /**
     * Log agent action
     */
    private function logAction($projectId, $actionType, $description, $data = []) {
        $this->db->query(
            "INSERT INTO agent_actions (project_id, agent_type, action_type, action_description, action_data, result) 
             VALUES (?, 'toolkit', ?, ?, ?, 'success')",
            [$projectId, $actionType, $description, json_encode($data)]
        );
    }
    
    /**
     * Validate code syntax (basic)
     */
    public function validateCode($code, $language = 'php') {
        // Basic validation - can be enhanced
        switch ($language) {
            case 'php':
                // Check PHP syntax
                $result = @eval('return true;' . $code);
                return ['valid' => $result !== false, 'errors' => []];
                
            case 'javascript':
            case 'js':
                // Basic JS validation (would need Node.js or external service)
                return ['valid' => true, 'errors' => []];
                
            default:
                return ['valid' => true, 'errors' => []];
        }
    }
    
    /**
     * Get project workspace info
     */
    public function getWorkspaceInfo($projectId) {
        $workspacePath = $this->getWorkspacePath($projectId);
        
        if (!is_dir($workspacePath)) {
            return [
                'exists' => false,
                'path' => $workspacePath,
                'size' => 0,
                'file_count' => 0
            ];
        }
        
        $size = $this->getDirectorySize($workspacePath);
        $fileCount = $this->countFiles($workspacePath);
        
        return [
            'exists' => true,
            'path' => $workspacePath,
            'size' => $size,
            'file_count' => $fileCount
        ];
    }
    
    /**
     * Get directory size recursively
     */
    private function getDirectorySize($directory) {
        $size = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Count files recursively
     */
    private function countFiles($directory) {
        $count = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
}
?>

