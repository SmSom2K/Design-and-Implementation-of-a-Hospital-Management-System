<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Connect
$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    echo json_encode(['error' => 'DB connect failed']);
    exit;
}

// 2) Fetch assignments, joining users for both doctor and patient
$sql = "
  SELECT
    a.id,
    d.name AS doctorName,
    p.name AS patientName,
    a.dateAssigned
  FROM assignments a
    JOIN users d ON a.doctor_id  = d.id
    JOIN users p ON a.patient_id = p.id
  ORDER BY a.dateAssigned DESC
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

// 3) Build JSON array
$assignments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $assignments[] = $row;
}

// 4) Output
echo json_encode($assignments);
