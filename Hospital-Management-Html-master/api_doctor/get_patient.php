<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (
    !isset($_SESSION['email'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'doctor'
) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2) Connect
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

// 3) Find doctor’s user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([ $_SESSION['email'] ]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid doctor session']);
    exit;
}
$doctorId = (int)$doc['id'];

// 4) Get & validate the patient user-ID from the querystring
$patientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($patientId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid patient ID']);
    exit;
}

// 5) Fetch patient only if assigned to this doctor
$sql = "
  SELECT
    u.id          AS id,
    u.name        AS name,
    u.email       AS email,
    p.age         AS age,
    p.gender      AS gender,
    p.phone       AS phone,
    p.blood_type  AS blood_type,
    p.status      AS patient_status
  FROM users       AS u
  JOIN patients    AS p ON p.user_id    = u.id
  JOIN assignments AS a ON a.patient_id = u.id
  WHERE u.id        = :uid
    AND a.doctor_id = :did
  LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':uid' => $patientId,
    ':did' => $doctorId
]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    http_response_code(404);
    echo json_encode(['error' => 'Patient not found or not assigned to you']);
    exit;
}

// 6) All good—send it back
echo json_encode($patient);
