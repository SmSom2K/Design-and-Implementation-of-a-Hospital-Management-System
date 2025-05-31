<?php
// api_patient/get_documents.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth: only patients
if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role']!=='patient') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// 2) Validate inputs
$type = $_GET['type'] ?? '';
$id   = intval($_GET['id'] ?? 0);
if (!in_array($type, ['diagnosis','medical_record'], true) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid parameters']);
    exit;
}

try {
    // 3) DB connect
    $pdo = new PDO(
      'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
      'root','',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );

    // 4) Fetch documents
    $sql = "
      SELECT
        d.id,
        d.doc_type,
        d.description,
        d.file_path,
        d.uploaded_at,
        u.NAME AS uploader_name
      FROM documents AS d
      JOIN users     AS u ON u.id = d.uploaded_by
      WHERE d.parent_type = :type
        AND d.parent_id   = :pid
      ORDER BY d.uploaded_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':type'=>$type, ':pid'=>$id]);
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($docs);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'DB error']);
}
