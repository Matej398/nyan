<?php
// This script will import the database schema and data
// WARNING: This will DROP and recreate the leaderboard table!

header('Content-Type: text/html');
echo "<h1>Database Import Tool</h1>";

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
    
    // Drop existing table if it exists
    $db->query("DROP TABLE IF EXISTS `leaderboard`");
    echo "<p>Dropped existing leaderboard table.</p>";
    
    // Create table
    $create_table_sql = "CREATE TABLE `leaderboard` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(15) NOT NULL,
      `score` int(11) NOT NULL,
      `timestamp` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    if ($db->query($create_table_sql)) {
        echo "<p>Created leaderboard table successfully.</p>";
    } else {
        throw new Exception("Error creating table: " . $db->error);
    }
    
    // Insert sample data
    $sample_data = [
        ['matej', 1],
        ['matej', 6],
        ['doris', 6],
        ['matej', 16],
        ['matej', 20],
        ['MATEJ', 19],
        ['Nyanonymous', 10],
        ['Nyanonymous', 0],
        ['MATEJ', 42],
        ['MATEJ', 109],
        ['Nyanonymous', 0],
        ['Nyanonymous', 0],
        ['Nyanonymous', 0],
        ['Nyanonymous', 3],
        ['Doris', 10],
        ['Doris', 4],
        ['Nyanonymous', 0],
        ['Nyanonymous', 6],
        ['DORIS', 31],
        ['Nyanonymous', 0],
        ['Nyanonymous', 7],
        ['Nyanonymous', 15],
        ['Nyanonymous', 3],
        ['Nyanonymous', 12],
        ['Nyanonymous', 8],
        ['Nyanonymous', 5],
        ['Nyanonymous', 21]
    ];
    
    $stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $score);
    
    $success_count = 0;
    foreach ($sample_data as $row) {
        $name = $row[0];
        $score = $row[1];
        if ($stmt->execute()) {
            $success_count++;
        }
    }
    
    echo "<p>Inserted $success_count sample records into the leaderboard table.</p>";
    
    $stmt->close();
    $db->close();
    
    echo "<p style='color:green'>Database import completed successfully!</p>";
    echo "<p><a href='test_db.php'>Click here to test the database connection</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 