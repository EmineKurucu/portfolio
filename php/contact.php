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

// ── Send email via PHPMailer ──────────────────────────────
$phpmailerPath = __DIR__ . '/PHPMailer/';

if (file_exists($phpmailerPath . 'PHPMailer.php')) {
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
    require_once $phpmailerPath . 'Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'Portfolio Contact');
        $mail->addAddress(NOTIFY_EMAIL);
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = '[Portfolio] New message: ' . $subject;
        $mail->Body    = '
        <div style="font-family:monospace;background:#0a0a0f;color:#e8e8f0;padding:32px;">
          <div style="max-width:540px;margin:0 auto;background:#15151e;border:1px solid #2a2a3a;border-radius:12px;padding:32px;">
            <h2 style="color:#7c6aff;margin:0 0 24px;">📬 New contact form message</h2>
            <p><span style="color:#8888a0;">Name:</span> ' . htmlspecialchars($name) . '</p>
            <p><span style="color:#8888a0;">Email:</span> <a href="mailto:' . htmlspecialchars($email) . '" style="color:#a78bfa;">' . htmlspecialchars($email) . '</a></p>
            <p><span style="color:#8888a0;">Subject:</span> ' . htmlspecialchars($subject) . '</p>
            <hr style="border:none;border-top:1px solid #2a2a3a;margin:20px 0;" />
            <p style="color:#8888a0;font-size:12px;">Message:</p>
            <p style="white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
          </div>
        </div>';

        $mail->send();
    } catch (\Exception $e) {
        // Mail hatası form başarısını engellemez
    }
}

jsonResponse(true, 'Your message has been sent successfully.');
