<?php
require_once '../db/db.php';
$conn = connectToDatabase();

$login = $_POST['login'] ?? '';
$password = md5($_POST['password'] ?? '');

$sql = "SELECT id, login, name FROM user WHERE login = ? AND password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $login, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login'] = $user['login'];

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'data' => [
            'id' => $user['id'],
            'login' => $user['login'],
            'name' => $user['name']
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid login or password'
    ]);
}
