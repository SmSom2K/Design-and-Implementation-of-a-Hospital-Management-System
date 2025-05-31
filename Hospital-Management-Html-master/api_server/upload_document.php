<?php
// api_server/upload_document.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
session_start();

// 1) Auth check
if (!isset($_SESSION['role'], $_SESSION['email']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

// 2) Identify uploader’s user ID
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :ident LIMIT 1");
    $stmt->execute([':ident' => $_SESSION['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("Uploader not found");
    }
    $uploaderId = (int)$user['id'];
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Cannot identify uploader']);
    exit;
}

// 3) Gather & validate form inputs
$parentId    = intval($_POST['patient_id'] ?? 0);
$docType     = trim($_POST['doc_type'] ?? '');
$description = trim($_POST['description'] ?? '');
if (!$parentId || !$docType || empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing required fields']);
    exit;
}

// 4) Handle the file upload
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Cannot create uploads directory']);
    exit;
}
$file     = $_FILES['file'];
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('doc_') . '.' . $ext;
$dest     = $uploadDir . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Failed to move uploaded file']);
    exit;
}
// Construct the public path to store in DB:
$filePath = '/hospital/Hospital-Management-Html-master/uploads/' . $filename;

// 5) Insert into documents
try {
    $insert = $pdo->prepare("
        INSERT INTO documents
          (parent_type, parent_id, doc_type, description, file_path, uploaded_by)
        VALUES
          ('medical_record', :pid, :dtype, :desc, :path, :by)
    ");
    $insert->execute([
        ':pid'   => $parentId,
        ':dtype' => $docType,
        ':desc'  => $description,
        ':path'  => $filePath,
        ':by'    => $uploaderId
    ]);

    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB error (documents): '.$e->getMessage()]);
    exit;
}

// 6) ALSO insert into medical_records
try {
    $rec = $pdo->prepare("
      INSERT INTO medical_records
        (patient_id, record_date, record_type, description)
      VALUES
        (:pid, NOW(), :rtype, :rdesc)
    ");
    $rec->execute([
      ':pid'   => $parentId,
      ':rtype' => $docType,
      ':rdesc' => $description
    ]);
} catch (PDOException $e) {
    // If this fails, we log the error but still return success for the document upload
    error_log("Failed to insert medical_record: " . $e->getMessage());
}

// 3) Log into doctor_activity_log
$conn = mysqli_connect("localhost","root","","hospital_db");
if ($conn) {
    $desc = "Uploaded document “{$docType}” for patient_id={$parentId}";
    mysqli_query($conn, "
      INSERT INTO doctor_activity_log (doctor_email, description)
      VALUES (
        '". mysqli_real_escape_string($conn, $_SESSION['email']) ."',
        '". mysqli_real_escape_string($conn, $desc) ."'
      )
    ");
    mysqli_close($conn);
}


// 7) Return success
echo json_encode(['success'=>true]);
exit;
