<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'u775386788_nyanuser';
$db_pass = 'PxBeoY5Ei#xB';
$db_name = 'u775386788_nyan';

// Function to get database connection
function getDbConnection() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        // Check if this is an API call or a direct page view
        if (strpos($_SERVER['SCRIPT_NAME'], 'get_leaderboard.php') !== false || 
            strpos($_SERVER['SCRIPT_NAME'], 'save_score.php') !== false) {
            // For API calls, return JSON error
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'error' => $e->getMessage()]));
        } else {
            // For direct page views, show HTML error
            echo "<div style='color:red; font-family:Arial,sans-serif; padding:20px; border:1px solid #ddd; margin:20px; border-radius:5px;'>";
            echo "<h2>Database Connection Error</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<h3>Troubleshooting Steps:</h3>";
            echo "<ol>";
            echo "<li>Check if the database exists: <code>" . htmlspecialchars($db_name) . "</code></li>";
            echo "<li>Verify the database user: <code>" . htmlspecialchars($db_user) . "</code></li>";
            echo "<li>Confirm the password is correct</li>";
            echo "<li>Make sure the user has permissions to access the database</li>";
            echo "</ol>";
            echo "</div>";
            die();
        }
    }
}
?> 