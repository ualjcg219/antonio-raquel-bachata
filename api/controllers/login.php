<?php
// api/controllers/login.php
// Endpoint: POST /api/controllers/login.php
header('Content-Type: application/json; charset=utf-8');

// Incluir clase Database y modelos/controlladores (ajusta rutas si tu estructura difiere)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/AuthController.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al conectar con la base de datos.']);
    exit;
}

// Inyectamos la conexión en el controlador
$authController = new AuthController($db);
$authController->login();