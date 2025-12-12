<?php
// api/debug/echo_post.php
// Devuelve lo que llega en POST y FILES para depuración rápida
header('Content-Type: application/json; charset=utf-8');

$out = [
    'time' => date('c'),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
    'post' => $_POST,
    'files' => []
];

foreach ($_FILES as $k => $f) {
    $out['files'][$k] = [
        'name' => $f['name'] ?? null,
        'type' => $f['type'] ?? null,
        'size' => $f['size'] ?? null,
        'error' => $f['error'] ?? null,
        'tmp_name' => $f['tmp_name'] ?? null,
        'is_uploaded_file' => isset($f['tmp_name']) ? is_uploaded_file($f['tmp_name']) : false
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);