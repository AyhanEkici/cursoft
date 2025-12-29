<?php
/**
 * Authentication Class
 * Handles user authentication and session management
 */

require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    private $sessionLifetime = 86400; // 24 hours
    
    public function __construct() {
        $this->db = new Database();
        $this->startSession();
    }
    
    /**
     * Start PHP session
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register a new user
     */
    public function register($email, $password, $name) {
        // Validate input
        if (empty($email) || empty($password) || empty($name)) {
            throw new Exception("All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        
        // Check if user exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existing) {
            throw new Exception("Email already registered");
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user
        $this->db->query(
            "INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)",
            [$email, $passwordHash, $name]
        );
        
        $userId = $this->db->lastInsertId();
        
        // Create default preferences
        $this->db->query(
            "INSERT INTO user_preferences (user_id) VALUES (?)",
            [$userId]
        );
        
        return $userId;
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $rememberMe = false) {
        // Get user
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            throw new Exception("Invalid email or password");
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception("Invalid email or password");
        }
        
        // Create session
        $sessionToken = $this->generateSessionToken();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);
        
        // Save session to database
        $this->db->query(
            "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $user['id'],
                $sessionToken,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $expiresAt
            ]
        );
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['session_token'] = $sessionToken;
        
        // Set cookie if remember me
        if ($rememberMe) {
            setcookie('cursoft_session', $sessionToken, time() + $this->sessionLifetime, '/');
        }
        
        return $user;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            // Delete session from database
            $this->db->query(
                "DELETE FROM user_sessions WHERE session_token = ?",
                [$_SESSION['session_token']]
            );
        }
        
        // Clear session
        $_SESSION = [];
        session_destroy();
        
        // Clear cookie
        if (isset($_COOKIE['cursoft_session'])) {
            setcookie('cursoft_session', '', time() - 3600, '/');
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        // Verify session token
        $session = $this->db->fetchOne(
            "SELECT * FROM user_sessions 
             WHERE session_token = ? AND user_id = ? AND expires_at > NOW()",
            [$_SESSION['session_token'], $_SESSION['user_id']]
        );
        
        if (!$session) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name']
        ];
    }
    
    /**
     * Get user by ID
     */
    public function getUser($userId) {
        return $this->db->fetchOne(
            "SELECT id, email, name, created_at FROM users WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Generate session token
     */
    private function generateSessionToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions() {
        $this->db->query(
            "DELETE FROM user_sessions WHERE expires_at < NOW()"
        );
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->getUser($userId);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Get full user with password
        $userFull = $this->db->fetchOne(
            "SELECT password_hash FROM users WHERE id = ?",
            [$userId]
        );
        
        // Verify current password
        if (!password_verify($currentPassword, $userFull['password_hash'])) {
            throw new Exception("Current password is incorrect");
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            throw new Exception("New password must be at least 8 characters");
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$newHash, $userId]
        );
        
        return true;
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        $user = $this->db->fetchOne(
            "SELECT id, email, name FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            // Don't reveal if user exists
            return true;
        }
        
        // Generate reset token
        require_once __DIR__ . '/Security.php';
        $security = new Security();
        $token = $security->generateToken(32);
        
        // Expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        
        // Save token
        $this->db->query(
            "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$user['id'], $token, $expiresAt]
        );
        
        // In production, send email here
        // For now, return token (remove in production!)
        return [
            'token' => $token,
            'user' => $user
        ];
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        // Validate password
        if (strlen($newPassword) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        
        // Find valid token
        $resetToken = $this->db->fetchOne(
            "SELECT * FROM password_reset_tokens 
             WHERE token = ? AND expires_at > NOW() AND used = FALSE",
            [$token]
        );
        
        if (!$resetToken) {
            throw new Exception("Invalid or expired reset token");
        }
        
        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $resetToken['user_id']]
        );
        
        // Mark token as used
        $this->db->query(
            "UPDATE password_reset_tokens SET used = TRUE WHERE id = ?",
            [$resetToken['id']]
        );
        
        // Invalidate all sessions for this user
        $this->db->query(
            "DELETE FROM user_sessions WHERE user_id = ?",
            [$resetToken['user_id']]
        );
        
        return true;
    }
}
?>

