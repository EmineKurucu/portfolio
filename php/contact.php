<?php
require_once __DIR__ . '/db.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is supported.');
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data.');
}

$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

$errors = [];
if (strlen($name) < 2)                          $errors[] = 'Name must be at least 2 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
if (strlen($subject) < 1)                       $errors[] = 'Subject is required.';
if (strlen($message) < 10)                      $errors[] = 'Message must be at least 10 characters.';

if (!empty($errors)) {
    jsonResponse(false, implode(' ', $errors));
}

// ── Save to database ──────────────────────────────────────
$conn = getConnection();
$stmt = $conn->prepare(
    "INSERT INTO contacts (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())"
);

if (!$stmt) {
    jsonResponse(false, 'Query could not be prepared.');
}

$stmt->bind_param('ssss', $name, $email, $subject, $message);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    jsonResponse(false, 'Message could not be saved, please try again.');
}

$stmt->close();
$conn->close();

// Email gönderimi EmailJS (JavaScript) tarafında yapılıyor.

jsonResponse(true, 'Your message has been sent successfully.');
