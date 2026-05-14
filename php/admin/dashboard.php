<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

require_once __DIR__ . '/../db.php';

$conn    = getConnection();
$success = '';
$error   = '';

// ── POST actions ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_project') {
        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $tags        = trim($_POST['tags']        ?? '');
        $github_url  = trim($_POST['github_url']  ?? '');
        $demo_url    = trim($_POST['demo_url']    ?? '');

        if (!$title || !$description) {
            $error = 'Title and description are required.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO projects (title, description, tags, github_url, demo_url, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param('sssss', $title, $description, $tags, $github_url, $demo_url);
            $stmt->execute() ? $success = 'Project added successfully.' : $error = 'Could not add project.';
            $stmt->close();
        }
    }

    if ($_POST['action'] === 'edit_project') {
        $id          = (int)($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $tags        = trim($_POST['tags']        ?? '');
        $github_url  = trim($_POST['github_url']  ?? '');
        $demo_url    = trim($_POST['demo_url']    ?? '');

        if (!$title || !$description) {
            $error = 'Title and description are required.';
        } else {
            $stmt = $conn->prepare(
                "UPDATE projects SET title=?, description=?, tags=?, github_url=?, demo_url=? WHERE id=?"
            );
            $stmt->bind_param('sssssi', $title, $description, $tags, $github_url, $demo_url, $id);
            $stmt->execute() ? $success = 'Project updated successfully.' : $error = 'Could not update project.';
            $stmt->close();
        }
    }

    if ($_POST['action'] === 'delete_project') {
        $id   = (int)($_POST['project_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute() ? $success = 'Project deleted.' : $error = 'Could not delete.';
        $stmt->close();
    }

    if ($_POST['action'] === 'delete_message') {
        $id   = (int)($_POST['msg_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute() ? $success = 'Message deleted.' : $error = 'Could not delete.';
        $stmt->close();
    }
}

// ── Logout ────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('last_login', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

// ── Edit mode: load project ───────────────────────────────
$editProject = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res    = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $res->bind_param('i', $editId);
    $res->execute();
    $editProject = $res->get_result()->fetch_assoc();
    $res->close();
}

// ── Fetch data ────────────────────────────────────────────
$projects = $conn->query("SELECT * FROM projects ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$messages = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Emine Kurucu</title>
  <link rel="icon" type="image/jpeg" href="../../assets/favicon.jpg" />
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg:#0a0a0f; --bg-2:#111118; --bg-card:#15151e; --border:#2a2a3a;
      --accent:#7c6aff; --accent-2:#a78bfa; --accent-glow:rgba(124,106,255,.18);
      --text:#e8e8f0; --text-muted:#8888a0; --text-dim:#555568;
      --success:#22d3a0; --error:#ff4f6a; --warn:#f59e0b;
      --font-mono:'Space Mono',monospace; --font-display:'Syne',sans-serif;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:var(--font-mono);background:var(--bg);color:var(--text);min-height:100vh}
    a{color:var(--accent-2);text-decoration:none}
    a:hover{color:var(--accent)}

    .dash-header{
      background:var(--bg-card);border-bottom:1px solid var(--border);
      padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;
      position:sticky;top:0;z-index:100;
    }
    .dash-logo{font-family:var(--font-display);font-weight:800;font-size:1.2rem;color:var(--accent)}
    .dash-meta{font-size:.75rem;color:var(--text-muted)}
    .logout-btn{
      padding:6px 16px;border:1px solid var(--border);border-radius:6px;
      color:var(--text-muted);font-family:var(--font-mono);font-size:.78rem;
      background:none;cursor:pointer;transition:all .2s;
    }
    .logout-btn:hover{border-color:var(--error);color:var(--error)}

    .dash-body{max-width:1100px;margin:0 auto;padding:40px 24px}
    .dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px}
    @media(max-width:768px){.dash-grid{grid-template-columns:1fr}}

    .panel{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden}
    .panel-head{
      padding:18px 24px;border-bottom:1px solid var(--border);
      font-family:var(--font-display);font-weight:700;font-size:1rem;
      display:flex;align-items:center;gap:8px;
    }
    .panel-head .dot{width:8px;height:8px;border-radius:50%;background:var(--accent)}
    .panel-head .dot.warn{background:var(--warn)}
    .panel-body{padding:24px}

    .alert{padding:10px 16px;border-radius:8px;font-size:.82rem;margin-bottom:20px}
    .alert-success{background:rgba(34,211,160,.1);border:1px solid var(--success);color:var(--success)}
    .alert-error{background:rgba(255,79,106,.1);border:1px solid var(--error);color:var(--error)}

    .form-group{margin-bottom:16px}
    label{display:block;font-size:.75rem;color:var(--text-muted);margin-bottom:6px}
    input,textarea{
      width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);
      border-radius:6px;color:var(--text);font-family:var(--font-mono);font-size:.82rem;
      outline:none;transition:border-color .2s;resize:vertical;
    }
    input:focus,textarea:focus{border-color:var(--accent)}
    .btn-add{
      width:100%;padding:12px;background:var(--accent);border:none;border-radius:8px;
      color:#fff;font-family:var(--font-mono);font-size:.85rem;font-weight:700;
      cursor:pointer;transition:all .2s;
    }
    .btn-add:hover{background:var(--accent-2);transform:translateY(-1px)}
    .btn-update{background:var(--warn);color:#000;}
    .btn-update:hover{background:#fbbf24;color:#000;}
    .btn-cancel{
      display:block;text-align:center;margin-top:10px;font-size:.78rem;
      color:var(--text-muted);cursor:pointer;
    }
    .btn-cancel:hover{color:var(--error)}

    .data-table{width:100%;border-collapse:collapse;font-size:.78rem}
    .data-table th{
      text-align:left;padding:10px 12px;border-bottom:1px solid var(--border);
      color:var(--text-muted);font-weight:400;
    }
    .data-table td{padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:top;color:var(--text-muted)}
    .data-table td strong{color:var(--text)}
    .data-table tr:last-child td{border-bottom:none}

    .btn-del{
      padding:4px 10px;border:1px solid var(--error);border-radius:4px;
      background:none;color:var(--error);font-size:.72rem;cursor:pointer;
      font-family:var(--font-mono);transition:all .2s;
    }
    .btn-del:hover{background:var(--error);color:#fff}
    .btn-edit{
      padding:4px 10px;border:1px solid var(--warn);border-radius:4px;
      background:none;color:var(--warn);font-size:.72rem;cursor:pointer;
      font-family:var(--font-mono);transition:all .2s;margin-right:6px;text-decoration:none;display:inline-block;
    }
    .btn-edit:hover{background:var(--warn);color:#000}

    .tag-pill{
      display:inline-block;padding:2px 8px;border-radius:20px;
      background:var(--accent-glow);color:var(--accent-2);font-size:.68rem;
      border:1px solid rgba(124,106,255,.2);margin:2px 2px 2px 0;
    }
    .empty{color:var(--text-dim);font-size:.82rem;padding:20px 0;text-align:center}

    .stats-bar{display:flex;gap:24px;margin-bottom:32px;flex-wrap:wrap;}
    .stat-pill{
      background:var(--bg-card);border:1px solid var(--border);border-radius:10px;
      padding:16px 24px;display:flex;flex-direction:column;gap:4px;
    }
    .stat-pill .num{font-family:var(--font-display);font-size:1.8rem;font-weight:800;color:var(--accent)}
    .stat-pill .lbl{font-size:.72rem;color:var(--text-muted)}
  </style>
</head>
<body>

<header class="dash-header">
  <div class="dash-logo">EK_admin</div>
  <div class="dash-meta">
    👤 <?= htmlspecialchars($_SESSION['admin_user']) ?>
    &nbsp;·&nbsp;
    Login: <?= date('H:i', $_SESSION['login_time']) ?>
  </div>
  <a href="?logout=1"><button class="logout-btn">Log Out</button></a>
</header>

<div class="dash-body">

  <?php if ($success): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-bar">
    <div class="stat-pill">
      <span class="num"><?= count($projects) ?></span>
      <span class="lbl">Projects</span>
    </div>
    <div class="stat-pill">
      <span class="num"><?= count($messages) ?></span>
      <span class="lbl">Messages</span>
    </div>
    <div class="stat-pill">
      <span class="num"><?= count(array_filter($messages, fn($m) => date('Y-m-d', strtotime($m['created_at'])) === date('Y-m-d'))) ?></span>
      <span class="lbl">Today</span>
    </div>
  </div>

  <div class="dash-grid">

    <!-- ── Left: Add/Edit + List ── -->
    <div>

      <?php if ($editProject): ?>
      <!-- EDIT FORM -->
      <div class="panel" style="margin-bottom:24px;border-color:var(--warn)">
        <div class="panel-head"><span class="dot warn"></span>Edit Project — <?= htmlspecialchars($editProject['title']) ?></div>
        <div class="panel-body">
          <form method="POST" action="">
            <input type="hidden" name="action" value="edit_project" />
            <input type="hidden" name="project_id" value="<?= (int)$editProject['id'] ?>" />
            <div class="form-group">
              <label>Title *</label>
              <input type="text" name="title" value="<?= htmlspecialchars($editProject['title']) ?>" required />
            </div>
            <div class="form-group">
              <label>Description *</label>
              <textarea name="description" rows="3" required><?= htmlspecialchars($editProject['description']) ?></textarea>
            </div>
            <div class="form-group">
              <label>Technologies (comma-separated)</label>
              <input type="text" name="tags" value="<?= htmlspecialchars($editProject['tags']) ?>" />
            </div>
            <div class="form-group">
              <label>GitHub URL</label>
              <input type="url" name="github_url" value="<?= htmlspecialchars($editProject['github_url']) ?>" />
            </div>
            <div class="form-group">
              <label>Demo URL</label>
              <input type="url" name="demo_url" value="<?= htmlspecialchars($editProject['demo_url']) ?>" />
            </div>
            <button type="submit" class="btn-add btn-update">💾 Save Changes</button>
            <a href="dashboard.php" class="btn-cancel">✕ Cancel</a>
          </form>
        </div>
      </div>
      <?php else: ?>
      <!-- ADD FORM -->
      <div class="panel" style="margin-bottom:24px">
        <div class="panel-head"><span class="dot"></span>Add New Project</div>
        <div class="panel-body">
          <form method="POST" action="">
            <input type="hidden" name="action" value="add_project" />
            <div class="form-group">
              <label>Title *</label>
              <input type="text" name="title" placeholder="Project Title" required />
            </div>
            <div class="form-group">
              <label>Description *</label>
              <textarea name="description" rows="3" placeholder="Short project description..." required></textarea>
            </div>
            <div class="form-group">
              <label>Technologies (comma-separated)</label>
              <input type="text" name="tags" placeholder="Python, OpenCV, React" />
            </div>
            <div class="form-group">
              <label>GitHub URL</label>
              <input type="url" name="github_url" placeholder="https://github.com/..." />
            </div>
            <div class="form-group">
              <label>Demo URL</label>
              <input type="url" name="demo_url" placeholder="https://..." />
            </div>
            <button type="submit" class="btn-add">+ Add Project</button>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <div class="panel">
        <div class="panel-head"><span class="dot"></span>Projects (<?= count($projects) ?>)</div>
        <div class="panel-body" style="padding:0">
          <?php if (empty($projects)): ?>
            <p class="empty">No projects yet.</p>
          <?php else: ?>
            <table class="data-table">
              <thead><tr><th>Title</th><th>Tags</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($p['title']) ?></strong><br />
                    <span style="font-size:.7rem;color:var(--text-dim)"><?= htmlspecialchars(substr($p['description'], 0, 60)) ?>...</span>
                  </td>
                  <td>
                    <?php foreach (explode(',', $p['tags']) as $t): ?>
                      <span class="tag-pill"><?= htmlspecialchars(trim($t)) ?></span>
                    <?php endforeach; ?>
                  </td>
                  <td style="white-space:nowrap">
                    <a href="?edit=<?= (int)$p['id'] ?>" class="btn-edit">Edit</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this project?')">
                      <input type="hidden" name="action" value="delete_project" />
                      <input type="hidden" name="project_id" value="<?= (int)$p['id'] ?>" />
                      <button type="submit" class="btn-del">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ── Right: Messages ── -->
    <div class="panel">
      <div class="panel-head"><span class="dot"></span>Inbox (<?= count($messages) ?>)</div>
      <div class="panel-body" style="padding:0">
        <?php if (empty($messages)): ?>
          <p class="empty">No messages yet.</p>
        <?php else: ?>
          <table class="data-table">
            <thead><tr><th>From</th><th>Subject & Message</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($messages as $m): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($m['name']) ?></strong><br />
                  <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="font-size:.7rem"><?= htmlspecialchars($m['email']) ?></a><br />
                  <span style="font-size:.68rem;color:var(--text-dim)"><?= date('d.m.Y H:i', strtotime($m['created_at'])) ?></span>
                </td>
                <td>
                  <strong><?= htmlspecialchars($m['subject']) ?></strong><br />
                  <span style="font-size:.72rem"><?= htmlspecialchars(substr($m['message'], 0, 80)) ?>...</span>
                </td>
                <td>
                  <form method="POST" onsubmit="return confirm('Delete this message?')">
                    <input type="hidden" name="action" value="delete_message" />
                    <input type="hidden" name="msg_id" value="<?= (int)$m['id'] ?>" />
                    <button type="submit" class="btn-del">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.dash-grid -->
</div><!-- /.dash-body -->

</body>
</html>
