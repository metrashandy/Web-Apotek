<?php
$koneksi = mysqli_connect("localhost", "root", "", "apotek");

if (mysqli_connect_error()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
