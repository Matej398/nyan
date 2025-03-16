<?php
// Database configuration
function getDbConnection() {
    $host = 'localhost';
    $username = 'u775386788_nyanuser';
    $database = 'u775386788_nyan';
    $password = 'NyanCat2024!';
    
    $db = new mysqli($host, $username, $password, $database);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    return $db;
}
?> 