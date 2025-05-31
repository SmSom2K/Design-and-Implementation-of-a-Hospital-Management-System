<?php
// api_doctor/add_diagnosis.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Only doctors may add diagnoses
if (!isset($_SESSION['email'], $_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// 2) Grab form-data instead of JSON
$pid   = intval($_POST['patient_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$desc  = trim($_POST['description'] ?? '');
$date  = $_POST['date'] ?? '';
$sev   = $_POST['severity'] ?? '';
$plan  = trim($_POST['treatment_plan'] ?? '');
$meds  = trim($_POST['medications'] ?? '');
$fup   = $_POST['follow_up_date'] ?? '';  // match your form field name

// Validate required fields
if (!$pid || !$title || !$desc || !$date || !$sev || !$plan) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing required fields']);
    exit;
}

// 3) Connect via PDO
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

// include the logger


// 4) Lookup doctor’s user ID from session email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email'=>$_SESSION['email']]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
    http_response_code(401);
    echo json_encode(['error'=>'Doctor account not found']);
    exit;
}
$did = (int)$doc['id'];

// 5) Insert into diagnoses
$sql = "
  INSERT INTO diagnoses
    (patient_id, doctor_id, title, description, date,
     severity, treatment_plan, medications, follow_up)
  VALUES
    (:pid, :did, :title, :desc, :date,
     :sev, :plan, :meds, :fup)
";
$insert = $pdo->prepare($sql);
$insert->execute([
    ':pid'   => $pid,
    ':did'   => $did,
    ':title' => $title,
    ':desc'  => $desc,
    ':date'  => $date,
    ':sev'   => $sev,
    ':plan'  => $plan,
    ':meds'  => $meds,
    ':fup'   => $fup
]);


$diagnosisId = $pdo->lastInsertId();

// 6) Handle optional file upload for this diagnosis
if (!empty($_FILES['diagnosis_file']) && $_FILES['diagnosis_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/diagnoses/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $origName = basename($_FILES['diagnosis_file']['name']);
    $target   = $uploadDir . time() . "_{$origName}";

    if (move_uploaded_file($_FILES['diagnosis_file']['tmp_name'], $target)) {
        $filePath = 'api_doctor/uploads/diagnoses/' . basename($target);

        // 7) Insert into documents table
        $docSql = "
          INSERT INTO documents
            (parent_type, parent_id, file_path, uploaded_by)
          VALUES
            ('diagnosis', :pid, :path, :by)
        ";
        $docStmt = $pdo->prepare($docSql);
        $docStmt->execute([
          ':pid'  => $diagnosisId,
          ':path' => $filePath,
          ':by'   => $did
        ]);

       
    }
}
// 3) Log into doctor_activity_log
$conn = mysqli_connect("localhost","root","","hospital_db");
if ($conn) {
    $desc = "Added diagnosis “{$title}” (severity={$sev}) for patient_id={$pid}";
    mysqli_query($conn, "
      INSERT INTO doctor_activity_log (doctor_email, description)
      VALUES (
        '". mysqli_real_escape_string($conn, $_SESSION['email']) ."',
        '". mysqli_real_escape_string($conn, $desc) ."'
      )
    ");
    mysqli_close($conn);
}


// 8) Return success JSON
echo json_encode(['success'=>true]);
