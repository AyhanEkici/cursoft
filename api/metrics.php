<?php
/**
 * Prometheus Metrics Endpoint
 * Exposes application metrics in Prometheus format
 */

header('Content-Type: text/plain; version=0.0.4');

require_once __DIR__ . '/../includes/Database.php';

$db = new Database();

// Get project statistics
$totalProjects = $db->fetchOne("SELECT COUNT(*) as count FROM projects")['count'] ?? 0;
$activeProjects = $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE status = 'active'")['count'] ?? 0;
$completedProjects = $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE status = 'completed'")['count'] ?? 0;

// Get container statistics
$totalContainers = $db->fetchOne("SELECT COUNT(*) as count FROM containers")['count'] ?? 0;
$runningContainers = $db->fetchOne("SELECT COUNT(*) as count FROM containers WHERE status = 'running'")['count'] ?? 0;

// Get LLM request statistics
$totalLLMRequests = $db->fetchOne("SELECT COUNT(*) as count FROM llm_requests")['count'] ?? 0;
$totalLLMCost = $db->fetchOne("SELECT SUM(cost_usd) as total FROM llm_requests WHERE status = 'completed'")['total'] ?? 0;

// Output Prometheus metrics
echo "# HELP cursoft_projects_total Total number of projects\n";
echo "# TYPE cursoft_projects_total gauge\n";
echo "cursoft_projects_total $totalProjects\n";

echo "# HELP cursoft_projects_active Number of active projects\n";
echo "# TYPE cursoft_projects_active gauge\n";
echo "cursoft_projects_active $activeProjects\n";

echo "# HELP cursoft_projects_completed Number of completed projects\n";
echo "# TYPE cursoft_projects_completed gauge\n";
echo "cursoft_projects_completed $completedProjects\n";

echo "# HELP cursoft_containers_total Total number of containers\n";
echo "# TYPE cursoft_containers_total gauge\n";
echo "cursoft_containers_total $totalContainers\n";

echo "# HELP cursoft_containers_running Number of running containers\n";
echo "# TYPE cursoft_containers_running gauge\n";
echo "cursoft_containers_running $runningContainers\n";

echo "# HELP cursoft_llm_requests_total Total number of LLM requests\n";
echo "# TYPE cursoft_llm_requests_total counter\n";
echo "cursoft_llm_requests_total $totalLLMRequests\n";

echo "# HELP cursoft_llm_cost_total Total cost of LLM requests in USD\n";
echo "# TYPE cursoft_llm_cost_total counter\n";
echo "cursoft_llm_cost_total $totalLLMCost\n";

?>

