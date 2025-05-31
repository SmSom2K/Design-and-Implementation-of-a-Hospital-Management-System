<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'];
$username = $data['username'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$department = $data['department'];
$phone = $data['phone'];
$status = $data['status'];

$query = "INSERT INTO users (NAME, username, email, PASSWORD, department, phone, status, role)
          VALUES ('$name', '$username', '$email', '$password', '$department', '$phone', '$status', 'doctor')";

if (mysqli_query($conn, $query)) {
    $desc = "Doctor <strong>" . htmlspecialchars($name) . "</strong> added to <em>" . htmlspecialchars($department) . "</em> department.";
    mysqli_query($conn, "INSERT INTO activity_log (action_type, description) VALUES ('add_doctor', '$desc')");
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
}
