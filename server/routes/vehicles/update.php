<?php
require_once '../../db/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

$id = $_POST['id'] ?? null;
$plate = strtoupper(trim($_POST['plate'] ?? ''));
$model = trim($_POST['model'] ?? '');
$color = trim($_POST['color'] ?? '');

if (!is_numeric($id) || $plate === '' || $model === '' || $color === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid fields: id, plate, model, color are required.'
    ]);
    exit;
}
$id = (int) $id;

$conn = connectToDatabase();

$own = $conn->prepare("SELECT plate FROM vehicles WHERE id = ? AND user_id = ?");
$own->bind_param('ii', $id, $user_id);
$own->execute();
$resOwn = $own->get_result();
if ($resOwn->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Vehicle not found.']);
    exit;
}
$currentPlate = $resOwn->fetch_assoc()['plate'];

if ($plate !== $currentPlate) {
    $dup = $conn->prepare("SELECT id FROM vehicles WHERE user_id = ? AND plate = ? AND id <> ?");
    $dup->bind_param('isi', $user_id, $plate, $id);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Another vehicle with this plate already exists.'
        ]);
        exit;
    }
}

$upd = $conn->prepare("UPDATE vehicles SET plate = ?, model = ?, color = ? WHERE id = ? AND user_id = ?");
$upd->bind_param('sssii', $plate, $model, $color, $id, $user_id);
$upd->execute();

echo json_encode([
    'status' => 'success',
    'message' => 'Vehicle updated successfully.',
    'data' => [
        'id' => $id,
        'plate' => $plate,
        'model' => $model,
        'color' => $color,
    ],
]);
