<?php
/* ═══════════════════════════════════════════════════════════
   admin/login.php — Admin girişi (Session yönetimi)
   ═══════════════════════════════════════════════════════════ */

session_start();

// Zaten giriş yaptıysa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../db.php'; // config.php'yi de çeker

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Kullanıcı adı ve şifre boş bırakılamaz.';
    } elseif ($username !== ADMIN_USER) {
        $error = 'Kullanıcı adı veya şifre hatalı.';
    } elseif (!password_verify($password, ADMIN_PASS)) {
        $error = 'Kullanıcı adı veya şifre hatalı.';
    } else {
        // Başarılı giriş
        session_regenerate_id(true); // Session fixation önlemi
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $username;
        $_SESSION['login_time']      = time();

        // Cookie: 1 saatlik oturum hatırlama
        setcookie('last_login', date('Y-m-d H:i:s'), time() + 3600, '/', '', false, true);

        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Giriş — Emine Kurucu</title>
  <link rel="icon" type="image/jpeg" href="../../assets/favicon.jpg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg: #0a0a0f; --bg-card: #15151e; --border: #2a2a3a;
      --accent: #7c6aff; --accent-2: #a78bfa;
      --text: #e8e8f0; --text-muted: #8888a0;
      --error: #ff4f6a; --success: #22d3a0;
      --font-mono: 'Space Mono', monospace;
      --font-display: 'Syne', sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-mono);
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .login-box {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 48px 40px;
      width: 100%;
      max-width: 420px;
    }
    .login-logo {
      font-family: var(--font-display);
      font-size: 2rem;
      font-weight: 800;
      color: var(--accent);
      margin-bottom: 8px;
    }
    .login-sub { color: var(--text-muted); font-size: 0.8rem; margin-bottom: 36px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 8px; }
    input {
      width: 100%; padding: 12px 16px;
      background: var(--bg); border: 1px solid var(--border);
      border-radius: 8px; color: var(--text);
      font-family: var(--font-mono); font-size: 0.85rem;
      outline: none; transition: border-color 0.2s;
    }
    input:focus { border-color: var(--accent); }
    .error-box {
      background: rgba(255,79,106,0.1); border: 1px solid var(--error);
      color: var(--error); padding: 12px 16px; border-radius: 8px;
      font-size: 0.82rem; margin-bottom: 20px;
    }
    <?php if (!empty($_COOKIE['last_login'])): ?>
    .last-login {
      background: rgba(34,211,160,0.08); border: 1px solid var(--success);
      color: var(--success); padding: 10px 14px; border-radius: 8px;
      font-size: 0.78rem; margin-bottom: 20px;
    }
    <?php endif; ?>
    button {
      width: 100%; padding: 14px;
      background: var(--accent); border: none; border-radius: 8px;
      color: #fff; font-family: var(--font-mono); font-size: 0.9rem;
      font-weight: 700; cursor: pointer; transition: background 0.2s, transform 0.2s;
    }
    button:hover { background: var(--accent-2); transform: translateY(-2px); }
    .back-link { display: block; text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 0.78rem; text-decoration: none; }
    .back-link:hover { color: var(--accent-2); }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="login-logo">EK_admin</div>
    <p class="login-sub">// Yönetim Paneli Girişi</p>

    <?php if (!empty($_COOKIE['last_login'])): ?>
      <div class="last-login">
        Son giriş: <?= htmlspecialchars($_COOKIE['last_login']) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Kullanıcı Adı</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               autocomplete="username" required />
      </div>
      <div class="form-group">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password"
               autocomplete="current-password" required />
      </div>
      <button type="submit">Giriş Yap →</button>
    </form>
    <a href="../../index.html" class="back-link">← Portfolyoya Dön</a>
  </div>
</body>
</html>