<?php
$conn = mysqli_connect("localhost", "root", "", "hospital_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$purpose = $_POST['subject'] ?? '';
$phone = $_POST['number'] ?? '';
$department = $_POST['Department'] ?? '';
$date = $_POST['appointment_date'] ?? '';
$time = $_POST['Time'] ?? '';

$sql = "INSERT INTO appointments (name, email, purpose, phone, department, appointment_date, appointment_time)
        VALUES ('$name', '$email', '$purpose', '$phone', '$department', '$date', '$time')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('✅ Appointment booked successfully!'); window.location.href='appointment.html';</script>";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
