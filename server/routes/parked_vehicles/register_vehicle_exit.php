<?php
require_once '../../db/db.php';
$conn = connectToDatabase();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid id'
    ]);
    exit;
}
$id = (int) $_POST['id'];

$updateSql = "
    UPDATE parked_vehicles
       SET date_end = NOW(),
           value    = CASE
                         WHEN TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33 < 20
                         THEN 15
                         ELSE TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33
                      END
     WHERE id = ?
       AND date_end IS NULL
     LIMIT 1
";

$upd = $conn->prepare($updateSql);
$upd->bind_param('i', $id);
$upd->execute();

if ($upd->affected_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No record updated (already closed or invalid id)'
    ]);
    exit;
}

$selectSql = "
    SELECT v.plate,
           v.model,
           v.color,
           pv.date_start,
           pv.date_end,
           pv.value
      FROM parked_vehicles AS pv
      JOIN vehicles        AS v ON v.id = pv.vehicle_id
     WHERE pv.id = ?
     LIMIT 1
";

$sel = $conn->prepare($selectSql);
$sel->bind_param('i', $id);
$sel->execute();

$result = $sel->get_result();
$summary = $result->fetch_assoc();

if (!$summary) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Updated but could not retrieve summary data'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Vehicle exit registered successfully',
    'data' => $summary
]);
