<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Get doctor name BEFORE deleting
        $res = mysqli_query($conn, "SELECT NAME AS name FROM users WHERE id = $id AND role = 'doctor'");
        $doctor = mysqli_fetch_assoc($res);
        $doctorName = $doctor['name'] ?? 'Unknown';

        // Delete doctor
        $query = "DELETE FROM users WHERE id = $id AND role = 'doctor'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $desc = "Deleted doctor " . htmlspecialchars($doctorName) . " (ID: $id)";
            mysqli_query($conn, "INSERT INTO activity_log (action_type, description) VALUES ('delete_doctor', '$desc')");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Missing doctor ID"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
