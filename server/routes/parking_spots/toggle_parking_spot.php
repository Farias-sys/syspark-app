<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$res = mysqli_query($conn, "SELECT active FROM parking_spots WHERE id = '$id'");
if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parking spot not found.'
    ]);
    exit;
}

$row = mysqli_fetch_assoc($res);
$current = (int) $row['active'];
$newActive = $current === 1 ? 0 : 1;

$upd = mysqli_query(
    $conn,
    "UPDATE parking_spots SET active = '$newActive' WHERE id = '$id'"
);

if ($upd && mysqli_affected_rows($conn) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Parking spot ' . ($newActive ? 'activated' : 'deactivated') . '.',
        'id' => (int) $id,
        'new_active' => $newActive
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => mysqli_error($conn) ?: 'Failed to update parking spot.'
    ]);
}
