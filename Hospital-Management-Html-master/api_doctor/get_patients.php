<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Must be logged in as doctor:
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode([]); 
    exit;
}

// Grab the doctor’s user ID from the session:
$doctorId = intval($_SESSION['id'] ?? 0);
error_log("get_patients.php → doctorId in session: $doctorId");

// Connect:
$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connect failed']);
    exit;
}

// Query only your assigned patients:
$sql = "
  SELECT
    u.id         AS user_id,
    u.username   AS username,
    u.email      AS email,
    u.name       AS name,
    p.age        AS age,
    p.gender     AS gender,
    p.phone      AS phone,
    p.blood_type AS blood_type,
    p.status     AS status
  FROM assignments a
  JOIN patients  p ON p.user_id = a.patient_id
  JOIN users     u ON u.id      = p.user_id
  WHERE a.doctor_id = $doctorId
  ORDER BY u.name
";
$res = mysqli_query($conn, $sql);
if (!$res) {
    http_response_code(500);
    error_log("get_patients.php → SQL error: " . mysqli_error($conn));
    echo json_encode(['error'=>mysqli_error($conn)]);
    exit;
}

$rows = mysqli_num_rows($res);
error_log("get_patients.php → assignment rows found: $rows");

// Build JSON
$out = [];
while ($r = mysqli_fetch_assoc($res)) {
    $out[] = $r;
}
echo json_encode($out);
