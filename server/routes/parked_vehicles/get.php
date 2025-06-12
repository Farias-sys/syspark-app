<?php
// api/get.php  – return vehicle info (plate, model, color) for THIS user

require_once '../../db/db.php';
session_start();

/* ---------- Auth guard ---------- */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

/* ---------- Input ---------- */
$plate = strtoupper(trim($_GET['plate'] ?? ''));
if ($plate === '') {
    echo json_encode(['status' => 'error', 'message' => 'plate is required']);
    exit;
}

/* ---------- Query ---------- */
$conn = connectToDatabase();
$stmt = $conn->prepare(
    "SELECT plate, model, color
       FROM vehicles
      WHERE plate = ? AND user_id = ?
      LIMIT 1"
);
$stmt->bind_param('si', $plate, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

/* ---------- Response ---------- */
echo json_encode([
    'status' => $data ? 'success' : 'not_found',
    'data' => $data
]);
