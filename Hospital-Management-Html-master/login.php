<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "hospital_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get and sanitize form data
$input = trim($_POST['userid'] ?? '');
$password = trim($_POST['usrpsw'] ?? '');

if (empty($input) || empty($password)) {
    echo "Please fill in both fields.";
    exit;
}

// Fetch user by email or username
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $input, $input);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['PASSWORD'])) {
        // Valid login - store session data
        $_SESSION['user'] = $user['NAME'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'doctor') {
            header("Location: doctor_dashboard.php");
        } elseif ($user['role'] === 'patient') {
            header("Location: patient_dashboard.php");
        } else {
            echo "Unknown role.";
        }
        exit;
    } else {
        echo "Invalid username or password.";
    }
} else {
    echo "Invalid username or password.";
}

mysqli_close($conn);
?>
