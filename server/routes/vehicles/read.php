<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$sql = "
    SELECT
        id,
        plate,
        model,
        color,
        date_created
    FROM vehicles
    ORDER BY plate
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
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
