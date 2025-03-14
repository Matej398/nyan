<?php
header('Content-Type: text/html');
echo "<h1>SQL File Import Tool</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'u775386788_nyanuser';
$db_pass = 'PxBeoY5Ei#xB';
$db_name = 'u775386788_nyan';

try {
    // Connect to database
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    echo "<p>Connected to database successfully.</p>";
    
    // Read SQL file
    $sql_file = 'nyan_database.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    if (!$sql) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "<p>Read SQL file successfully.</p>";
    
    // Split SQL file into individual queries
    $queries = explode(';', $sql);
    $success_count = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        if ($db->query($query)) {
            $success_count++;
        } else {
            echo "<p style='color:orange'>Warning: " . htmlspecialchars($db->error) . "</p>";
        }
    }
    
    echo "<p>Executed $success_count SQL queries successfully.</p>";
    $db->close();
    
    echo "<p style='color:green'>SQL file import completed!</p>";
    echo "<p><a href='test_db.php'>Click here to test the database connection</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 