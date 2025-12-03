<?php
header('Content-Type: application/json');

try {
    // Include database configuration
    require_once __DIR__ . '/db_config.php';

    // Get database connection
    $db = getDbConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $result = $db->query("SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 5");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $db->error);
    }
    
    $leaderboard = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
    $result->free();
    $db->close();
    
    echo json_encode($leaderboard);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>