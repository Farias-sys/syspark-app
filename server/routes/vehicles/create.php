<?php
// api/vehicles/create.php  – add a vehicle for the logged-in user

require_once '../../db/db.php';
session_start();

/* ── Auth guard ───────────────────────────── */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

/* ── Validate input ───────────────────────── */
$plate = strtoupper(trim($_POST['plate'] ?? ''));
$model = trim($_POST['model'] ?? '');
$color = trim($_POST['color'] ?? '');

if ($plate === '' || $model === '' || $color === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing one of required fields: plate, model, color'
    ]);
    exit;
}

/* ── DB work ──────────────────────────────── */
$conn = connectToDatabase();

/* 1. ensure plate is unique for this user */
$dup = $conn->prepare(
    "SELECT id FROM vehicles WHERE user_id = ? AND plate = ? LIMIT 1"
);
$dup->bind_param('is', $user_id, $plate);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You already have a vehicle with that plate.'
    ]);
    exit;
}

/* 2. insert */
$ins = $conn->prepare(
    "INSERT INTO vehicles (user_id, plate, model, color)
     VALUES (?, ?, ?, ?)"
);
$ins->bind_param('isss', $user_id, $plate, $model, $color);
$ok = $ins->execute();

if ($ok) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Vehicle created successfully',
        'data' => [
            'id' => $ins->insert_id,
            'plate' => $plate,
            'model' => $model,
            'color' => $color,
        ],
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB error: ' . $conn->error,
    ]);
}
