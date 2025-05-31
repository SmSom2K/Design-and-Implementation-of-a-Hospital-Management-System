<?php
// api/delete_patient.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' || !isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$patientUserId = intval($_GET['id']);

// 1) Grab patient name for the log
$res = mysqli_query($conn, "
    SELECT name
    FROM users
    WHERE id = {$patientUserId}
      AND role = 'patient'
");
$row = mysqli_fetch_assoc($res);
$patientName = $row['name'] ?? 'Unknown';

// 2) Child‐record cleanup
mysqli_query($conn, "DELETE FROM assignments   WHERE patient_id   = {$patientUserId}");
mysqli_query($conn, "DELETE FROM appointments  WHERE patient_id   = {$patientUserId}");
mysqli_query($conn, "DELETE FROM diagnoses     WHERE patient_id   = {$patientUserId}");
mysqli_query($conn, "DELETE FROM notes         WHERE user_id      = {$patientUserId}");
// ← REMOVED: DELETE FROM documents WHERE patient_id…

// 3) Remove from patients
mysqli_query($conn, "
    DELETE FROM patients
    WHERE user_id = {$patientUserId}
");

// 4) Remove from users
$ok = mysqli_query($conn, "
    DELETE FROM users
    WHERE id = {$patientUserId}
      AND role = 'patient'
");

if ($ok) {
    // 5) Log it
    $desc = "Deleted patient " . mysqli_real_escape_string($conn, $patientName)
          . " (User ID: {$patientUserId})";
    mysqli_query($conn, "
      INSERT INTO activity_log (action_type, description)
      VALUES (
        'delete_patient',
        '" . mysqli_real_escape_string($conn, $desc) . "'
      )
    ");
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
      "success" => false,
      "error"   => mysqli_error($conn)
    ]);
}
