<?php
header('Content-Type: application/json');

try {
    // Include database configuration
    require_once 'db_config.php';

    // Get database connection
    $db = getDbConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['score'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing name or score']);
        exit;
    }

    $name = $data['name'];
    $score = intval($data['score']);

    // Prepare and execute the query
    $stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }
    
    $stmt->bind_param("si", $name, $score);
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    $db->close();

    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 