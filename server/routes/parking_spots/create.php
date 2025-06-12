<?php
require_once '../../db/db.php';
session_start();

// Auth guard
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated'
    ]);
    exit;
}
$user_id = $_SESSION['user_id'];

// Validate POST data
$count = $_POST['count'] ?? null;
if (!is_numeric($count) || (int) $count < 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing `count` (must be a positive integer).'
    ]);
    exit;
}
$count = (int) $count;

// DB: bulk insert
$conn = connectToDatabase();

try {
    $placeholders = implode(',', array_fill(0, $count, '(?)'));
    $sql = "INSERT INTO parking_spots (user_id) VALUES $placeholders";
    $stmt = $conn->prepare($sql);

    $types = str_repeat('i', $count);
    $params = array_merge([$types], array_fill(0, $count, $user_id));

    $refs = [];
    foreach ($params as $k => $v) {
        $refs[$k] = &$params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => "$count parking spot(s) created."
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
