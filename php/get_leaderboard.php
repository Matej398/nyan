<?php
header('Content-Type: application/json');
// Include database configuration
require_once __DIR__ . '/db_config.php';

// Get database connection
$db = getDbConnection();

$result = $db->query("SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 5");
$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}
$result->free();
$db->close();
echo json_encode($leaderboard);
?>