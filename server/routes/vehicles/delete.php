<?php
require_once '../../db/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

$id = $_POST['id'] ?? null;
if (!is_numeric($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing `id`.']);
    exit;
}
$id = (int) $id;

$conn = connectToDatabase();

$chk = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
$chk->bind_param('ii', $id, $user_id);
$chk->execute();
if ($chk->get_result()->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Vehicle not found.']);
    exit;
}

$del = $conn->prepare("DELETE FROM vehicles WHERE id = ? AND user_id = ?");
$del->bind_param('ii', $id, $user_id);
$del->execute();

if ($del->affected_rows > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => "Vehicle (id = $id) deleted successfully."
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $conn->error,
    ]);
}
