<?php
// api_doctor/delete_appointment.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// only doctors
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role']!=='doctor') {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$aid  = intval($data['appointment_id'] ?? 0);
if (!$aid) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing appointment_id']);
    exit;
}

try {
    $pdo = new PDO(
      'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
      'root','',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB conn failed']);
    exit;
}

// ensure this appointment belongs to this doctor
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email'=>$_SESSION['email']]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Doctor not found']);
    exit;
}
$did = intval($doc['id']);

// delete only if it matches
$del = $pdo->prepare("DELETE FROM appointments WHERE id = :aid AND doctor_id = :did");
$ok = $del->execute([':aid'=>$aid,':did'=>$did]);

if ($ok && $del->rowCount()) {

    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Not found or not yours']);
}
