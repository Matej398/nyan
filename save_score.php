<?php
header('Content-Type: application/json');
// Include database configuration
require_once 'db_config.php';

// Get database connection
$db = getDbConnection();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$name = isset($data['name']) ? $db->real_escape_string($data['name']) : '';
$score = isset($data['score']) ? intval($data['score']) : 0;

if (empty($name) || $score <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Insert score into database
$stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
$stmt->bind_param("si", $name, $score);
$result = $stmt->execute();
$stmt->close();
$db->close();

echo json_encode(['success' => $result]);
?> 