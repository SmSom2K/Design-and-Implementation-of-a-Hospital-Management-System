<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

$query = "SELECT id, NAME AS name, username, email, department, phone, status FROM users WHERE role = 'doctor'";
$result = mysqli_query($conn, $query);

$doctors = [];

while ($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

echo json_encode($doctors);
?>
