<?php
require_once __DIR__ . '/config.php';

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantı hatası.']);
        exit;
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}

function jsonResponse(bool $success, string $message = '', $data = null): void {
    header('Content-Type: application/json; charset=utf-8');
    $res = ['success' => $success, 'message' => $message];
    if ($data !== null) $res['data'] = $data;
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

function setHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
}
