<?php
// contact.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) Collect & sanitize
  $date    = date('Y-m-d H:i');
  $name    = trim($_POST['name']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $phone   = trim($_POST['number']  ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  // 2) Build the line (escape any | in message)
  $safe_message = str_replace('|', '/', $message);
  $line = "$date | $name | $email | $phone | $subject | $safe_message" . PHP_EOL;

  // 3) Append to inbox.txt
  file_put_contents(__DIR__ . '/inbox.txt', $line, FILE_APPEND | LOCK_EX);

  // 4) Redirect to thank-you
  header('Location: thank-you.html');
  exit;
}

// If someone lands here by GET, just show a 404 or redirect back
header('Location: contact.html');
exit;
