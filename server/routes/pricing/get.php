<?php
require_once '../../db/db.php';
session_start();
$userId = $_SESSION['user_id'];

$conn = connectToDatabase();
$stmt = $conn->prepare(
    "SELECT price_per_min, fixed_fee
     FROM pricing_rules
    WHERE user_id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => $result ?: ['price_per_min' => 0.33, 'fixed_fee' => 15.00]
]);
