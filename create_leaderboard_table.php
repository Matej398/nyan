<?php
header('Content-Type: text/html');
echo "<h1>Create Leaderboard Table</h1>";

// Include database configuration
require_once 'db_config.php';

try {
    // Connect to database
    $db = getDbConnection();
    echo "<p>Connected to database successfully.</p>";
    
    // Create the leaderboard table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `leaderboard` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(15) NOT NULL,
      `score` int(11) NOT NULL,
      `timestamp` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    if ($db->query($create_table_sql)) {
        echo "<p style='color:green'>Leaderboard table created successfully!</p>";
    } else {
        throw new Exception("Error creating table: " . $db->error);
    }
    
    // Check if the table is empty
    $result = $db->query("SELECT COUNT(*) as count FROM leaderboard");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "<p>The leaderboard table is empty. Adding a test record...</p>";
        
        // Add a test record
        $stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $score);
        
        $name = "TestUser";
        $score = 100;
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>Added a test record successfully!</p>";
        } else {
            echo "<p style='color:orange'>Warning: Failed to add test record: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p>The leaderboard table already has " . $row['count'] . " records.</p>";
    }
    
    $db->close();
    
    echo "<div style='margin-top:20px'>";
    echo "<p><a href='test_db.php'>Click here to test the database connection</a></p>";
    echo "<p><a href='index.html'>Click here to play the game</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>Manual Setup Instructions:</h3>";
    echo "<p>If you're having trouble connecting to the database, follow these steps:</p>";
    echo "<ol>";
    echo "<li>Make sure the database <code>" . htmlspecialchars($db_name) . "</code> exists</li>";
    echo "<li>Make sure the user <code>" . htmlspecialchars($db_user) . "</code> exists and has the correct password</li>";
    echo "<li>Make sure the user has privileges on the database</li>";
    echo "<li>Run the following SQL to create the leaderboard table:</li>";
    echo "</ol>";
    
    echo "<pre style='background:#f8f8f8; padding:10px; border-radius:4px; overflow:auto;'>";
    echo htmlspecialchars($create_table_sql);
    echo "</pre>";
}
?> 