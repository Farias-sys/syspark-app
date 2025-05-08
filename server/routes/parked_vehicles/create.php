<?php

require_once '../../db/db.php';

$conn = connectToDatabase();

$vehicle_plate = $_POST['vehicle_plate'];
$vehicle_model = $_POST['vehicle_model'];
$vehicle_color = $_POST['vehicle_color'];
$parking_spot = $_POST['parking_spot'];

$check_if_vehicle_exists = mysqli_query($conn, "SELECT id,plate,model,color FROM vehicles WHERE vehicle_plate = '$vehicle_plate'");

if (mysqli_num_rows($check_if_vehicle_exists) > 0) {
    $vehicle = mysqli_fetch_assoc($check_if_vehicle_exists);

    $insert_parked_vehicle = mysqli_query($conn, "INSERT INTO parked_vehicles (vehicle_id, parking_spot) VALUES ('{$vehicle['id']}', '$parking_spot')");

    echo json_encode(array('status' => 'success', 'message' => 'Vehicle parked successfully'));
} else {
    $insert_vehicle = mysqli_query($conn, "INSERT INTO vehicles (plate, model, color) VALUES ('$vehicle_plate', '$vehicle_model', '$vehicle_color')");

    $vehicle = mysqli_fetch_assoc($insert_vehicle);
    $insert_parked_vehicle = mysqli_query($conn, "INSERT INTO parked_vehicles (vehicle_id, parking_spot) VALUES ('{$vehicle['id']}', '$parking_spot')");
    echo json_encode(array("status" => "success", "message" => "Vehicle parked successfully"));
}