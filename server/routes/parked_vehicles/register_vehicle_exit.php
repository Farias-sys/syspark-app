<?php

require_once '../../db/db.php';
$conn = connectToDatabase();


$id = $_POST['id'] ?? null;
$date_end = $_POST['date_end'] ?? null;
$value = $_POST['value'] ?? null;

if (!$id || !$date_end || !$value) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing one of required fields: id, date_end, value'
    ]);
    exit;
}

// perform update
$sql = "
    UPDATE parked_vehicles
       SET date_end = '$date_end',
           invoice  = '$value'
     WHERE id = '$id'
";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_affected_rows($conn) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Vehicle exit registered successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => mysqli_error($conn) ?: 'No record updated (invalid id?)'
    ]);
}
