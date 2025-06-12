<?php
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
    "SELECT id,
          spot_number,
          status,
          active,
          in_use_by,
          date_created
     FROM parking_spots
    WHERE user_id = ?
 ORDER BY spot_number"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();

$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($data as &$row)
    $row['active'] = (int) $row['active'];

echo json_encode(['status' => 'success', 'data' => $data]);
