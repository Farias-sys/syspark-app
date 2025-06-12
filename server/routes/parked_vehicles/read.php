<?php
// api/read.php  – list all parked vehicles that belong to THIS user

require_once '../../db/db.php';
session_start();

/* ---------- Auth guard ---------- */
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
  exit;
}
$user_id = $_SESSION['user_id'];

/* ---------- Query ---------- */
$conn = connectToDatabase();
$sql = "
  SELECT
    pv.id,
    v.plate,
    v.model,
    v.color,
    pv.parking_spot_id AS parking_spot,
    pv.date_start,
    pv.date_end,
    pv.value
  FROM parked_vehicles AS pv
  JOIN vehicles      AS v  ON pv.vehicle_id      = v.id
  JOIN parking_spots AS ps ON pv.parking_spot_id = ps.id
  WHERE pv.user_id = ?
  ORDER BY pv.date_start DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* ---------- Build payload ---------- */
$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

/* ---------- Response ---------- */
echo json_encode([
  'status' => 'success',
  'data' => $data
]);
