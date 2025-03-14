<?php
// Database configuration
function getDbConnection() {
    $host = 'localhost';
    $username = 'nyan_user';
    $password = 'nyan_password';
    $database = 'nyan_game';
    
    $db = new mysqli($host, $username, $password, $database);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    return $db;
}
?> 