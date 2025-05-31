<?php
// api_doctor/delete_note.php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Only doctors can delete notes
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents('php://input'), true);
$noteId    = intval($data['note_id'] ?? 0);
$patientId = intval($data['patient_id'] ?? 0);

if (!$noteId || !$patientId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing note_id or patient_id']);
    exit;
}

try {
    // 1) Connect
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 2) Ensure this note belongs to this patient
    $stmt = $pdo->prepare("SELECT id FROM notes WHERE id = :nid AND user_id = :pid");
    $stmt->execute([':nid' => $noteId, ':pid' => $patientId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Note not found']);
        exit;
    }

    // 3) Delete the note
    $del = $pdo->prepare("DELETE FROM notes WHERE id = :nid");
    $del->execute([':nid' => $noteId]);

    // 4) Lookup patient name for the log
    $u = $pdo->prepare("SELECT name FROM users WHERE id = :pid LIMIT 1");
    $u->execute([':pid' => $patientId]);
    $user = $u->fetch(PDO::FETCH_ASSOC);
    $patientName = $user['name'] ?? 'Unknown';

    // 5) Log into doctor_activity_log
    $log = $pdo->prepare("
        INSERT INTO doctor_activity_log
          (doctor_email, patient_id, description)
        VALUES
          (:email, :pid, :desc)
    ");
    $desc = sprintf(
        "Deleted a note (ID %d) for Patient %s (ID %d)",
        $noteId,
        $patientName,
        $patientId
    );
    $log->execute([
        ':email' => $_SESSION['email'],
        ':pid'   => $patientId,
        ':desc'  => $desc
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
