<?php
// api/debug/db_info.php
header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['ok' => false, 'message' => 'No se pudo conectar a la BD con PDO.']);
    exit;
}

try {
    $row = $db->query("SELECT DATABASE() AS dbname")->fetch(PDO::FETCH_ASSOC);
    $user = $db->query("SELECT USER() AS user")->fetch(PDO::FETCH_ASSOC);
    $version = $db->getAttribute(PDO::ATTR_SERVER_VERSION);

    echo json_encode([
        'ok' => true,
        'database' => $row['dbname'] ?? null,
        'user' => $user['user'] ?? null,
        'pdo_server_version' => $version,
        'pdo_attributes' => [
            'ERRMODE' => $db->getAttribute(PDO::ATTR_ERRMODE)
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}