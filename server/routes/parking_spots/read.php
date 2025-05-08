<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$sql = "
    SELECT
        id,
        status,
        active,
        in_use_by,
        date_created
    FROM parking_spots
    ORDER BY id
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB error: ' . mysqli_error($conn)
    ]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['active'] = (int) $row['active'];
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
