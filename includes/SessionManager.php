<?php
/**
 * Session Manager Class
 * Manages user sessions and provides helper methods
 */

require_once __DIR__ . '/Auth.php';

class SessionManager {
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public function requireLogin() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: /cursoft/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    /**
     * Require guest - redirect if already logged in
     */
    public function requireGuest() {
        if ($this->auth->isLoggedIn()) {
            header('Location: /cursoft/pages/dashboard.php');
            exit;
        }
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        return $this->auth->getCurrentUser();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->auth->isLoggedIn();
    }
}

