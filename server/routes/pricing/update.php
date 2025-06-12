<?php
require_once '../../db/db.php';
session_start();
$userId = $_SESSION['user_id'];
$pricePerMin = $_POST['price_per_min'] ?? null;
$fixedFee = $_POST['fixed_fee'] ?? null;

if (!is_numeric($pricePerMin) || !is_numeric($fixedFee)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid numbers']);
    exit;
}

$conn = connectToDatabase();
$stmt = $conn->prepare(
    "INSERT INTO pricing_rules (user_id, price_per_min, fixed_fee)
   VALUES (?,?,?)
   ON DUPLICATE KEY UPDATE
     price_per_min = VALUES(price_per_min),
     fixed_fee     = VALUES(fixed_fee)"
);
$stmt->bind_param("idd", $userId, $pricePerMin, $fixedFee);
$stmt->execute();

echo json_encode(['status' => 'success']);
