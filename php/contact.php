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
    "INSERT INTO contacts (name, email, subject, message, created_at)
     VALUES (?, ?, ?, ?, NOW())"
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

// ── Send email notification ───────────────────────────────
$to      = NOTIFY_EMAIL;
$headers = implode("\r\n", [
    'Content-Type: text/html; charset=UTF-8',
    'From: Portfolio Contact <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'portfolio') . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

$emailSubject = '[Portfolio] New message: ' . $subject;

$emailBody = '
<!DOCTYPE html>
<html>
<body style="font-family:monospace;background:#0a0a0f;color:#e8e8f0;padding:32px;">
  <div style="max-width:540px;margin:0 auto;background:#15151e;border:1px solid #2a2a3a;border-radius:12px;padding:32px;">
    <h2 style="color:#7c6aff;margin:0 0 24px;">📬 New contact form message</h2>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <tr><td style="color:#8888a0;padding:8px 0;width:90px;">Name</td>
          <td style="color:#e8e8f0;">' . htmlspecialchars($name) . '</td></tr>
      <tr><td style="color:#8888a0;padding:8px 0;">Email</td>
          <td><a href="mailto:' . htmlspecialchars($email) . '" style="color:#a78bfa;">' . htmlspecialchars($email) . '</a></td></tr>
      <tr><td style="color:#8888a0;padding:8px 0;">Subject</td>
          <td style="color:#e8e8f0;">' . htmlspecialchars($subject) . '</td></tr>
    </table>
    <hr style="border:none;border-top:1px solid #2a2a3a;margin:20px 0;" />
    <p style="color:#8888a0;font-size:12px;margin:0 0 8px;">Message</p>
    <p style="color:#e8e8f0;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
    <hr style="border:none;border-top:1px solid #2a2a3a;margin:20px 0;" />
    <p style="color:#555568;font-size:11px;margin:0;">Sent from your portfolio contact form</p>
  </div>
</body>
</html>';

mail($to, $emailSubject, $emailBody, $headers);
// mail() hatası form gönderimini engellemez — DB kaydı zaten yapıldı

jsonResponse(true, 'Your message has been sent successfully.');
