<?php
// api/vehicle.php
// Deletes ONE parked_vehicles row that belongs to the logged-in user.

require_once '../../db/db.php';
session_start();

/* -------------------------------------------------
   1.  Authentication guard
   ------------------------------------------------- */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated',
    ]);
    exit;
}
$user_id = $_SESSION['user_id'];

/* -------------------------------------------------
   2.  Validate input
   ------------------------------------------------- */
if (empty($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Operation ID is required to delete an entry',
    ]);
    exit;
}
$entry_id = intval($_POST['id']);         // cast = cheap sanitization

/* -------------------------------------------------
   3.  Delete (only) the record owned by this user
   ------------------------------------------------- */
$conn = connectToDatabase();

$stmt = $conn->prepare(
    "DELETE FROM parked_vehicles
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $entry_id, $user_id);
$stmt->execute();

/* -------------------------------------------------
   4.  Response
   ------------------------------------------------- */
if ($stmt->affected_rows > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Parking record deleted successfully',
    ]);
} else {
    // Either the row doesn’t exist or it belongs to another user
    echo json_encode([
        'status' => 'error',
        'message' => 'Record not found or you do not have permission',
    ]);
}

$stmt->close();
$conn->close();
