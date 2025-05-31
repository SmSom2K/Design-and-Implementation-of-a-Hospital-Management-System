<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// 2) Lookup doctor’s user ID by email
$ident = $_SESSION['email'];
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :ident LIMIT 1");
    $stmt->execute([':ident' => $ident]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$doc) {
        throw new Exception('Doctor not found');
    }
    $doctorId = (int)$doc['id'];
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['error'=>'Doctor lookup failed']);
    exit;
}

// 3) Fetch and split appointments
try {
    // Fetch all appointments for this doctor
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.appointment_time,
            a.patient_id,
            u.name AS patient_name,
            a.purpose,
            a.status
        FROM appointments AS a
        JOIN users        AS u ON u.id = a.patient_id
        WHERE a.doctor_id = :did
        ORDER BY a.appointment_time DESC
    ");
    $stmt->execute([':did' => $doctorId]);
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Split into buckets
    $pending  = [];
    $accepted = [];
    $declined = [];
    foreach ($all as $row) {
        switch ($row['status']) {
            case 'pending':
                $pending[]  = $row;
                break;
            case 'accepted':
                $accepted[] = $row;
                break;
            case 'declined':
                $declined[] = $row;
                break;
        }
    }

    // 4) Return JSON
    echo json_encode([
        'pending'  => $pending,
        'accepted' => $accepted,
        'declined' => $declined
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Query failed']);
    exit;
}
