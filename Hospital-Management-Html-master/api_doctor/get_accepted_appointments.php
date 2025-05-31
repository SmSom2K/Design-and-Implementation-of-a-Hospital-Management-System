<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!($_SESSION['role'] ?? '') === 'doctor') {
  http_response_code(401); exit;
}
$did = (int)($_SESSION['user_id'] ?? 0);
$pdo = new PDO('mysql:host=localhost;dbname=hospital_db','root','',[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
]);
$stmt = $pdo->prepare("
  SELECT * FROM accepted_appointments
   WHERE doctor_id = :did
");
$stmt->execute([':did'=>$did]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
