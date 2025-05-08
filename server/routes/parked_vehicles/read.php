<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$sql = "
  SELECT
    pv.id,
    v.plate,
    v.model,
    v.color    AS cor,
    pv.parking_spot_id AS parking_spot,
    pv.date_start,
    pv.date_end,
    pv.value
  FROM parked_vehicles AS pv
  JOIN vehicles         AS v  ON pv.vehicle_id      = v.id
  JOIN parking_spots    AS ps ON pv.parking_spot_id = ps.id
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB error: ' . mysqli_error($conn)
    ]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
