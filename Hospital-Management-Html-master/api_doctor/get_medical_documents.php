<?php
// api_doctor/get_medical_documents.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// auth check…
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$pid = intval($_GET['patient_id'] ?? 0);
if (!$pid) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing patient_id']);
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

// fetch docs + uploader name
$stmt = $pdo->prepare("
  SELECT
    doc.id,
    doc.doc_type,
    doc.description,
    doc.file_path,
    doc.uploaded_at,
    doc.uploaded_by      AS uploader_id,
    u.name               AS uploader_name
  FROM documents doc
  JOIN users u
    ON u.id = doc.uploaded_by
  WHERE doc.parent_type = 'medical_record'
    AND doc.parent_id   = :pid
  ORDER BY doc.uploaded_at DESC
");
$stmt->execute([':pid'=>$pid]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($docs);
