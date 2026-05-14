<?php
/* ═══════════════════════════════════════════════════════════
   Projeler MySQL den çekilir ve JSON formatında döndürülür.
    Sadece GET metodu desteklenir.
   ═══════════════════════════════════════════════════════════ */

require_once __DIR__ . '/db.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Sadece GET metodu destekleniyor.');
}

$conn = getConnection();

$result = $conn->query(
    "SELECT id, title, description, tags, github_url, demo_url, created_at
     FROM projects
     ORDER BY id ASC"
);

if (!$result) {
    $conn->close();
    jsonResponse(false, 'Projeler yüklenemedi.');
}

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

$result->free();
$conn->close();

jsonResponse(true, '', $projects);