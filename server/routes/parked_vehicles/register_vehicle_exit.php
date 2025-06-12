<?php
require_once '../../db/db.php';
session_start();
$conn = connectToDatabase();

$id = $_POST['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$id || !$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing id or user']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT price_per_min, fixed_fee
     FROM pricing_rules
     WHERE user_id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$pricing = $stmt->get_result()->fetch_assoc() ?: ['price_per_min' => 0.33, 'fixed_fee' => 15.00];

$upd = $conn->prepare(
    "UPDATE parked_vehicles
     SET date_end = NOW(),
         value = ROUND(? + TIMESTAMPDIFF(MINUTE, date_start, NOW()) * ?, 2)
     WHERE id = ? AND user_id = ?"
);
$upd->bind_param(
    "diii",
    $pricing['fixed_fee'],
    $pricing['price_per_min'],
    $id,
    $userId
);
$upd->execute();

if ($upd->affected_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Nothing updated']);
    exit;
}

$info = $conn->prepare(
    "SELECT pv.date_start,
            pv.date_end,
            pv.value,
            v.plate, v.model, v.color,
            ps.spot_number
     FROM parked_vehicles pv
     JOIN vehicles v ON v.id = pv.vehicle_id
     JOIN parking_spots ps ON ps.id = pv.parking_spot_id
     WHERE pv.id = ? AND pv.user_id = ?"
);
$info->bind_param("ii", $id, $userId);
$info->execute();
$data = $info->get_result()->fetch_assoc();

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Row not found']);
    exit;
}

echo json_encode(['status' => 'success', 'data' => $data]);
