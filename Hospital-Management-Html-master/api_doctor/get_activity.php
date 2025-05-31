<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Only allow doctors (or admins, if you like)
if (!isset($_SESSION['email'], $_SESSION['role']) 
    || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2) Identify doctor by email
$doctorEmail = $_SESSION['email'];

// 3) Connect
$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connect failed']);
    exit;
}

// 4) Fetch last 50 activity log entries
$sql = "
  SELECT 
    id,
    created_at AS timestamp,
    description
  FROM doctor_activity_log
  WHERE doctor_email = '" . mysqli_real_escape_string($conn, $doctorEmail) . "'
  ORDER BY created_at DESC
  LIMIT 50
";
$res = mysqli_query($conn, $sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

// 5) Build result array
$logs = [];
while ($row = mysqli_fetch_assoc($res)) {
    $logs[] = $row;
}

// 6) Output JSON
echo json_encode($logs);
