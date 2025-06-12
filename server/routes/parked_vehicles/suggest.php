<?php
require_once '../../db/db.php';
$conn = connectToDatabase();

$q = strtoupper($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT plate, model, color
     FROM vehicles
     WHERE plate LIKE CONCAT('%', ?, '%')
     ORDER BY plate
     LIMIT 10"
);
$stmt->bind_param('s', $q);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode([
    'status' => 'success',
    'data' => $result->fetch_all(MYSQLI_ASSOC)
]);
