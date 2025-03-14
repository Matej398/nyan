<?php
header('Content-Type: application/json');
// Include database configuration
require_once 'db_config.php';

// Get database connection
$db = getDbConnection();

$name = $_POST['name'] ?? 'Nyanonymous';
// Limit name to 15 characters as per database schema
$name = substr($name, 0, 15);
$score = (int)($_POST['score'] ?? 0);
$stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
$stmt->bind_param("si", $name, $score);
$stmt->execute();
$stmt->close();
$db->close();
echo json_encode(['success' => true]);
?>