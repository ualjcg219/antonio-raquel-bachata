<?php
// admin/api/db.php

// 1. Cargar Composer (Subimos 2 niveles para llegar a la raíz)
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// 2. Cargar variables de entorno desde la raíz
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

// 3. Función de conexión
function conectarDB() {
    // Usamos las variables del .env
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db   = $_ENV['DB_NAME'] ?? 'mydb';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Devolvemos error JSON si falla la conexión
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error de Conexión BD: " . $e->getMessage()]);
        exit;
    }
}
?>