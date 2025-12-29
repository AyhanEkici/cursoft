<?php
/**
 * Debugger Agent Class
 * Autonomous debugging and error fixing
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AgentToolkit.php';
require_once __DIR__ . '/LLMBridge.php';

class DebuggerAgent {
    private $db;
    private $toolkit;
    private $llmBridge;
    
    public function __construct() {
        $this->db = new Database();
        $this->toolkit = new AgentToolkit();
        $this->llmBridge = new LLMBridge();
    }
    
    /**
     * Analyze error and suggest fix
     */
    public function analyzeError($projectId, $errorMessage, $codeContext = '', $options = []) {
        $userId = $options['user_id'] ?? 1;
        $llmProvider = $options['llm_provider'] ?? 'openai';
        
        // Build prompt for LLM
        $prompt = "I have an error in my code. Please analyze it and suggest a fix.\n\n";
        $prompt .= "Error: {$errorMessage}\n\n";
        
        if (!empty($codeContext)) {
            $prompt .= "Code context:\n{$codeContext}\n\n";
        }
        
        $prompt .= "Please provide:\n";
        $prompt .= "1. A brief explanation of the error\n";
        $prompt .= "2. The root cause\n";
        $prompt .= "3. A suggested fix with code example\n";
        $prompt .= "4. Prevention tips\n";
        
        try {
            $response = $this->llmBridge->makeRequest($userId, $llmProvider, $prompt, [
                'project_id' => $projectId,
                'max_tokens' => 1500
            ]);
            
            // Parse response
            $analysis = $this->parseAnalysis($response['text']);
            
            // Log action
            $this->logAction($projectId, 'error_analysis', "Analyzed error: " . substr($errorMessage, 0, 100), [
                'error' => $errorMessage,
                'analysis' => $analysis
            ]);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'llm_response' => $response['text'],
                'tokens_used' => $response['tokens']['total'],
                'cost' => $response['cost']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Automatically fix code errors
     */
    public function autoFix($projectId, $filePath, $errorMessage, $options = []) {
        $userId = $options['user_id'] ?? 1;
        $llmProvider = $options['llm_provider'] ?? 'openai';
        
        try {
            // Read the file
            $code = $this->toolkit->readFile($projectId, $filePath);
            
            // Analyze error
            $analysis = $this->analyzeError($projectId, $errorMessage, $code, $options);
            
            if (!$analysis['success']) {
                throw new Exception("Failed to analyze error");
            }
            
            // Build fix prompt
            $prompt = "Fix this code error:\n\n";
            $prompt .= "Error: {$errorMessage}\n\n";
            $prompt .= "Current code:\n{$code}\n\n";
            $prompt .= "Please provide the corrected code. Return ONLY the fixed code, no explanations.";
            
            // Get fix from LLM
            $response = $this->llmBridge->makeRequest($userId, $llmProvider, $prompt, [
                'project_id' => $projectId,
                'max_tokens' => 2000
            ]);
            
            $fixedCode = trim($response['text']);
            
            // Validate the fix
            $validation = $this->validateFix($fixedCode, $filePath);
            
            if (!$validation['valid']) {
                throw new Exception("Generated fix has errors: " . implode(', ', $validation['errors']));
            }
            
            // Apply fix (with backup)
            $this->toolkit->editFile($projectId, $filePath, $fixedCode, ['backup' => true]);
            
            // Log action
            $this->logAction($projectId, 'auto_fix', "Auto-fixed: {$filePath}", [
                'file_path' => $filePath,
                'error' => $errorMessage
            ]);
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'fixed_code' => $fixedCode,
                'analysis' => $analysis['analysis']
            ];
        } catch (Exception $e) {
            $this->logAction($projectId, 'auto_fix', "Failed to fix: {$filePath}", [
                'error' => $e->getMessage()
            ], 'failed');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Review code for issues
     */
    public function reviewCode($projectId, $filePath, $options = []) {
        $userId = $options['user_id'] ?? 1;
        $llmProvider = $options['llm_provider'] ?? 'openai';
        
        try {
            // Read the file
            $code = $this->toolkit->readFile($projectId, $filePath);
            
            // Build review prompt
            $prompt = "Review this code and identify:\n";
            $prompt .= "1. Potential bugs or errors\n";
            $prompt .= "2. Security vulnerabilities\n";
            $prompt .= "3. Performance issues\n";
            $prompt .= "4. Code quality improvements\n";
            $prompt .= "5. Best practices violations\n\n";
            $prompt .= "Code:\n{$code}\n\n";
            $prompt .= "Provide a structured review with specific line numbers and suggestions.";
            
            // Get review from LLM
            $response = $this->llmBridge->makeRequest($userId, $llmProvider, $prompt, [
                'project_id' => $projectId,
                'max_tokens' => 2000
            ]);
            
            // Parse review
            $review = $this->parseReview($response['text']);
            
            // Log action
            $this->logAction($projectId, 'code_review', "Reviewed code: {$filePath}", [
                'file_path' => $filePath,
                'issues_found' => count($review['issues'] ?? [])
            ]);
            
            return [
                'success' => true,
                'review' => $review,
                'llm_response' => $response['text'],
                'tokens_used' => $response['tokens']['total'],
                'cost' => $response['cost']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse analysis response from LLM
     */
    private function parseAnalysis($text) {
        // Simple parsing - can be enhanced
        return [
            'explanation' => $text,
            'root_cause' => '',
            'suggested_fix' => '',
            'prevention' => ''
        ];
    }
    
    /**
     * Parse review response from LLM
     */
    private function parseReview($text) {
        // Simple parsing - can be enhanced
        return [
            'summary' => $text,
            'issues' => [],
            'suggestions' => []
        ];
    }
    
    /**
     * Validate fix
     */
    private function validateFix($code, $filePath) {
        // Basic validation
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        switch ($extension) {
            case 'php':
                // Check PHP syntax
                $tempFile = tempnam(sys_get_temp_dir(), 'php_');
                file_put_contents($tempFile, $code);
                $output = [];
                exec("php -l {$tempFile} 2>&1", $output, $returnVar);
                unlink($tempFile);
                
                return [
                    'valid' => $returnVar === 0,
                    'errors' => $returnVar !== 0 ? $output : []
                ];
                
            default:
                return ['valid' => true, 'errors' => []];
        }
    }
    
    /**
     * Log agent action
     */
    private function logAction($projectId, $actionType, $description, $data = [], $result = 'success') {
        $this->db->query(
            "INSERT INTO agent_actions (project_id, agent_type, action_type, action_description, action_data, result) 
             VALUES (?, 'debugger', ?, ?, ?, ?)",
            [$projectId, $actionType, $description, json_encode($data), $result]
        );
    }
}
?>

