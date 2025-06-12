<?php

function connectToDatabase()
{
    $host = 'localhost'; // Database host
    $db = 'app'; // Database name
    $user = 'root'; // Database username
    // $pass = 'root'; // Database password

    $conn = mysqli_connect("$host", "$user", "", "$db") or die("Houve um problema ao se conectar com o servidor");
    return $conn;
}
