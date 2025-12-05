<?php
// db.php
require_once 'vendor/autoload.php'; // Asegurarnos de cargar composer

// Cargamos las variables del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Función para conectar a la base de datos usando PDO
function conectarDB() {
    // Usamos $_ENV para leer las variables
    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
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
        // En producción no se debe mostrar el error exacto al usuario, pero para desarrollo está bien
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
?>