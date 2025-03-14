<?php
header('Content-Type: text/html');
echo "<h1>Leaderboard Setup</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'u775386788_nyanuser';
$db_pass = 'PxBeoY5Ei#xB';
$db_name = 'u775386788_nyan';

try {
    // Step 1: Create the database if it doesn't exist
    $root_conn = new mysqli($db_host, 'root', '');
    if ($root_conn->connect_error) {
        throw new Exception("Could not connect as root: " . $root_conn->connect_error);
    }
    
    echo "<p>Connected to MySQL server as root.</p>";
    
    // Create database
    if ($root_conn->query("CREATE DATABASE IF NOT EXISTS `$db_name`")) {
        echo "<p>Database created or already exists.</p>";
    } else {
        throw new Exception("Failed to create database: " . $root_conn->error);
    }
    
    // Create user if it doesn't exist
    $create_user_sql = "CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_pass'";
    if ($root_conn->query($create_user_sql)) {
        echo "<p>User created or already exists.</p>";
    } else {
        throw new Exception("Failed to create user: " . $root_conn->error);
    }
    
    // Grant privileges
    $grant_sql = "GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_user'@'localhost'";
    if ($root_conn->query($grant_sql)) {
        echo "<p>Privileges granted to user.</p>";
    } else {
        throw new Exception("Failed to grant privileges: " . $root_conn->error);
    }
    
    $root_conn->query("FLUSH PRIVILEGES");
    $root_conn->close();
    
    // Step 2: Connect to the database with the user
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    echo "<p>Connected to database as $db_user.</p>";
    
    // Step 3: Create the leaderboard table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `leaderboard` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(15) NOT NULL,
      `score` int(11) NOT NULL,
      `timestamp` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    if ($db->query($create_table_sql)) {
        echo "<p>Leaderboard table created successfully.</p>";
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
            echo "<p>Added a test record successfully.</p>";
        } else {
            echo "<p>Warning: Failed to add test record: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p>The leaderboard table already has " . $row['count'] . " records.</p>";
    }
    
    $db->close();
    
    echo "<div style='background-color:#dff0d8; color:#3c763d; padding:15px; border-radius:4px; margin-top:20px;'>";
    echo "<h2>Setup Completed Successfully!</h2>";
    echo "<p>The leaderboard is now ready to use.</p>";
    echo "<p><a href='test_db.php'>Click here to test the database connection</a></p>";
    echo "<p><a href='index.html'>Click here to play the game</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color:#f2dede; color:#a94442; padding:15px; border-radius:4px; margin-top:20px;'>";
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>Alternative Setup Instructions:</h3>";
    echo "<p>If you don't have root access to MySQL, follow these steps:</p>";
    echo "<ol>";
    echo "<li>Create the database <code>$db_name</code> using your hosting control panel</li>";
    echo "<li>Create a user <code>$db_user</code> with password <code>$db_pass</code></li>";
    echo "<li>Grant the user all privileges on the database</li>";
    echo "<li>Run the following SQL to create the leaderboard table:</li>";
    echo "</ol>";
    
    echo "<pre style='background:#f8f8f8; padding:10px; border-radius:4px; overflow:auto;'>";
    echo htmlspecialchars($create_table_sql);
    echo "</pre>";
    
    echo "</div>";
}
?> 