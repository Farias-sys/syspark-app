<?php

function connectToDatabase()
{
    $host = ''; // Database host
    $db = 'app'; // Database name
    $user = ''; // Database username
    $pass = ''; // Database password

    $conn = mysqli_connect("$host", "$user", "$pass", "$db") or die("Houve um problema ao se conectar com o servidor");
    return $conn;
}
