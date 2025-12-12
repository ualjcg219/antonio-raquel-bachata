<?php
// api/index.php
// Punto de entrada centralizado de la API
// Versión adaptada: incluye automáticamente los controladores disponibles,
// normaliza rutas (trailing slash / subdirectorio), acepta __route y maneja CORS/OPTIONS.
// Evita warnings de include mostrando una respuesta JSON clara si falta un controller.

// Evitar que warnings/notices se impriman en la salida JSON
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Encabezados para permitir CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

// Rutas absolutas para incluir archivos con seguridad
$BASE_DIR = __DIR__;

// Incluimos la configuración de la base de datos si existe
$dbConfigFile = $BASE_DIR . '/config/database.php';
if (!file_exists($dbConfigFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falta config/database.php']);
    exit;
}
include_once $dbConfigFile;

// Incluimos todos los controladores disponibles en api/controllers (si carpeta existe)
$controllersDir = $BASE_DIR . '/controllers';
if (is_dir($controllersDir)) {
    foreach (glob($controllersDir . '/*.php') as $ctrlFile) {
        include_once $ctrlFile;
    }
}

// Crear conexión con la base de datos (clase Database proporcionada en config/database.php)
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al conectar con la BD: ' . $e->getMessage()]);
    exit;
}

// Inicializamos controladores solo si las clases existen
$clienteController   = class_exists('ClienteController')   ? new ClienteController($db)   : null;
$cursoController     = class_exists('CursoController')     ? new CursoController($db)     : null;
$bonoController      = class_exists('BonoController')      ? new BonoController($db)      : null;
$reservaController   = class_exists('ReservaController')   ? new ReservaController($db)   : null;
$eventoController    = class_exists('EventoController')    ? new EventoController($db)    : null;
$claseController     = class_exists('ClaseDelDiaController')? new ClaseDelDiaController($db): null;
$authController      = class_exists('AuthController')      ? new AuthController($db)      : null;

// Obtener la ruta y método HTTP
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Calculamos el prefijo del proyecto (directorio padre de /api)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // ej. /antonio-raquel-bachata/api
$projectBase = dirname($scriptDir); // ej. /antonio-raquel-bachata

// Si la app está en un subdirectorio (ej. /antonio-raquel-bachata), eliminar ese prefijo
if ($projectBase !== '/' && strpos($requestUri, $projectBase) === 0) {
    $request = substr($requestUri, strlen($projectBase));
    if ($request === '') $request = '/';
} else {
    $request = $requestUri;
}

// Permitir pasar la ruta explícita vía parámetro '__route' (POST o GET)
// útil cuando llamamos directamente a index.php (evita problemas de rewrite)
if (isset($_REQUEST['__route']) && is_string($_REQUEST['__route']) && strlen($_REQUEST['__route']) > 0) {
    $request = $_REQUEST['__route'];
}

// Normalizar: eliminar doble slashes y quitar trailing slash (excepto si solo '/')
$request = preg_replace('#/+#', '/', $request);
if ($request !== '/' && substr($request, -1) === '/') {
    $request = rtrim($request, '/');
}

// Método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Helper para responder JSON
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper para comprobar que controller existe y lanzar 500 si no
function requireController($ctrl, $name) {
    if ($ctrl === null) {
        sendResponse(500, ['success' => false, 'message' => "Controller {$name} no disponible"]);
    }
}

// ROUTING
// Aquí definimos las rutas que ya existían en tu repo. Añade o modifica según necesites.
switch (true) {

    // === Auth ===
    case $request === '/api/login' && $method === 'POST':
        requireController($authController, 'AuthController');
        $authController->login();
        break;

    // === Clientes ===
    case $request === '/api/clientes' && $method === 'GET':
        requireController($clienteController, 'ClienteController');
        $clienteController->read();
        break;
    case $request === '/api/clientes' && $method === 'POST':
        requireController($clienteController, 'ClienteController');
        $clienteController->create();
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $m) && $method === 'GET':
        requireController($clienteController, 'ClienteController');
        $clienteController->readOne($m[1]);
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $m) && $method === 'PUT':
        requireController($clienteController, 'ClienteController');
        $clienteController->update($m[1]);
        break;
    case preg_match('#^/api/clientes/(\d+)$#', $request, $m) && $method === 'DELETE':
        requireController($clienteController, 'ClienteController');
        $clienteController->delete($m[1]);
        break;

    // === Cursos ===
    case $request === '/api/cursos' && $method === 'GET':
        requireController($cursoController, 'CursoController');
        $cursoController->read();
        break;
    case $request === '/api/cursos' && $method === 'POST':
        requireController($cursoController, 'CursoController');
        $cursoController->create();
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $m) && $method === 'GET':
        requireController($cursoController, 'CursoController');
        $cursoController->readOne($m[1]);
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $m) && $method === 'PUT':
        requireController($cursoController, 'CursoController');
        $cursoController->update($m[1]);
        break;
    case preg_match('#^/api/cursos/(\d+)$#', $request, $m) && $method === 'DELETE':
        requireController($cursoController, 'CursoController');
        $cursoController->delete($m[1]);
        break;

    // === Bonos ===
    case $request === '/api/bonos' && $method === 'GET':
        requireController($bonoController, 'BonoController');
        $bonoController->read();
        break;
    case $request === '/api/bonos' && $method === 'POST':
        requireController($bonoController, 'BonoController');
        $bonoController->create();
        break;
    case preg_match('#^/api/bonos/([^/]+)$#', $request, $m) && $method === 'GET':
        // Si hay una ruta tipo /api/bonos/{id} o /api/bonos/{tipo-numDias} la gestionará readOne si existe
        requireController($bonoController, 'BonoController');
        // intentar pasar como id si es numérico, sino pasar el valor a readOne si implementado
        $bonoController->readOne($m[1]);
        break;
    case preg_match('#^/api/bonos/([^/]+)$#', $request, $m) && $method === 'PUT':
        requireController($bonoController, 'BonoController');
        $bonoController->update($m[1]);
        break;
    case preg_match('#^/api/bonos/([^/]+)$#', $request, $m) && $method === 'DELETE':
        requireController($bonoController, 'BonoController');
        $bonoController->delete($m[1]);
        break;

    // === Reservas ===
    case $request === '/api/reservas' && $method === 'GET':
        requireController($reservaController, 'ReservaController');
        $reservaController->read();
        break;
    case $request === '/api/reservas' && $method === 'POST':
        requireController($reservaController, 'ReservaController');
        $reservaController->create();
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $m) && $method === 'GET':
        requireController($reservaController, 'ReservaController');
        $reservaController->readOne($m[1]);
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $m) && $method === 'PUT':
        requireController($reservaController, 'ReservaController');
        $reservaController->update($m[1]);
        break;
    case preg_match('#^/api/reservas/(\d+)$#', $request, $m) && $method === 'DELETE':
        requireController($reservaController, 'ReservaController');
        $reservaController->delete($m[1]);
        break;

    // === Eventos ===
    case $request === '/api/eventos' && $method === 'GET':
        requireController($eventoController, 'EventoController');
        $eventoController->read();
        break;
    case $request === '/api/eventos' && $method === 'POST':
        requireController($eventoController, 'EventoController');
        $eventoController->create();
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $m) && $method === 'GET':
        requireController($eventoController, 'EventoController');
        $eventoController->readOne($m[1]);
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $m) && $method === 'PUT':
        requireController($eventoController, 'EventoController');
        $eventoController->update($m[1]);
        break;
    case preg_match('#^/api/eventos/(\d+)$#', $request, $m) && $method === 'DELETE':
        requireController($eventoController, 'EventoController');
        $eventoController->delete($m[1]);
        break;

    // === ClasesDelDia ===
    case $request === '/api/ClasesDelDia' && $method === 'GET':
        requireController($claseController, 'ClaseDelDiaController');
        $claseController->read();
        break;
    case $request === '/api/ClasesDelDia' && $method === 'POST':
        requireController($claseController, 'ClaseDelDiaController');
        $claseController->create();
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $m) && $method === 'GET':
        requireController($claseController, 'ClaseDelDiaController');
        $claseController->readOne($m[1]);
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $m) && $method === 'PUT':
        requireController($claseController, 'ClaseDelDiaController');
        $claseController->update($m[1]);
        break;
    case preg_match('#^/api/ClasesDelDia/(\d+)$#', $request, $m) && $method === 'DELETE':
        requireController($claseController, 'ClaseDelDiaController');
        $claseController->delete($m[1]);
        break;

    // === Fallbacks para compatibilidad con archivos antiguos (opcional) ===
    // Si la ruta solicitada apunta a un archivo PHP real dentro de /api (ej: /api/bonos/create.php),
    // intentar incluirlo directamente (mantener compatibilidad con endpoints antiguos).
    case preg_match('#^/api/(.+\.php)$#', $request, $m):
        $file = $BASE_DIR . '/' . $m[1];
        if (file_exists($file)) {
            include_once $file;
            exit;
        }
        // si no existe, caer al 404 por defecto
        break;

    // === Ruta no encontrada ===
    default:
        sendResponse(404, ["success" => false, "message" => "Ruta no encontrada. Received request: " . $request]);
        break;
}
?>