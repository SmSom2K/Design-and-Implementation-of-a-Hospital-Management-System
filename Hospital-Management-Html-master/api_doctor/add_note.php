<?php
// api_doctor/add_note.php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Auth
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// 2) Read payload
$data      = json_decode(file_get_contents('php://input'), true);
$patientId = intval($data['patient_id'] ?? 0);
$note      = trim($data['note'] ?? '');

if (!$patientId || !$note) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing data']);
    exit;
}

try {
    // 3) Connect
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root','',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 4) Insert into notes
    $stmt = $pdo->prepare("
        INSERT INTO notes (user_id, note)
        VALUES (:uid, :note)
    ");
    $stmt->execute([
        ':uid'  => $patientId,
        ':note' => $note
    ]);

    // 5) Look up patient name
    $pstmt = $pdo->prepare("SELECT name FROM users WHERE id = :pid LIMIT 1");
    $pstmt->execute([':pid' => $patientId]);
    $row = $pstmt->fetch(PDO::FETCH_ASSOC);
    $patientName = $row['name'] ?? 'Unknown';

    // 6) Log into doctor_activity_log with patient name
    $logStmt = $pdo->prepare("
        INSERT INTO doctor_activity_log
          (doctor_email, patient_id, description)
        VALUES
          (:email, :pid, :desc)
    ");
    $description = "Added a note for Patient {$patientName} (ID {$patientId})";
    $logStmt->execute([
        ':email' => $_SESSION['email'],
        ':pid'   => $patientId,
        ':desc'  => $description
    ]);

    echo json_encode(['success'=>true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'DB error: '.$e->getMessage()]);
}
