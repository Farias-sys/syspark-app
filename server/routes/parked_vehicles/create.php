<?php

require_once '../../db/db.php';
session_start();
$user_id = $_SESSION['user_id'];

$conn = connectToDatabase();

$vehicle_plate = $_POST['plate'];
$vehicle_model = $_POST['model'];
$vehicle_color = $_POST['color'];
$parking_spot = $_POST['spot'];

$exists = mysqli_query(
    $conn,
    "SELECT id FROM vehicles
     WHERE plate = '$vehicle_plate' AND user_id = '$user_id'"
);

if (mysqli_num_rows($exists) > 0) {
    $v = mysqli_fetch_assoc($exists);
    mysqli_query(
        $conn,
        "INSERT INTO parked_vehicles (user_id, vehicle_id, parking_spot_id)
         VALUES ('$user_id', '{$v['id']}', '$parking_spot')"
    );
} else {
    mysqli_query(
        $conn,
        "INSERT INTO vehicles (user_id, plate, model, color)
         VALUES ('$user_id', '$vehicle_plate', '$vehicle_model', '$vehicle_color')"
    );

    $vehicle_id = mysqli_insert_id($conn);

    mysqli_query(
        $conn,
        "INSERT INTO parked_vehicles (user_id, vehicle_id, parking_spot_id)
         VALUES ('$user_id', '$vehicle_id', '$parking_spot')"
    );
}

echo json_encode(['status' => 'success']);
