<?php
/**
 * Path Helper
 * Provides dynamic base paths for local and Render deployment
 */

class PathHelper {
    private static $basePath = null;
    
    /**
     * Get base path (empty on Render, /cursoft on local)
     */
    public static function getBasePath() {
        if (self::$basePath === null) {
            // Check if we're on Render
            if (getenv('RENDER')) {
                self::$basePath = getenv('BASE_PATH') ?: '';
            } else {
                // Local development
                self::$basePath = '/cursoft';
            }
        }
        return self::$basePath;
    }
    
    /**
     * Get full URL path
     */
    public static function url($path = '') {
        $base = self::getBasePath();
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
    
    /**
     * Get asset URL (CSS, JS, images)
     */
    public static function asset($path) {
        return self::url('public/' . ltrim($path, '/'));
    }
    
    /**
     * Get API URL
     */
    public static function api($endpoint = '') {
        return self::url('api/' . ltrim($endpoint, '/'));
    }
    
    /**
     * Get page URL
     */
    public static function page($page) {
        return self::url('pages/' . ltrim($page, '/'));
    }
}

