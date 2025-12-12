<?php
// api/debug/bonos_list.php
header('Content-Type: application/json');

include_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la BD.']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT tipo, numDias, descripcion, precio, foto FROM bono ORDER BY tipo, numDias");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'count' => count($rows),
        'data' => $rows
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}