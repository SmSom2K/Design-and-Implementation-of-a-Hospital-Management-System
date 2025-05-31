<?php
// api_doctor/get_profile.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only doctors may fetch this
if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(null);
    exit;
}

$uid = (int) $_SESSION['id'];

try {
    $pdo = new PDO(
      'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
      'root','',
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
      SELECT
        u.id        AS user_id,
        u.username  AS username,
        u.NAME      AS name,
        u.email     AS email,
        u.department AS department,
        u.phone     AS phone,
        u.status    AS status
      FROM users AS u
      WHERE u.id = :uid
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(null);
        exit;
    }

    echo json_encode($row);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(null);
    exit;
}
