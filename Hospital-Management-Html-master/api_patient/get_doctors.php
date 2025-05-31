<?php
// api_patient/get_doctors.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// turn off notice-level output
error_reporting(E_ALL & ~E_NOTICE);

if (!isset($_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    http_response_code(401);
    echo json_encode([]); // always echo something
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hospital_db;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->query(
      "SELECT id, NAME AS name, department
         FROM users
        WHERE role='doctor'
        ORDER BY name"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]); // still return valid JSON
}
