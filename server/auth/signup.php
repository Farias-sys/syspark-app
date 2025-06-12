<?php

require_once '../db/db.php';
$conn = connectToDatabase();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';

if (!$name || !$email || !$password || !$confirm) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos.']);
    exit;
}

if ($password !== $confirm) {
    echo json_encode(['status' => 'error', 'message' => 'As senhas não conferem.']);
    exit;
}

$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$hash = md5($password);

$exists = mysqli_query($conn, "SELECT id FROM user WHERE login = '$email'");
if (mysqli_num_rows($exists) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Este e-mail já está cadastrado.']);
    exit;
}

$insert = mysqli_query($conn, "INSERT INTO user (login, password, name) VALUES ('$email', '$hash', '$name')");

if ($insert) {
    echo json_encode(['status' => 'success', 'message' => 'Usuário criado!']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn) ?: 'Erro desconhecido.']);
}
