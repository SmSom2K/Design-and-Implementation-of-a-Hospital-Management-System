<?php
// api_patient/get_my_appointments.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Only patients may call this
if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // 2) Connect
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 3) Fetch all appointments for this patient,
    //    joining to users to get the doctor’s name & ID
    $stmt = $pdo->prepare("
        SELECT
          a.id,
          a.appointment_time,
          a.purpose,
          a.status,
          u.id   AS doctor_id,
          u.NAME AS doctor_name
        FROM appointments AS a
        JOIN users        AS u
          ON u.id = a.doctor_id
        WHERE a.patient_id = :uid
        ORDER BY a.appointment_time DESC
    ");
    $stmt->execute([':uid' => $_SESSION['id']]);
    $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4) Return as JSON array
    echo json_encode($apps);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error']);
    exit;
}
