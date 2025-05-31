<?php
$conn = mysqli_connect("localhost", "root", "", "hospital_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email = $_POST['email'];
$password_raw = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'];

// check if passwords match BEFORE hashing
if ($password_raw !== $confirm_password) {
    echo "❌ Passwords do not match!";
    exit;
}

// hash it after confirmation
$hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);
$full_name = $fname . ' ' . $lname;

$sql = "INSERT INTO users (NAME, email, PASSWORD, role) VALUES ('$full_name', '$email', '$hashed_password', '$role')";


if (mysqli_query($conn, $sql)) {
    echo "✅ Registered successfully! <a href='login.html'>Click here to login</a>";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
