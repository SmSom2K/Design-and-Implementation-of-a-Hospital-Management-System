<?php
// api/get_patients.php

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connect failed"]);
    exit;
}

// Grab every user whose role is patient, LEFT JOIN to patients for extra fields
$sql = "
  SELECT
    u.id            AS user_id,
    u.username      AS username,
    u.email         AS email,
    u.name          AS name,
    p.age           AS age,
    p.gender        AS gender,
    p.phone         AS phone,
    p.blood_type    AS blood_type,
    p.status        AS patient_status
  FROM users u
  LEFT JOIN patients p ON p.user_id = u.id
  WHERE u.role = 'patient'
  ORDER BY u.name
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
    exit;
}

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    // ensure numeric fields are proper types
    $row['user_id'] = intval($row['user_id']);
    $row['age']     = $row['age'] !== null ? intval($row['age']) : null;
    $patients[]     = $row;
}

echo json_encode($patients);
