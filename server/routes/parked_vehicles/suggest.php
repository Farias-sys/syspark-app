<?php
require_once '../../db/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$q = strtoupper(trim($_GET['q'] ?? ''));

if (strlen($q) < 2) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

$conn = connectToDatabase();
$stmt = $conn->prepare(
    "SELECT plate, model, color
     FROM vehicles
     WHERE user_id = ?
       AND plate LIKE CONCAT('%', ?, '%')
     ORDER BY plate
     LIMIT 10"
);

$stmt->bind_param('is', $user_id, $q);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode([
    'status' => 'success',
    'data' => $result->fetch_all(MYSQLI_ASSOC),
]);
