<?php
session_start();

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Access denied"]);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

// Get form inputs
$name         = mysqli_real_escape_string($conn, $_POST['name']);
$username     = mysqli_real_escape_string($conn, $_POST['username']);
$passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$email        = mysqli_real_escape_string($conn, $_POST['email']);
$phone        = mysqli_real_escape_string($conn, $_POST['phone']);
$age          = intval($_POST['age']);
$gender       = mysqli_real_escape_string($conn, $_POST['gender']);
$bloodType    = mysqli_real_escape_string($conn, $_POST['blood_type']);
$status       = mysqli_real_escape_string($conn, $_POST['status']);

// <-- use doctor_id (matches your <select name="doctor_id">) -->
$assignedDoctorId = isset($_POST['doctor_id'])
    ? intval($_POST['doctor_id'])
    : 0;

// 1) Insert into users table
$insertUser = "
  INSERT INTO users (name, username, password, email, role)
  VALUES ('$name', '$username', '$passwordHash', '$email', 'patient')
";
if (!mysqli_query($conn, $insertUser)) {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
    exit;
}
$userId = mysqli_insert_id($conn);

// 2) Insert into patients table
// 2) Insert into patients table (no doctor_id column any more)
$insertPatient = "
  INSERT INTO patients 
    (user_id, name, age, gender, phone, blood_type, status)
  VALUES
    ($userId, '$name', $age, '$gender', '$phone', '$bloodType', '$status')
";
if (!mysqli_query($conn, $insertPatient)) {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
    exit;
}

// 3) Now link patient to doctor in assignments
$assign = "
  INSERT INTO assignments (doctor_id, patient_id)
  VALUES ($assignedDoctorId, $userId)
";
if (!mysqli_query($conn, $assign)) {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
    exit;
}

// 3) Log the activity
$desc = "Patient <strong>" . htmlspecialchars($name) .
        "</strong> added and assigned to doctor ID <em>$assignedDoctorId</em>.";
mysqli_query($conn, "
  INSERT INTO activity_log (action_type, description)
  VALUES ('add_patient', '" . mysqli_real_escape_string($conn, $desc) . "')
");

// 4) Success!
echo json_encode(["success" => true]);
