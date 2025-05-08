<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$plate = trim($_POST['plate'] ?? '');
$model = trim($_POST['model'] ?? '');
$color = trim($_POST['color'] ?? '');

if ($plate === '' || $model === '' || $color === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing one of required fields: plate, model, color'
    ]);
    exit;
}

$plate = mysqli_real_escape_string($conn, $plate);
$model = mysqli_real_escape_string($conn, $model);
$color = mysqli_real_escape_string($conn, $color);

// 5) Insert new vehicle
$sql = "
    INSERT INTO vehicles (plate, model, color)
    VALUES ('$plate', '$model', '$color')
";
$result = mysqli_query($conn, $sql);

if ($result) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        'status' => 'success',
        'message' => 'Vehicle created successfully',
        'data' => [
            'id' => (int) $newId,
            'plate' => $plate,
            'model' => $model,
            'color' => $color
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB error: ' . mysqli_error($conn)
    ]);
}
