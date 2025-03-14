<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'u775386788_nyanuser';
$db_pass = 'PxBeoY5Ei#xB';
$db_name = 'u775386788_nyan';

// Function to get database connection
function getDbConnection() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    return $conn;
}
?> 