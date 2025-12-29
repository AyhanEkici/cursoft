<?php
/**
 * Container Orchestrator Class
 * Manages container lifecycle and integration with projects
 */

require_once __DIR__ . '/ContainerManager.php';
require_once __DIR__ . '/Database.php';

class ContainerOrchestrator {
    private $containerManager;
    private $basePort = 8000;
    private $portRange = 1000; // Ports 8000-8999
    
    public function __construct() {
        $this->containerManager = new ContainerManager();
    }
    
    /**
     * Create container for a project
     */
    public function createProjectContainer($projectId, $options = []) {
        // Get available port
        $port = $this->getAvailablePort();
        
        // Generate container name
        $containerName = 'cursoft-project-' . $projectId . '-' . time();
        
        // Default environment variables
        $envVars = [
            'PROJECT_ID' => $projectId,
            'CURSOFT_ENV' => 'development'
        ];
        
        // Merge with provided options
        if (isset($options['env_vars'])) {
            $envVars = array_merge($envVars, $options['env_vars']);
        }
        
        $image = $options['image'] ?? 'cursoft-dev:latest';
        $portMapping = $options['port_mapping'] ?? "{$port}:80";
        
        // Create container
        $container = $this->containerManager->createContainer(
            $projectId,
            $containerName,
            $image,
            $portMapping,
            $envVars
        );
        
        // Auto-start if requested
        if (isset($options['auto_start']) && $options['auto_start']) {
            $this->containerManager->startContainer($container['id']);
        }
        
        return $container;
    }
    
    /**
     * Get available port (simple implementation)
     */
    private function getAvailablePort() {
        $db = new Database();
        
        // Get all used ports
        $containers = $db->fetchAll(
            "SELECT port_mapping FROM containers WHERE port_mapping IS NOT NULL AND status != 'removed'"
        );
        
        $usedPorts = [];
        foreach ($containers as $container) {
            if (preg_match('/^(\d+):/', $container['port_mapping'], $matches)) {
                $usedPorts[] = (int)$matches[1];
            }
        }
        
        // Find first available port
        for ($port = $this->basePort; $port < $this->basePort + $this->portRange; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }
        
        throw new Exception('No available ports in range');
    }
    
    /**
     * Start container for a project
     */
    public function startProjectContainer($projectId) {
        $containers = $this->containerManager->getProjectContainers($projectId);
        
        if (empty($containers)) {
            throw new Exception('No containers found for this project');
        }
        
        // Start the most recent container
        $container = $containers[0];
        return $this->containerManager->startContainer($container['id']);
    }
    
    /**
     * Stop container for a project
     */
    public function stopProjectContainer($projectId) {
        $containers = $this->containerManager->getProjectContainers($projectId);
        
        if (empty($containers)) {
            throw new Exception('No containers found for this project');
        }
        
        // Stop the most recent container
        $container = $containers[0];
        return $this->containerManager->stopContainer($container['id']);
    }
    
    /**
     * Get project container status
     */
    public function getProjectContainerStatus($projectId) {
        $containers = $this->containerManager->getProjectContainers($projectId);
        
        if (empty($containers)) {
            return null;
        }
        
        $container = $containers[0];
        $status = $this->containerManager->getContainerStatus($container['id']);
        
        return [
            'container' => $container,
            'status' => $status,
            'logs' => $this->containerManager->getContainerLogsFromDb($container['id'], 20)
        ];
    }
    
    /**
     * Clean up stopped containers older than X days
     */
    public function cleanupOldContainers($days = 7) {
        $db = new Database();
        
        $containers = $db->fetchAll(
            "SELECT id, container_id FROM containers 
             WHERE status IN ('stopped', 'error') 
             AND updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        
        $removed = 0;
        foreach ($containers as $container) {
            try {
                $this->containerManager->removeContainer($container['id']);
                $removed++;
            } catch (Exception $e) {
                // Log error but continue
                error_log("Failed to remove container {$container['id']}: " . $e->getMessage());
            }
        }
        
        return $removed;
    }
    
    /**
     * Sync container statuses with Docker
     */
    public function syncContainerStatuses() {
        $db = new Database();
        $containers = $db->fetchAll(
            "SELECT id, container_id FROM containers WHERE status != 'removed'"
        );
        
        $synced = 0;
        foreach ($containers as $container) {
            try {
                $this->containerManager->getContainerStatus($container['id']);
                $synced++;
            } catch (Exception $e) {
                // Log error but continue
                error_log("Failed to sync container {$container['id']}: " . $e->getMessage());
            }
        }
        
        return $synced;
    }
}
?>

