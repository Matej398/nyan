<?php
header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/db_config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['score'])) {
    echo json_encode(['success' => false, 'error' => 'Missing name or score']);
    exit;
}

$name = $data['name'];
$score = intval($data['score']);

// Get database connection
$db = getDbConnection();

// Prepare and execute the query
$stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
$stmt->bind_param("si", $name, $score);

$success = $stmt->execute();

$stmt->close();
$db->close();

echo json_encode(['success' => $success]);
?>