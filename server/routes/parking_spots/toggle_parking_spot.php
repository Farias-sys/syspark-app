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
if (!is_numeric($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid `id`.']);
    exit;
}
$id = (int) $id;

$conn = connectToDatabase();

/* fetch current active flag */
$sel = $conn->prepare(
    "SELECT active FROM parking_spots
    WHERE id = ? AND user_id = ?"
);
$sel->bind_param('ii', $id, $user_id);
$sel->execute();
$res = $sel->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Parking spot not found.']);
    exit;
}

$current = (int) $res->fetch_assoc()['active'];
$newActive = $current ? 0 : 1;

/* update flag */
$upd = $conn->prepare(
    "UPDATE parking_spots SET active = ? WHERE id = ? AND user_id = ?"
);
$upd->bind_param('iii', $newActive, $id, $user_id);
$upd->execute();

echo json_encode([
    'status' => 'success',
    'message' => 'Parking spot ' . ($newActive ? 'activated' : 'deactivated') . '.',
    'id' => $id,
    'new_active' => $newActive
]);
