<?php
// api/vehicles/read.php  – list vehicles that belong to the user

require_once '../../db/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

$conn = connectToDatabase();
$stmt = $conn->prepare(
    "SELECT id, plate, model, color, date_created
       FROM vehicles
      WHERE user_id = ?
   ORDER BY plate"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

$data = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => 'success', 'data' => $data]);
