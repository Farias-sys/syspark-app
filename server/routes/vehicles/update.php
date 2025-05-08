<?php

require_once '../../db/db.php';
$conn = connectToDatabase();

$id = $_POST['id'] ?? null;
$plate = trim($_POST['plate'] ?? '');
$model = trim($_POST['model'] ?? '');
$color = trim($_POST['color'] ?? '');

if (!is_numeric($id) || $plate === '' || $model === '' || $color === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid fields: id, plate, model, and color are required.'
    ]);
    exit;
}
$id = (int) $id;

$plateEsc = mysqli_real_escape_string($conn, $plate);
$modelEsc = mysqli_real_escape_string($conn, $model);
$colorEsc = mysqli_real_escape_string($conn, $color);

$check = mysqli_query($conn, "SELECT plate FROM vehicles WHERE id = {$id}");
if (!$check || mysqli_num_rows($check) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vehicle not found.'
    ]);
    exit;
}

$current = mysqli_fetch_assoc($check);
$currentPlate = $current['plate'];

if ($plate !== $currentPlate) {
    $dup = mysqli_query(
        $conn,
        "SELECT id FROM vehicles WHERE plate = '{$plateEsc}' AND id != {$id}"
    );
    if ($dup && mysqli_num_rows($dup) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Another vehicle with this plate already exists.'
        ]);
        exit;
    }
}

$sql = "
    UPDATE vehicles
       SET plate = '{$plateEsc}',
           model = '{$modelEsc}',
           color = '{$colorEsc}'
     WHERE id    = {$id}
";
$res = mysqli_query($conn, $sql);

if ($res && mysqli_affected_rows($conn) >= 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Vehicle updated successfully.',
        'data' => [
            'id' => $id,
            'plate' => $plate,
            'model' => $model,
            'color' => $color
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}
