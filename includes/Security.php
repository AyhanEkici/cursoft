<?php
/**
 * Security Class
 * Handles CSRF protection, rate limiting, input validation, and security logging
 */

require_once __DIR__ . '/Database.php';

class Security {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->startSession();
    }
    
    /**
     * Start session if not started
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validate and sanitize input
     */
    public function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            case 'string':
            default:
                return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate input against rules
     */
    public function validateInput($value, $rules) {
        $errors = [];
        
        // Required
        if (isset($rules['required']) && $rules['required'] && empty($value)) {
            $errors[] = 'This field is required';
            return $errors;
        }
        
        if (empty($value) && !isset($rules['required'])) {
            return []; // Optional field, skip validation if empty
        }
        
        // Min length
        if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
            $errors[] = "Minimum length is {$rules['min_length']} characters";
        }
        
        // Max length
        if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
            $errors[] = "Maximum length is {$rules['max_length']} characters";
        }
        
        // Email validation
        if (isset($rules['type']) && $rules['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        // Pattern matching
        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $value)) {
            $errors[] = $rules['pattern_message'] ?? 'Invalid format';
        }
        
        return $errors;
    }
    
    /**
     * Rate limiting - check if request should be allowed
     */
    public function checkRateLimit($identifier, $maxRequests = 60, $timeWindow = 60) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$identifier}_{$ip}";
        
        // Get current count
        $current = $this->db->fetchOne(
            "SELECT request_count, reset_time FROM rate_limits 
             WHERE identifier = ? AND ip_address = ?",
            [$identifier, $ip]
        );
        
        $now = time();
        
        if (!$current || $current['reset_time'] < $now) {
            // Reset or create new entry
            $resetTime = $now + $timeWindow;
            if ($current) {
                $this->db->query(
                    "UPDATE rate_limits SET request_count = 1, reset_time = ? 
                     WHERE identifier = ? AND ip_address = ?",
                    [$resetTime, $identifier, $ip]
                );
            } else {
                $this->db->query(
                    "INSERT INTO rate_limits (identifier, ip_address, request_count, reset_time) 
                     VALUES (?, ?, 1, ?)",
                    [$identifier, $ip, $resetTime]
                );
            }
            return true;
        }
        
        // Check if limit exceeded
        if ($current['request_count'] >= $maxRequests) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'identifier' => $identifier,
                'ip' => $ip,
                'count' => $current['request_count']
            ]);
            return false;
        }
        
        // Increment count
        $this->db->query(
            "UPDATE rate_limits SET request_count = request_count + 1 
             WHERE identifier = ? AND ip_address = ?",
            [$identifier, $ip]
        );
        
        return true;
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent($eventType, $data = []) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? null;
        
        try {
            $this->db->query(
                "INSERT INTO security_logs (event_type, ip_address, user_agent, user_id, event_data, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $eventType,
                    $ip,
                    $userAgent,
                    $userId,
                    json_encode($data)
                ]
            );
        } catch (Exception $e) {
            // Log to file if database fails
            error_log("Security log error: " . $e->getMessage());
        }
    }
    
    /**
     * Generate secure random token
     */
    public function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Check if request is from allowed origin (CORS)
     */
    public function validateOrigin($allowedOrigins = []) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        
        if (empty($allowedOrigins)) {
            return true; // Allow all if not specified
        }
        
        return $origin && in_array($origin, $allowedOrigins);
    }
    
    /**
     * Clean old rate limit entries
     */
    public function cleanRateLimits() {
        $this->db->query(
            "DELETE FROM rate_limits WHERE reset_time < ?",
            [time()]
        );
    }
    
    /**
     * Clean old security logs (keep last 30 days)
     */
    public function cleanSecurityLogs($days = 30) {
        $this->db->query(
            "DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
    }
}
?>

