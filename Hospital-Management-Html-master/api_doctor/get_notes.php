<?php
// api_doctor/get_notes.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role']!=='doctor') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// 2) Validate patient user ID
$patientUserId = intval($_GET['patient_id'] ?? 0);
if ($patientUserId <= 0) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid patient_id']);
    exit;
}

// 3) Connect
try {
    $pdo = new PDO(
      'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
      'root','',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connection failed']);
    exit;
}

// 4) Fetch notes for this user_id
$sql = "
  SELECT 
    id,
    user_id,
    note,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS date
  FROM notes
  WHERE user_id = :uid
  ORDER BY created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $patientUserId]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Return
echo json_encode($notes);
