<?php
header('Content-Type: text/html');
echo "<h1>Database Connection Test</h1>";

try {
    // Include database configuration
    require_once 'db_config.php';
    
    // Try to connect
    $db = getDbConnection();
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Test query
    $result = $db->query("SELECT COUNT(*) as count FROM leaderboard");
    $row = $result->fetch_assoc();
    echo "<p>Found " . $row['count'] . " entries in the leaderboard table.</p>";
    
    // Show table structure
    echo "<h2>Leaderboard Table Structure:</h2>";
    $result = $db->query("DESCRIBE leaderboard");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Close connection
    $db->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 