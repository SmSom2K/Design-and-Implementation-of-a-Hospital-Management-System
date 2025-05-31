<?php
// api_patient/request_appointment.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) only patients may request
if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    http_response_code(401);
    echo json_encode(['success'=>false, 'error'=>'Unauthorized']);
    exit;
}

// 2) pull in the form data
$docId   = intval($_POST['doctor_id'] ?? 0);
$time    = trim($_POST['appointment_time'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');

if (!$docId || !$time || !$purpose) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'Missing fields']);
    exit;
}

try {
    // 3) connect
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );

    // 4) insert into appointments using the users.id from session
    $ins = $pdo->prepare("
        INSERT INTO appointments
          (doctor_id, patient_id, appointment_time, purpose, status)
        VALUES
          (:did,      :uid,        :atime,          :purpose, 'pending')
    ");
    $ins->execute([
        ':did'     => $docId,
        ':uid'     => $_SESSION['id'],   // <-- use users.id here
        ':atime'   => $time,
        ':purpose' => $purpose
    ]);

    echo json_encode(['success'=>true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
