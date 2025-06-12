<?php
require_once '../../db/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated',
    ]);
    exit;
}
$user_id = $_SESSION['user_id'];

if (empty($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Operation ID is required to delete an entry',
    ]);
    exit;
}
$entry_id = intval($_POST['id']);

$conn = connectToDatabase();

$stmt = $conn->prepare(
    "DELETE FROM parked_vehicles WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $entry_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Parking record deleted successfully',
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Record not found or you do not have permission',
    ]);
}

$stmt->close();
$conn->close();
