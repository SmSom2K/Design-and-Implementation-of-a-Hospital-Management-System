<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role']!=='patient') {
  echo json_encode([]);
  exit;
}
$uid = (int)$_SESSION['id'];
try {
  $pdo = new PDO(
    'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
    'root','',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
  $sql = "
    SELECT
      d.id,
      d.title,
      d.description,
      d.date,
      d.severity,
      d.treatment_plan,
      d.medications,
      d.follow_up,
      u.NAME       AS doctor_name
    FROM diagnoses AS d
    JOIN users     AS u ON u.id = d.doctor_id
    WHERE d.patient_id = :uid
    ORDER BY d.date DESC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':uid'=>$uid]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error'=>'DB error']);
}
