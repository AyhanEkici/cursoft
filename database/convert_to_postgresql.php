<?php
/**
 * MySQL to PostgreSQL Converter
 * Run this locally to convert your MySQL schema to PostgreSQL
 * Access: http://localhost/cursoft/database/convert_to_postgresql.php
 */

session_start();

// Security check - only allow localhost
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($clientIP, $allowedIPs) && strpos($clientIP, '192.168.') !== 0 && strpos($clientIP, '10.') !== 0) {
    die('Access denied. Run this locally only.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>MySQL to PostgreSQL Converter</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .alert { padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔄 MySQL to PostgreSQL Converter</h1>
    <p>This tool converts your MySQL database schema to PostgreSQL format for Render deployment.</p>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $database = $_POST['database'] ?? 'cursoft';
    
    try {
        $conn = new mysqli($host, $user, $pass, $database);
        
        if ($conn->connect_error) {
            throw new Exception("MySQL Connection failed: " . $conn->connect_error);
        }
        
        echo "<div class='alert success'><h3>✅ Connected to MySQL database: $database</h3></div>";
        
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "<p>Found " . count($tables) . " tables: " . implode(', ', $tables) . "</p>";
        
        // Generate PostgreSQL compatible SQL
        $pg_sql = "-- Cursoft PostgreSQL Migration\n";
        $pg_sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $pg_sql .= "-- Source: MySQL database '$database'\n\n";
        
        foreach ($tables as $table) {
            $pg_sql .= "-- ============================================\n";
            $pg_sql .= "-- Table: $table\n";
            $pg_sql .= "-- ============================================\n";
            $pg_sql .= "DROP TABLE IF EXISTS \"$table\" CASCADE;\n\n";
            
            // Get table structure
            $create_result = $conn->query("SHOW CREATE TABLE `$table`");
            $create_row = $create_result->fetch_assoc();
            $mysql_sql = $create_row['Create Table'];
            
            // Convert to PostgreSQL
            $pg_create = convertToPostgreSQL($mysql_sql, $table);
            $pg_sql .= $pg_create . ";\n\n";
            
            // Export data
            $data_result = $conn->query("SELECT * FROM `$table`");
            if ($data_result->num_rows > 0) {
                $pg_sql .= "-- Data for $table (" . $data_result->num_rows . " rows)\n";
                while ($row = $data_result->fetch_assoc()) {
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
                    $colStr = '"' . implode('", "', $columns) . '"';
                    $valStr = implode(', ', array_map(function($val) use ($conn) {
                        if ($val === null) return 'NULL';
                        return "'" . $conn->real_escape_string($val) . "'";
                    }, $values));
                    
                    $pg_sql .= "INSERT INTO \"$table\" ($colStr) VALUES ($valStr);\n";
                }
                $pg_sql .= "\n";
            }
        }
        
        // Save to file
        $filename = 'postgres-export.sql';
        file_put_contents($filename, $pg_sql);
        
        echo "<div class='alert success'>";
        echo "<h3>✅ Migration file created!</h3>";
        echo "<p><strong>File:</strong> <a href='$filename' download>$filename</a></p>";
        echo "<p><strong>Tables exported:</strong> " . count($tables) . "</p>";
        echo "<p><strong>File size:</strong> " . number_format(filesize($filename)) . " bytes</p>";
        echo "</div>";
        
        echo "<h3>Preview (first 2000 characters):</h3>";
        echo "<pre>" . htmlspecialchars(substr($pg_sql, 0, 2000)) . "...\n[truncated]</pre>";
        
        echo "<div class='alert warning'>";
        echo "<h4>⚠️ Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Download the <code>$filename</code> file</li>";
        echo "<li>After deploying to Render, connect to your PostgreSQL database</li>";
        echo "<li>Run the SQL file in your Render PostgreSQL database</li>";
        echo "<li>Some MySQL-specific functions may need manual adjustment</li>";
        echo "</ol>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert error'>";
        echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
        echo "</div>";
    }
} else {
    // Show form
    ?>
    <form method="POST">
        <div class="form-group">
            <label>Database Name:</label>
            <input type="text" name="database" value="cursoft" required>
        </div>
        
        <div class="form-group">
            <label>Host:</label>
            <input type="text" name="host" value="localhost">
        </div>
        
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="user" value="root">
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="pass" value="">
        </div>
        
        <button type="submit">Generate PostgreSQL Migration</button>
    </form>
    
    <div class="alert warning">
        <h4>⚠️ Important Notes:</h4>
        <ul>
            <li>Run this on your local XAMPP server only</li>
            <li>This creates a PostgreSQL-compatible SQL file</li>
            <li>You'll import this into Render PostgreSQL after deployment</li>
            <li>Some MySQL-specific functions may need manual adjustment</li>
            <li>Test the converted SQL before deploying to production</li>
        </ul>
    </div>
    <?php
}

function convertToPostgreSQL($mysql_sql, $tableName) {
    $pg_sql = $mysql_sql;
    
    // Remove backticks and replace with double quotes
    $pg_sql = preg_replace('/`([^`]+)`/', '"$1"', $pg_sql);
    
    // Convert data types
    $pg_sql = preg_replace('/int\(\d+\)/i', 'INTEGER', $pg_sql);
    $pg_sql = preg_replace('/tinyint\(1\)/i', 'BOOLEAN', $pg_sql);
    $pg_sql = preg_replace('/varchar\((\d+)\)/i', 'VARCHAR($1)', $pg_sql);
    $pg_sql = preg_replace('/text(\(\d+\))?/i', 'TEXT', $pg_sql);
    $pg_sql = preg_replace('/datetime/i', 'TIMESTAMP', $pg_sql);
    $pg_sql = preg_replace('/timestamp/i', 'TIMESTAMP', $pg_sql);
    
    // Convert AUTO_INCREMENT to SERIAL
    if (preg_match('/AUTO_INCREMENT/i', $pg_sql)) {
        // Find the column with AUTO_INCREMENT
        $pg_sql = preg_replace('/(\w+)\s+INTEGER\s+AUTO_INCREMENT/i', '$1 SERIAL', $pg_sql);
        $pg_sql = preg_replace('/AUTO_INCREMENT/i', '', $pg_sql);
    }
    
    // Remove MySQL-specific options
    $pg_sql = preg_replace('/ENGINE=\w+/i', '', $pg_sql);
    $pg_sql = preg_replace('/DEFAULT CHARSET=\w+/i', '', $pg_sql);
    $pg_sql = preg_replace('/COLLATE \w+/i', '', $pg_sql);
    
    // Remove trailing commas before closing parenthesis
    $pg_sql = preg_replace('/,\s*\)/', ')', $pg_sql);
    
    // Convert PRIMARY KEY syntax
    $pg_sql = preg_replace('/PRIMARY KEY\s*\(([^)]+)\)/i', 'PRIMARY KEY ($1)', $pg_sql);
    
    // Convert INDEX syntax
    $pg_sql = preg_replace('/KEY\s+(\w+)\s*\(([^)]+)\)/i', '', $pg_sql);
    $pg_sql = preg_replace('/INDEX\s+(\w+)\s*\(([^)]+)\)/i', '', $pg_sql);
    
    // Extract CREATE TABLE statement
    if (preg_match('/CREATE TABLE[^;]+/is', $pg_sql, $matches)) {
        $pg_sql = $matches[0];
    }
    
    return $pg_sql;
}
?>
</body>
</html>

