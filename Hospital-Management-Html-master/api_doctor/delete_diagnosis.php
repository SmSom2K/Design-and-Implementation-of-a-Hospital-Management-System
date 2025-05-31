<?php
// api_doctor/delete_diagnosis.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only doctors can delete
if (!isset($_SESSION['role'], $_SESSION['email']) || $_SESSION['role']!=='doctor') {
  http_response_code(401);
  echo json_encode(['error'=>'Unauthorized']);
  exit;
}
$body = json_decode(file_get_contents('php://input'), true);
$did  = intval($body['diagnosis_id'] ?? 0);

if (!$did) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid diagnosis_id']);
    exit;
}

try {
  $pdo = new PDO(
    'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
    'root','',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );

  // Optional: delete any attached document first
  $stmt1 = $pdo->prepare("
    DELETE FROM documents
     WHERE parent_type='diagnosis' AND parent_id=:did
  ");
  $stmt1->execute([':did'=>$did]);

  // Then delete the diagnosis
  $stmt2 = $pdo->prepare("DELETE FROM diagnoses WHERE id=:did");
  $stmt2->execute([':did'=>$did]);


// 3) Log into doctor_activity_log
$conn = mysqli_connect("localhost","root","","hospital_db");
if ($conn) {
    $desc = "Deleted diagnosis_id={$did}";
    mysqli_query($conn, "
      INSERT INTO doctor_activity_log (doctor_email, description)
      VALUES (
        '". mysqli_real_escape_string($conn, $_SESSION['email']) ."',
        '". mysqli_real_escape_string($conn, $desc) ."'
      )
    ");
    mysqli_close($conn);
}



  echo json_encode(['success'=>true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
