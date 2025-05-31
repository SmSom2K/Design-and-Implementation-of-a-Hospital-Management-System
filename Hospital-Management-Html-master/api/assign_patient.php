<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Connect
$conn = mysqli_connect("localhost","root","","hospital_db");
if (!$conn) {
  echo json_encode(["success"=>false,"error"=>"DB connect failed"]);
  exit;
}

// 2) Decode payload
$payload = json_decode(file_get_contents('php://input'), true);
$doctorId  = intval($payload['doctorId']  ?? 0);
$patientId = intval($payload['patientId'] ?? 0);
if (!$doctorId || !$patientId) {
  echo json_encode(["success"=>false,"error"=>"Missing doctorId or patientId"]);
  exit;
}

// 3) Check for existing assignment
$stmt = mysqli_prepare(
  $conn,
  'SELECT COUNT(*) FROM assignments WHERE doctor_id = ? AND patient_id = ?'
);
mysqli_stmt_bind_param($stmt, 'ii', $doctorId, $patientId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($count > 0) {
  echo json_encode(["success"=>false,"error"=>"This patient is already assigned to that doctor."]);
  exit;
}

// 4) Insert new assignment
$stmt = mysqli_prepare(
  $conn,
  'INSERT INTO assignments (doctor_id, patient_id) VALUES (?, ?)'
);
mysqli_stmt_bind_param($stmt, 'ii', $doctorId, $patientId);
if (!mysqli_stmt_execute($stmt)) {
  echo json_encode(["success"=>false,"error"=>mysqli_error($conn)]);
  exit;
}
mysqli_stmt_close($stmt);

// 5) Log activity (optional)
$desc = sprintf('Assigned patient ID %d to doctor ID %d.', $patientId, $doctorId);
$logStmt = mysqli_prepare(
  $conn,
  'INSERT INTO activity_log (action_type, description) VALUES (?, ?)'
);
$action = 'assign';
mysqli_stmt_bind_param($logStmt, 'ss', $action, $desc);
mysqli_stmt_execute($logStmt);
mysqli_stmt_close($logStmt);

// 6) Success
echo json_encode(["success"=>true]);
exit;
?>
