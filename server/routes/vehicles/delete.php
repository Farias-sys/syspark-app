<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$id = $_POST['id'] ?? null;
if (!is_numeric($id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing `id`.'
    ]);
    exit;
}
$id = mysqli_real_escape_string($conn, $id);

$check = mysqli_query(
    $conn,
    "SELECT id FROM vehicles WHERE id = '$id'"
);
if (!$check || mysqli_num_rows($check) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vehicle not found.'
    ]);
    exit;
}

$del = mysqli_query(
    $conn,
    "DELETE FROM vehicles WHERE id = '$id'"
);

if ($del && mysqli_affected_rows($conn) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => "Vehicle (id={$id}) deleted successfully."
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}
