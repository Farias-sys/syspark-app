<?php
// api/register_vehicle_exit.php  – close a parking session (for THIS user)

require_once '../../db/db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

/* ---------- Auth guard ---------- */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

/* ---------- Input ---------- */
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid id']);
    exit;
}
$id = intval($_POST['id']);

/* ---------- Update (close ticket) ---------- */
$conn = connectToDatabase();
$updateSql = "
    UPDATE parked_vehicles
       SET date_end = NOW(),
           value    = CASE
                         WHEN TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33 < 20
                         THEN 15
                         ELSE TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33
                      END
     WHERE id = ? AND user_id = ? AND date_end IS NULL
     LIMIT 1
";
$upd = $conn->prepare($updateSql);
$upd->bind_param('ii', $id, $user_id);
$upd->execute();

if ($upd->affected_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No record updated (already closed or invalid id)'
    ]);
    exit;
}

/* ---------- Fetch summary ---------- */
$selectSql = "
    SELECT v.plate,
           v.model,
           v.color,
           pv.date_start,
           pv.date_end,
           pv.value
      FROM parked_vehicles AS pv
      JOIN vehicles        AS v ON v.id = pv.vehicle_id
     WHERE pv.id = ? AND pv.user_id = ?
     LIMIT 1
";
$sel = $conn->prepare($selectSql);
$sel->bind_param('ii', $id, $user_id);
$sel->execute();
$summary = $sel->get_result()->fetch_assoc();

/* ---------- Response ---------- */
echo json_encode([
    'status' => 'success',
    'message' => 'Vehicle exit registered successfully',
    'data' => $summary
]);
