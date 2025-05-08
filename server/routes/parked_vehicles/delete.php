<?php
// api/vehicle.php

require_once '../../db/db.php';
$conn = connectToDatabase();

// Read raw request body & decode JSON
$body = file_get_contents('php://input');
$data = json_decode($body, true);


if (empty($data['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Operation ID is required to delete an entry'
    ]);
    exit;
}

$entry_id = $data['id'];

mysqli_query(
    $conn,
    "DELETE FROM parked_vehicles WHERE id = '$entry_id'"
);

echo json_encode([
    'status' => 'success',
    'message' => 'Vehicle and its parking records deleted successfully'
]);