<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$count = $_POST['count'] ?? null;
if (!is_numeric($count) || (int) $count < 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing `count` (must be a positive integer).'
    ]);
    exit;
}
$count = (int) $count;

try {
    $values = array_fill(0, $count, "( )");
    $sql = "INSERT INTO parking_spots () VALUES " . implode(",", $values);
    mysqli_query($conn, $sql);

    echo json_encode([
        'status' => 'success',
        'message' => "{$count} parking spot(s) created."
    ]);

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
