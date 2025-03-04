<?php
header('Content-Type: application/json');
// Replace with your Hostinger MySQL credentials
$db = new mysqli('mysql.hostinger.com', 'u775386788_nyanuser', 'PxBeoY5Ei#xB', 'u775386788_nyan');
if ($db->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}
$result = $db->query("SELECT name, score FROM leaderboard ORDER BY score DESC LIMIT 5");
$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}
$result->free();
$db->close();
echo json_encode($leaderboard);
?>