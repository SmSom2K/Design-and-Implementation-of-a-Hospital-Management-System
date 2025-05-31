<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$query = "SELECT action_type, description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 10";
$result = mysqli_query($conn, $query);

$logs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $logs[] = [
        "type" => $row['action_type'],
        "description" => $row['description'],
        "timestamp" => $row['timestamp']
    ];
}

echo json_encode($logs);
?>
