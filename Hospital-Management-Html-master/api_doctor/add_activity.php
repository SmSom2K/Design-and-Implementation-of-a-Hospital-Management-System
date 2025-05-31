<?php
// api_doctor/add_activity.php

session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2) Read payload
$data = json_decode(file_get_contents('php://input'), true);
$patientId   = isset($data['patient_id'])   ? intval($data['patient_id'])   : null;
$description = isset($data['description'])  ? trim($data['description'])    : '';

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing description']);
    exit;
}

// 3) Connect
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// 4) Insert
$sql = "
    INSERT INTO doctor_activity_log
      (doctor_email, patient_id, description)
    VALUES
      (:doc, :pid, :desc)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':doc'  => $_SESSION['email'],
    ':pid'  => $patientId,
    ':desc' => $description
]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
