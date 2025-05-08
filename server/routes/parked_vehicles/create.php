<?php

require_once '../../db/db.php';

$conn = connectToDatabase();

$vehicle_plate = $_POST['plate'];
$vehicle_model = $_POST['model'];
$vehicle_color = $_POST['color'];
$parking_spot = $_POST['spot'];

$check_if_vehicle_exists = mysqli_query($conn, "SELECT id,plate,model,color FROM vehicles WHERE plate = '$vehicle_plate'");

if (mysqli_num_rows($check_if_vehicle_exists) > 0) {
    $vehicle = mysqli_fetch_assoc($check_if_vehicle_exists);

    $insert_parked_vehicle = mysqli_query($conn, "INSERT INTO parked_vehicles (vehicle_id, parking_spot_id) VALUES ('{$vehicle['id']}', '$parking_spot')");

    echo json_encode(array('status' => 'success', 'message' => 'Vehicle parked successfully'));
} else {
    $insert_vehicle = mysqli_query($conn, "INSERT INTO vehicles (plate, model, color) VALUES ('$vehicle_plate', '$vehicle_model', '$vehicle_color')");

    $vehicle_id = mysqli_insert_id($conn);
    $insert_parked_vehicle = mysqli_query($conn, "INSERT INTO parked_vehicles (vehicle_id, parking_spot_id) VALUES ('$vehicle_id', '$parking_spot')");
    echo json_encode(["status" => "success", "message" => "Vehicle parked successfully"]);
}