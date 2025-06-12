<?php
// api/suggest.php   – autocomplete list of vehicle plates for THIS user

require_once '../../db/db.php';
session_start();

/* ---------- Auth guard ---------- */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$user_id = $_SESSION['user_id'];

/* ---------- Input ---------- */
$q = strtoupper(trim($_GET['q'] ?? ''));
if (strlen($q) < 2) {
    // require at least 2 chars before we start suggesting
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

/* ---------- Query ---------- */
$conn = connectToDatabase();
$stmt = $conn->prepare(
    "SELECT plate, model, color
       FROM vehicles
      WHERE user_id = ?
        AND plate LIKE CONCAT('%', ?, '%')
      ORDER BY plate
      LIMIT 10"
);
/* bind: i = int (user_id), s = string (search term) */
$stmt->bind_param('is', $user_id, $q);
$stmt->execute();
$result = $stmt->get_result();

/* ---------- Response ---------- */
echo json_encode([
    'status' => 'success',
    'data' => $result->fetch_all(MYSQLI_ASSOC),
]);
