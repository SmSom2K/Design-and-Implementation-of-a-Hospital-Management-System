<?php
// api_doctor/get_diagnoses.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$patientId = intval($_GET['patient_id'] ?? 0);
if ($patientId <= 0) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid patient_id']);
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
    echo json_encode(['error'=>'DB connect failed']);
    exit;
}

// 2) Fetch diagnoses + attachment (if any)
$sql = "
    SELECT 
      d.id,
      d.patient_id,
      d.title,
      d.description,
      d.date,
      d.severity,
      d.treatment_plan,
      d.medications,
      d.follow_up,
      doc.file_path,
      doc.doc_type   AS attachment_type,
      doc.description AS attachment_desc
    FROM diagnoses d
    LEFT JOIN documents doc
      ON doc.parent_type = 'diagnosis'
     AND doc.parent_id   = d.id
    WHERE d.patient_id = :pid
    ORDER BY d.date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pid' => $patientId]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
