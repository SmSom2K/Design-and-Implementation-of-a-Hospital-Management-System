<?php
// api_doctor/update_appointment.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (!isset($_SESSION['role'], $_SESSION['email']) || $_SESSION['role']!=='doctor') {
  http_response_code(401);
  echo json_encode(['error'=>'Unauthorized']);
  exit;
}

// 2) Parse JSON body
$body = json_decode(file_get_contents('php://input'), true);
$id     = intval($body['id'] ?? 0);
$status = $body['status'] ?? '';

if (!$id || !in_array($status, ['accepted','declined'], true)) {
  http_response_code(400);
  echo json_encode(['error'=>'Invalid payload']);
  exit;
}

try {
  // 3) Lookup doctor_id
  $pdo = new PDO(
    'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
    'root','',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
  $stmt->execute([':email'=>$_SESSION['email']]);
  $doc = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$doc) throw new Exception('Doctor not found');
  $doctorId = (int)$doc['id'];

  // 4) Update appointment if it belongs to this doctor
  $upd = $pdo->prepare("
    UPDATE appointments
       SET status = :status
     WHERE id = :id
       AND doctor_id = :did
  ");
  $upd->execute([
    ':status' => $status,
    ':id'     => $id,
    ':did'    => $doctorId
  ]);

  if ($upd->rowCount()===0) {
    http_response_code(404);
    echo json_encode(['error'=>'No matching appointment']);
    exit;
  }


  // ────────────────
  // 
  // 
  // 
  
  // 3) Log into doctor_activity_log
$conn = mysqli_connect("localhost","root","","hospital_db");
if ($conn) {
    $action = $status === 'accepted' ? 'Accepted' : 'Declined';
    $desc   = "{$action} appointment_id={$id}";
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
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>'Update failed']);
}
