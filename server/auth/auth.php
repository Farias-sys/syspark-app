<?php

require_once '../db/db.php';

$conn = connectToDatabase();

$login = $_POST['login'];
$password = md5($_POST['password']);

$check = mysqli_query($conn, "SELECT * FROM user WHERE login = '$login' AND password = '$password'");

if (mysqli_num_rows($check) > 0) {
    $user = mysqli_fetch_assoc($check);
    session_start();
    $_SESSION['user'] = $user['login'];
    $_SESSION['id'] = $user['id'];
    echo json_encode(array('status' => 'success', 'message' => 'Login successful'));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid login or password'));
}