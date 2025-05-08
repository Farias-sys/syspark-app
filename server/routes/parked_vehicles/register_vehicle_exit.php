<?php

require_once '../../db/db.php';
$conn = connectToDatabase();


$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing one of required field: id'
    ]);
    exit;
}

$id = mysqli_real_escape_string($conn, $id);

$sql = "
    UPDATE parked_vehicles
        SET date_end = NOW(),
            value    = CASE 
                            WHEN TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33 < 20 
                            THEN 15 
                            ELSE TIMESTAMPDIFF(MINUTE, date_start, NOW()) * 0.33 
                        END
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
