<?php
// api_doctor/delete_activity.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Only doctors (or admins) may delete
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role']!=='doctor') {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Unauthorized']);
  exit;
}

// 2) Parse JSON body
$body = json_decode(file_get_contents('php://input'), true);
$actId = intval($body['activity_id'] ?? 0);
if (!$actId) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Missing activity_id']);
  exit;
}

try {
  // 3) Connect
  $pdo = new PDO(
    'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
    'root','',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );

  // 4) (Optional) ensure this record belongs to this doctor
  $stmt = $pdo->prepare("
    SELECT 1 FROM doctor_activity_log 
      WHERE id = :aid 
        AND doctor_email = :email
    LIMIT 1
  ");
  $stmt->execute([
    ':aid'   => $actId,
    ':email' => $_SESSION['email']
  ]);
  if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>'Not found or not yours']);
    exit;
  }

  // 5) Delete
  $del = $pdo->prepare("DELETE FROM doctor_activity_log WHERE id = :aid");
  $del->execute([':aid'=>$actId]);

  echo json_encode(['success'=>true]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'error'=>'DB error: '.$e->getMessage()]);
}
