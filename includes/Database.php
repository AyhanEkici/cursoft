<?php
/**
 * Database Connection Class
 * Handles all database operations using PDO
 * Supports both MySQL (local) and PostgreSQL (Render)
 */

class Database {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $driver;
    private $conn;
    
    public function __construct() {
        // Check if we're on Render (PostgreSQL) or local (MySQL)
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // Render PostgreSQL connection
            $this->parseDatabaseUrl($dbUrl);
            $this->driver = 'pgsql';
        } else {
            // Local MySQL connection
            $this->host = getenv('DB_HOST') ?: 'localhost';
            $this->dbname = getenv('DB_NAME') ?: 'cursoft';
            $this->username = getenv('DB_USER') ?: 'root';
            $this->password = getenv('DB_PASS') ?: '';
            $this->driver = 'mysql';
        }
        
        try {
            if ($this->driver === 'pgsql') {
                // PostgreSQL connection
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
                $this->conn = new PDO(
                    $dsn,
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } else {
                // MySQL connection
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            }
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    /**
     * Parse Render DATABASE_URL
     * Format: postgresql://user:password@host:port/database
     */
    private function parseDatabaseUrl($url) {
        $parsed = parse_url($url);
        
        $this->host = $parsed['host'] ?? 'localhost';
        $this->port = $parsed['port'] ?? 5432;
        $this->dbname = ltrim($parsed['path'] ?? '/cursoft', '/');
        $this->username = $parsed['user'] ?? 'cursoft_user';
        $this->password = $parsed['pass'] ?? '';
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function getDriver() {
        return $this->driver;
    }
    
    /**
     * Execute query with parameters
     * Handles MySQL/PostgreSQL differences
     */
    public function query($sql, $params = []) {
        // Convert MySQL-specific syntax to PostgreSQL if needed
        if ($this->driver === 'pgsql') {
            $sql = $this->convertToPostgreSQL($sql);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params)->fetch();
        return $result ? $result : null;
    }
    
    public function lastInsertId($sequence = null) {
        if ($this->driver === 'pgsql') {
            if ($sequence) {
                return $this->conn->lastInsertId($sequence);
            }
            // Try to get last insert id from common sequences
            $result = $this->conn->query("SELECT lastval()");
            return $result->fetchColumn();
        }
        return $this->conn->lastInsertId();
    }
    
    /**
     * Convert MySQL SQL to PostgreSQL
     */
    private function convertToPostgreSQL($sql) {
        $pgSql = $sql;
        
        // Replace backticks with double quotes
        $pgSql = preg_replace('/`([^`]+)`/', '"$1"', $pgSql);
        
        // Convert AUTO_INCREMENT to SERIAL (handled in schema)
        // Convert NOW() - both support it
        // Convert LIMIT/OFFSET - both support it
        
        // Remove MySQL-specific syntax
        $pgSql = preg_replace('/ENGINE=\w+/i', '', $pgSql);
        $pgSql = preg_replace('/DEFAULT CHARSET=\w+/i', '', $pgSql);
        $pgSql = preg_replace('/COLLATE \w+/i', '', $pgSql);
        
        // Convert boolean
        $pgSql = preg_replace('/tinyint\(1\)/i', 'BOOLEAN', $pgSql);
        
        return $pgSql;
    }
}
