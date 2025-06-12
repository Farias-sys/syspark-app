<?php
require_once '../../db/db.php';
$conn = connectToDatabase();

$plate = strtoupper($_GET['plate'] ?? '');
if (!$plate) {
    echo json_encode(['status' => 'error', 'message' => 'plate is required']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT plate, model, color
     FROM vehicles
     WHERE plate = ?
     LIMIT 1"
);
$stmt->bind_param('s', $plate);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => $data ? 'success' : 'not_found',
    'data' => $data
]);
