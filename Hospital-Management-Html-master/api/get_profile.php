<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo json_encode(["session_value" => $_SESSION['user'] ?? 'not set']);
exit;
