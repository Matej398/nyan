<?php
header('Content-Type: application/json');
// Replace with your Hostinger MySQL credentials
$db = new mysqli('mysql.hostinger.com', 'u775386788_nyanuser', 'PxBeoY5Ei#xB', 'u775386788_nyan');
if ($db->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}
$name = $_POST['name'] ?? 'Nyanonymous';
$score = (int)($_POST['score'] ?? 0);
$stmt = $db->prepare("INSERT INTO leaderboard (name, score) VALUES (?, ?)");
$stmt->bind_param("si", $name, $score);
$stmt->execute();
$stmt->close();
$db->close();
echo json_encode(['success' => true]);
?>