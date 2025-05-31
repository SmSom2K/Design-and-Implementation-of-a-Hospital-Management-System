<?php
// api_patient/get_profile.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Auth check
if (
    !isset($_SESSION['id'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'patient'
) {
    echo json_encode(null);
    exit;
}
$uid = (int) $_SESSION['id'];

try {
    // 2) Connect
    $pdo = new PDO(
      'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
      'root','',
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 3) Fetch joined profile by users.id
        $sql = "
      SELECT
        u.id         AS user_id,
        u.NAME       AS name,
        u.username   AS username,       -- add this
        u.email      AS email,
        p.id         AS patient_id,
        p.age        AS age,
        p.gender     AS gender,
        p.blood_type AS blood_type,
        p.phone      AS phone,
        p.status     AS status
      FROM users    AS u
      JOIN patients AS p ON p.user_id = u.id
      WHERE u.id = :uid
      LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4) Not found?
    if (!$row) {
        echo json_encode(null);
        exit;
    }

    // 5) Return JSON
    echo json_encode([
      'user_id'     => (int)$row['user_id'],
      'name'        => $row['name'],
      'username'    => $row['username'],
      'email'       => $row['email'],
      'patient_id'  => (int)$row['patient_id'],
      'age'         => (int)$row['age'],
      'gender'      => $row['gender'],
      'blood_type'  => $row['blood_type'],
      'phone'       => $row['phone'],
      'status'      => $row['status']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Database error']);
    exit;
}
