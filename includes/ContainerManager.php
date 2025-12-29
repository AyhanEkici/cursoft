<?php
/**
 * Container Manager Class
 * Handles Docker container operations via PHP exec
 */

require_once __DIR__ . '/Database.php';

class ContainerManager {
    private $db;
    private $dockerPath = 'docker'; // Assumes docker is in PATH
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Check if Docker is available
     */
    public function isDockerAvailable() {
        $output = [];
        $returnVar = 0;
        exec('docker --version 2>&1', $output, $returnVar);
        return $returnVar === 0;
    }
    
    /**
     * Create a new container
     */
    public function createContainer($projectId, $containerName, $image = 'cursoft-dev:latest', $portMapping = null, $envVars = []) {
        if (!$this->isDockerAvailable()) {
            throw new Exception('Docker is not available. Please ensure Docker Desktop is running.');
        }
        
        // Generate container name if not provided
        if (empty($containerName)) {
            $containerName = 'cursoft-project-' . $projectId . '-' . time();
        }
        
        // Build docker run command
        $cmd = "docker run -d --name {$containerName}";
        
        // Add port mapping
        if ($portMapping) {
            $cmd .= " -p {$portMapping}";
        }
        
        // Add environment variables
        foreach ($envVars as $key => $value) {
            $cmd .= " -e {$key}={$value}";
        }
        
        $cmd .= " {$image}";
        
        // Execute docker command
        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new Exception("Failed to create container: {$error}");
        }
        
        $containerId = trim($output[0] ?? '');
        
        // Save to database
        $this->db->query(
            "INSERT INTO containers (project_id, container_id, container_name, image, status, port_mapping, environment_vars) 
             VALUES (?, ?, ?, ?, 'created', ?, ?)",
            [
                $projectId,
                $containerId,
                $containerName,
                $image,
                $portMapping,
                json_encode($envVars)
            ]
        );
        
        $dbContainerId = $this->db->lastInsertId();
        
        // Log creation
        $this->addLog($dbContainerId, 'info', "Container created: {$containerName}");
        
        return [
            'id' => $dbContainerId,
            'container_id' => $containerId,
            'container_name' => $containerName,
            'status' => 'created'
        ];
    }
    
    /**
     * Start a container
     */
    public function startContainer($containerId) {
        $container = $this->getContainer($containerId);
        
        if (!$container) {
            throw new Exception('Container not found');
        }
        
        $cmd = "docker start {$container['container_id']}";
        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new Exception("Failed to start container: {$error}");
        }
        
        // Update status
        $this->db->query(
            "UPDATE containers SET status = 'running', started_at = NOW() WHERE id = ?",
            [$containerId]
        );
        
        $this->addLog($containerId, 'success', "Container started");
        
        return true;
    }
    
    /**
     * Stop a container
     */
    public function stopContainer($containerId) {
        $container = $this->getContainer($containerId);
        
        if (!$container) {
            throw new Exception('Container not found');
        }
        
        $cmd = "docker stop {$container['container_id']}";
        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new Exception("Failed to stop container: {$error}");
        }
        
        // Update status
        $this->db->query(
            "UPDATE containers SET status = 'stopped', stopped_at = NOW() WHERE id = ?",
            [$containerId]
        );
        
        $this->addLog($containerId, 'info', "Container stopped");
        
        return true;
    }
    
    /**
     * Restart a container
     */
    public function restartContainer($containerId) {
        $container = $this->getContainer($containerId);
        
        if (!$container) {
            throw new Exception('Container not found');
        }
        
        $cmd = "docker restart {$container['container_id']}";
        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new Exception("Failed to restart container: {$error}");
        }
        
        // Update status
        $this->db->query(
            "UPDATE containers SET status = 'running', started_at = NOW() WHERE id = ?",
            [$containerId]
        );
        
        $this->addLog($containerId, 'info', "Container restarted");
        
        return true;
    }
    
    /**
     * Remove a container
     */
    public function removeContainer($containerId) {
        $container = $this->getContainer($containerId);
        
        if (!$container) {
            throw new Exception('Container not found');
        }
        
        // Stop first if running
        if ($container['status'] === 'running') {
            $this->stopContainer($containerId);
        }
        
        $cmd = "docker rm {$container['container_id']}";
        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new Exception("Failed to remove container: {$error}");
        }
        
        // Update status
        $this->db->query(
            "UPDATE containers SET status = 'removed' WHERE id = ?",
            [$containerId]
        );
        
        $this->addLog($containerId, 'info', "Container removed");
        
        return true;
    }
    
    /**
     * Get container status from Docker
     */
    public function getContainerStatus($containerId) {
        $container = $this->getContainer($containerId);
        
        if (!$container || !$container['container_id']) {
            return null;
        }
        
        $cmd = "docker inspect --format='{{.State.Status}}' {$container['container_id']}";
        $output = [];
        exec($cmd . ' 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            return 'error';
        }
        
        $status = trim($output[0] ?? 'unknown');
        
        // Map Docker status to our status enum
        $statusMap = [
            'created' => 'created',
            'running' => 'running',
            'stopped' => 'stopped',
            'paused' => 'paused',
            'exited' => 'stopped',
            'dead' => 'error'
        ];
        
        $mappedStatus = $statusMap[$status] ?? 'error';
        
        // Update database if status changed
        if ($mappedStatus !== $container['status']) {
            $this->db->query(
                "UPDATE containers SET status = ? WHERE id = ?",
                [$mappedStatus, $containerId]
            );
        }
        
        return $mappedStatus;
    }
    
    /**
     * Get container logs from Docker
     */
    public function getContainerLogs($containerId, $lines = 100) {
        $container = $this->getContainer($containerId);
        
        if (!$container || !$container['container_id']) {
            return [];
        }
        
        $cmd = "docker logs --tail {$lines} {$container['container_id']} 2>&1";
        $output = [];
        exec($cmd, $output, $returnVar);
        
        return $output;
    }
    
    /**
     * Get container from database
     */
    public function getContainer($containerId) {
        return $this->db->fetchOne(
            "SELECT * FROM containers WHERE id = ?",
            [$containerId]
        );
    }
    
    /**
     * Get all containers for a project
     */
    public function getProjectContainers($projectId) {
        return $this->db->fetchAll(
            "SELECT * FROM containers WHERE project_id = ? ORDER BY created_at DESC",
            [$projectId]
        );
    }
    
    /**
     * Get all containers
     */
    public function getAllContainers() {
        return $this->db->fetchAll(
            "SELECT c.*, p.name as project_name FROM containers c 
             LEFT JOIN projects p ON c.project_id = p.id 
             ORDER BY c.created_at DESC"
        );
    }
    
    /**
     * Add log entry
     */
    public function addLog($containerId, $level, $message) {
        $this->db->query(
            "INSERT INTO container_logs (container_id, log_level, message) VALUES (?, ?, ?)",
            [$containerId, $level, $message]
        );
    }
    
    /**
     * Get container logs from database
     */
    public function getContainerLogsFromDb($containerId, $limit = 100) {
        return $this->db->fetchAll(
            "SELECT * FROM container_logs WHERE container_id = ? ORDER BY created_at DESC LIMIT ?",
            [$containerId, $limit]
        );
    }
}
?>

