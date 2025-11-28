<?php
// index.php - punto de entrada de la API

// Encabezados para permitir CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Incluimos la configuración de la base de datos
include_once 'config/database.php';

// Incluimos todos los controladores
include_once 'controllers/ClienteController.php';
include_once 'controllers/CursoController.php';
include_once 'controllers/BonoController.php';
include_once 'controllers/ReservaController.php';
include_once 'controllers/EventoController.php';
include_once 'controllers/ClaseDelDiaController.php';
include_once 'controllers/AuthController.php';

// Crear conexión con la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializamos controladores
$clienteController = new ClienteController($db);
$cursoController = new CursoController($db);
$bonoController = new BonoController($db);
$reservaController = new ReservaController($db);
$eventoController = new EventoController($db);
$claseController = new ClaseDelDiaController($db);
$authController = new AuthController($db);

// Obtener la ruta y método HTTP
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Eliminamos query strings para trabajar solo con la ruta
$request = explode('?', $request, 2)[0];

// Función auxiliar para enviar respuestas JSON
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Routing básico
switch(true) {

    // === Auth ===
    case $request === '/api/login' && $method === 'POST':
        $authController->login();
        break;

    // === Clientes ===
    case $request === '/api/clientes' && $method === 'GET':
        $clienteController->read();
        break;
    case $request === '/api/clientes' && $method === 'POST':
        $clienteController->create();
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $matches) && $method === 'GET':
        $clienteController->readOne($matches[1]);
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $matches) && $method === 'PUT':
        $clienteController->update($matches[1]);
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $clienteController->delete($matches[1]);
        break;

    // === Cursos ===
    case $request === '/api/cursos' && $method === 'GET':
        $cursoController->read();
        break;
    case $request === '/api/cursos' && $method === 'POST':
        $cursoController->create();
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $matches) && $method === 'GET':
        $cursoController->readOne($matches[1]);
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $matches) && $method === 'PUT':
        $cursoController->update($matches[1]);
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $cursoController->delete($matches[1]);
        break;

    // === Bonos ===
    case $request === '/api/bonos' && $method === 'GET':
        $bonoController->read();
        break;
    case $request === '/api/bonos' && $method === 'POST':
        $bonoController->create();
        break;
    case preg_match('#^/api/bonos/(\d+)$#', $request, $matches) && $method === 'GET':
        $bonoController->readOne($matches[1]);
        break;
    case preg_match('#^/api/bonos/(\d+)$#', $request, $matches) && $method === 'PUT':
        $bonoController->update($matches[1]);
        break;
    case preg_match('#^/api/bonos/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $bonoController->delete($matches[1]);
        break;

    // === Reservas ===
    case $request === '/api/reservas' && $method === 'GET':
        $reservaController->read();
        break;
    case $request === '/api/reservas' && $method === 'POST':
        $reservaController->create();
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $matches) && $method === 'GET':
        $reservaController->readOne($matches[1]);
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $matches) && $method === 'PUT':
        $reservaController->update($matches[1]);
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $reservaController->delete($matches[1]);
        break;

    // === Eventos ===
    case $request === '/api/eventos' && $method === 'GET':
        $eventoController->read();
        break;
    case $request === '/api/eventos' && $method === 'POST':
        $eventoController->create();
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $matches) && $method === 'GET':
        $eventoController->readOne($matches[1]);
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $matches) && $method === 'PUT':
        $eventoController->update($matches[1]);
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $eventoController->delete($matches[1]);
        break;

    // === ClasesDelDia ===
    case $request === '/api/ClasesDelDia' && $method === 'GET':
        $claseController->read();
        break;
    case $request === '/api/ClasesDelDia' && $method === 'POST':
        $claseController->create();
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $matches) && $method === 'GET':
        $claseController->readOne($matches[1]);
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $matches) && $method === 'PUT':
        $claseController->update($matches[1]);
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $matches) && $method === 'DELETE':
        $claseController->delete($matches[1]);
        break;

    // === Ruta no encontrada ===
    default:
        sendResponse(404, ["message" => "Ruta no encontrada."]);
        break;
}
