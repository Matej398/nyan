<?php
// Simple setup script to help with database setup
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyan Game Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #ff00ff;
            text-align: center;
        }
        .step {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .step h2 {
            margin-top: 0;
            color: #003366;
        }
        pre {
            background-color: #333;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Nyan Game Setup</h1>
    
    <div class="step">
        <h2>Step 1: Database Configuration</h2>
        <p>Current database configuration:</p>
        <pre><?php
            require_once 'db_config.php';
            $config = [
                'host' => 'localhost',
                'username' => 'nyan_user',
                'password' => 'nyan_password',
                'database' => 'nyan_game'
            ];
            echo "Host: " . $config['host'] . "\n";
            echo "Username: " . $config['username'] . "\n";
            echo "Password: " . $config['password'] . "\n";
            echo "Database: " . $config['database'] . "\n";
        ?></pre>
    </div>
    
    <div class="step">
        <h2>Step 2: Database Connection Test</h2>
        <?php
        try {
            $db = getDbConnection();
            echo '<p class="success">✓ Database connection successful!</p>';
            $db->close();
        } catch (Exception $e) {
            echo '<p class="error">✗ Database connection failed: ' . $e->getMessage() . '</p>';
            echo '<p>Please make sure the database is set up correctly. You can run the SQL script manually:</p>';
            echo '<pre>' . htmlspecialchars(file_get_contents('setup_database.sql')) . '</pre>';
        }
        ?>
    </div>
    
    <div class="step">
        <h2>Step 3: Leaderboard Table Check</h2>
        <?php
        try {
            $db = getDbConnection();
            $result = $db->query("SHOW TABLES LIKE 'leaderboard'");
            if ($result->num_rows > 0) {
                echo '<p class="success">✓ Leaderboard table exists!</p>';
                
                // Check if there are records
                $countResult = $db->query("SELECT COUNT(*) as count FROM leaderboard");
                $count = $countResult->fetch_assoc()['count'];
                echo '<p>There are currently ' . $count . ' records in the leaderboard table.</p>';
                
                if ($count > 0) {
                    echo '<p>Top 5 scores:</p>';
                    $topScores = $db->query("SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 5");
                    echo '<ul>';
                    while ($row = $topScores->fetch_assoc()) {
                        echo '<li>' . htmlspecialchars($row['name']) . ': ' . $row['score'] . '</li>';
                    }
                    echo '</ul>';
                }
            } else {
                echo '<p class="error">✗ Leaderboard table does not exist!</p>';
                echo '<p>Please run the SQL script to create the table.</p>';
            }
            $db->close();
        } catch (Exception $e) {
            echo '<p class="error">✗ Error checking leaderboard table: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <div class="step">
        <h2>Step 4: API Endpoints</h2>
        <p>The following API endpoints should be accessible:</p>
        <ul>
            <li><a href="/nyan/php/get_leaderboard.php" target="_blank">Get Leaderboard API</a></li>
            <li>Save Score API (POST to /nyan/php/save_score.php)</li>
        </ul>
    </div>
    
    <div class="step">
        <h2>Step 5: Game Access</h2>
        <p>The game should be accessible at:</p>
        <p><a href="/nyan/" target="_blank">Nyan Game</a></p>
    </div>
</body>
</html> 