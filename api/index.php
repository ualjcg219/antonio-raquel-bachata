<?php
// api/index.php

// 1. HEADERS Y CORS (Dejamos que PHP se encargue, no el .htaccess)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight requests (Peticiones OPTIONS del navegador)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. INCLUDES
require_once 'config/database.php';
require_once 'utils/Response.php';

// 3. OBTENER URI Y MÉTODO (LÓGICA CORREGIDA)
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Obtenemos la ruta solicitada (ej: /antonio-raquel-bachata/api/clientes)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Obtenemos la carpeta donde está este script (ej: /antonio-raquel-bachata/api)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// Si la ruta empieza por la carpeta del script, la quitamos para limpiar la URL
if (strpos($requestUri, $scriptDir) === 0) {
    $requestUri = substr($requestUri, strlen($scriptDir));
}

// Dividimos lo que queda por barras '/'
$uri = explode('/', $requestUri);
// Quitamos elementos vacíos y reordenamos índices
$uri = array_values(array_filter($uri));

// Ahora $uri[0] siempre será el recurso (clientes, bonos, etc.) sin importar la carpeta base
$resource = isset($uri[0]) ? $uri[0] : null;
$id = isset($uri[1]) ? $uri[1] : null;
$subresource = isset($uri[2]) ? $uri[2] : null;

// 4. CONEXIÓN BASE DE DATOS
$db = conectarDB();               // ✅ LLAMAMOS A LA FUNCIÓN DIRECTAMENTE

// 5. OBTENER DATOS DEL BODY (JSON)
$data = json_decode(file_get_contents("php://input"));

// 6. ENRUTAMIENTO
try {
    switch($resource) {
        case 'clientes':
            require_once 'models/Cliente.php';
            $cliente = new Cliente($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        $cliente->DNI = $id;
                        $stmt = $cliente->getByDNI();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Cliente no encontrado", 404);
                        }
                    } else {
                        $stmt = $cliente->getAll();
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($clientes);
                    }
                    break;
                    
                case 'POST':
                    if($id === 'login') { // Ojo: si la ruta es /clientes/login, 'login' llega como $id en esta lógica nueva
                        if(!isset($data->email) || !isset($data->password)) {
                            Response::error("Email y contraseña requeridos", 400);
                        }
                        $result = $cliente->login($data->email, $data->password);
                        if($result) {
                            Response::success($result, "Login exitoso");
                        } else {
                            Response::error("Credenciales inválidas", 401);
                        }
                    } else {
                        // Crear cliente
                        $cliente->DNI = $data->DNI;
                        $cliente->Nombre = $data->Nombre;
                        $cliente->Apellidos = $data->Apellidos;
                        $cliente->Telefono = $data->Telefono;
                        $cliente->FechaNacimiento = $data->FechaNacimiento;
                        $cliente->Email = $data->Email;
                        $cliente->Contrasena = $data->Contrasena;
                        $cliente->CodigoPostal = $data->CodigoPostal;
                        $cliente->Genero = $data->Genero;
                        
                        if($cliente->emailExists()) {
                            Response::error("El email ya está registrado", 400);
                        }
                        
                        if($cliente->create()) {
                            Response::success(['DNI' => $cliente->DNI], "Cliente creado exitosamente", 201);
                        } else {
                            Response::error("Error al crear el cliente", 500);
                        }
                    }
                    break;
                    
                case 'PUT':
                    if(!$id) Response::error("DNI requerido", 400);
                    $cliente->DNI = $id;
                    $cliente->Nombre = $data->Nombre;
                    $cliente->Apellidos = $data->Apellidos;
                    $cliente->Telefono = $data->Telefono;
                    $cliente->Email = $data->Email;
                    $cliente->CodigoPostal = $data->CodigoPostal;
                    if($cliente->update()) {
                        Response::success(null, "Cliente actualizado exitosamente");
                    } else {
                        Response::error("Error al actualizar el cliente", 500);
                    }
                    break;
                    
                case 'DELETE':
                    if(!$id) Response::error("DNI requerido", 400);
                    $cliente->DNI = $id;
                    if($cliente->delete()) {
                        Response::success(null, "Cliente eliminado exitosamente");
                    } else {
                        Response::error("Error al eliminar el cliente", 500);
                    }
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'bonos':
            require_once 'models/Bono.php';
            $bono = new Bono($db);
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        $bono->tipo = $id;
                        $bono->numDias = $subresource;
                        $stmt = $bono->getByTipoAndDias();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Bono no encontrado", 404);
                        }
                    } else {
                        $stmt = $bono->getAll();
                        $bonos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($bonos);
                    }
                    break;
                case 'POST':
                    $bono->tipo = $data->tipo;
                    $bono->numDias = $data->numDias;
                    $bono->descripcion = $data->descripcion;
                    $bono->precio = $data->precio;
                    $bono->foto = $data->foto;
                    if($bono->create()) {
                        Response::success(['tipo' => $bono->tipo], "Bono creado exitosamente", 201);
                    } else {
                        Response::error("Error al crear el bono", 500);
                    }
                    break;
                case 'PUT':
                    if(!$id || !$subresource) Response::error("Tipo y días requeridos", 400);
                    $bono->tipo = $id;
                    $bono->numDias = $subresource;
                    $bono->descripcion = $data->descripcion;
                    $bono->precio = $data->precio;
                    $bono->foto = $data->foto;
                    if($bono->update()) {
                        Response::success(null, "Bono actualizado");
                    } else {
                        Response::error("Error al actualizar", 500);
                    }
                    break;
                case 'DELETE':
                    if(!$id || !$subresource) Response::error("Datos requeridos", 400);
                    $bono->tipo = $id;
                    $bono->numDias = $subresource;
                    if($bono->delete()) {
                        Response::success(null, "Bono eliminado");
                    } else {
                        Response::error("Error al eliminar", 500);
                    }
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'cursos':
            require_once 'models/Curso.php';
            $curso = new Curso($db);
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        $curso->TipoBaile = urldecode($id);
                        $curso->Nivel = urldecode($subresource);
                        $stmt = $curso->getByTipoAndNivel();
                        if($stmt->rowCount() > 0) Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        else Response::error("Curso no encontrado", 404);
                    } else {
                        $stmt = $curso->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                case 'POST':
                    $curso->TipoBaile = $data->TipoBaile;
                    $curso->Nivel = $data->Nivel;
                    $curso->Descripcion = $data->Descripcion;
                    $curso->Aforo = $data->Aforo;
                    $curso->Foto = $data->Foto;
                    if($curso->create()) Response::success(null, "Curso creado", 201);
                    else Response::error("Error al crear", 500);
                    break;
                case 'PUT':
                    if(!$id || !$subresource) Response::error("Datos requeridos", 400);
                    $curso->TipoBaile = urldecode($id);
                    $curso->Nivel = urldecode($subresource);
                    $curso->Descripcion = $data->Descripcion;
                    $curso->Aforo = $data->Aforo;
                    $curso->Foto = $data->Foto;
                    if($curso->update()) Response::success(null, "Curso actualizado");
                    else Response::error("Error al actualizar", 500);
                    break;
                case 'DELETE':
                    if(!$id || !$subresource) Response::error("Datos requeridos", 400);
                    $curso->TipoBaile = urldecode($id);
                    $curso->Nivel = urldecode($subresource);
                    if($curso->delete()) Response::success(null, "Curso eliminado");
                    else Response::error("Error al eliminar", 500);
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'clases':
            require_once 'models/Clase.php';
            $clase = new Clase($db);
            switch($requestMethod) {
                case 'GET':
                    if($id === 'disponibles') {
                        $stmt = $clase->getAvailable();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } elseif($id) {
                        $clase->idClase = $id;
                        $stmt = $clase->getById();
                        if($stmt->rowCount() > 0) Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        else Response::error("Clase no encontrada", 404);
                    } else {
                        $stmt = $clase->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                case 'POST':
                    $clase->fechaInicio = $data->fechaInicio;
                    $clase->fechaFin = $data->fechaFin;
                    $clase->baile = $data->baile;
                    $clase->nivel = $data->nivel;
                    if($clase->create()) Response::success(['id' => $clase->idClase], "Clase creada", 201);
                    else Response::error("Error crear clase", 500);
                    break;
                case 'PUT':
                    if(!$id) Response::error("ID requerido", 400);
                    $clase->idClase = $id;
                    $clase->fechaInicio = $data->fechaInicio;
                    $clase->fechaFin = $data->fechaFin;
                    $clase->baile = $data->baile;
                    $clase->nivel = $data->nivel;
                    if($clase->update()) Response::success(null, "Actualizado");
                    else Response::error("Error actualizar", 500);
                    break;
                case 'DELETE':
                    if(!$id) Response::error("ID requerido", 400);
                    $clase->idClase = $id;
                    if($clase->delete()) Response::success(null, "Eliminado");
                    else Response::error("Error eliminar", 500);
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'reservas':
            require_once 'models/Reserva.php';
            $reserva = new Reserva($db);
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        $stmt = $reserva->getByCliente($subresource);
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } elseif($id === 'clase' && $subresource) {
                        $reserva->idClase = $subresource;
                        $stmt = $reserva->getByClase();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } else {
                        $stmt = $reserva->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                case 'POST':
                    $reserva->idClase = $data->idClase;
                    $reserva->idBonoComprado = $data->idBonoComprado;
                    try {
                        if($reserva->create()) Response::success(['id' => $reserva->idReserva], "Reserva creada", 201);
                    } catch(Exception $e) { Response::error($e->getMessage(), 400); }
                    break;
                case 'DELETE':
                    if(!$id) Response::error("ID requerido", 400);
                    $reserva->idReserva = $id;
                    try {
                        if($reserva->delete()) Response::success(null, "Reserva cancelada");
                    } catch(Exception $e) { Response::error($e->getMessage(), 400); }
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'bonos-comprados':
            require_once 'models/BonoComprado.php';
            $bonoComprado = new BonoComprado($db);
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        $stmt = $bonoComprado->getByCliente($subresource);
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } elseif($id === 'activos' && $subresource) {
                        $stmt = $bonoComprado->getActiveByCliente($subresource);
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } elseif($id) {
                        $bonoComprado->idBonoComprado = $id;
                        $stmt = $bonoComprado->getById();
                        if($stmt->rowCount() > 0) Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        else Response::error("No encontrado", 404);
                    } else {
                        $stmt = $bonoComprado->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'transacciones':
            require_once 'models/Transaccion.php';
            $transaccion = new Transaccion($db);
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        $stmt = $transaccion->getByCliente($subresource);
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } else {
                        $stmt = $transaccion->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                case 'POST':
                    $transaccion->cliente_DNI = $data->cliente_DNI;
                    try {
                        $result = $transaccion->createWithBono($data->bono_tipo, $data->bono_numDias);
                        Response::success($result, "Compra realizada", 201);
                    } catch(Exception $e) { Response::error($e->getMessage(), 400); }
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        case 'eventos':
            require_once 'models/Evento.php';
            $evento = new Evento($db);
            switch($requestMethod) {
                case 'GET':
                    if($id === 'proximos') {
                        $stmt = $evento->getUpcoming();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } elseif($id) {
                        $evento->idEvento = $id;
                        $stmt = $evento->getById();
                        if($stmt->rowCount() > 0) Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        else Response::error("No encontrado", 404);
                    } else {
                        $stmt = $evento->getAll();
                        Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
                    }
                    break;
                case 'POST':
                    $evento->TítuloEvento = $data->TítuloEvento;
                    $evento->FechaEvento = $data->FechaEvento;
                    $evento->URLFoto = $data->URLFoto;
                    $evento->descripciónEvento = $data->descripciónEvento;
                    $evento->enlaceEvento = $data->enlaceEvento;
                    if($evento->create()) Response::success(null, "Evento creado", 201);
                    else Response::error("Error crear", 500);
                    break;
                case 'PUT':
                    if(!$id) Response::error("ID requerido", 400);
                    $evento->idEvento = $id;
                    $evento->TítuloEvento = $data->TítuloEvento;
                    $evento->FechaEvento = $data->FechaEvento;
                    $evento->URLFoto = $data->URLFoto;
                    $evento->descripciónEvento = $data->descripciónEvento;
                    $evento->enlaceEvento = $data->enlaceEvento;
                    if($evento->update()) Response::success(null, "Evento actualizado");
                    else Response::error("Error actualizar", 500);
                    break;
                case 'DELETE':
                    if(!$id) Response::error("ID requerido", 400);
                    $evento->idEvento = $id;
                    if($evento->delete()) Response::success(null, "Evento eliminado");
                    else Response::error("Error eliminar", 500);
                    break;
                default: Response::error("Método no permitido", 405);
            }
            break;
            
        default:
            Response::error("Recurso '{$resource}' no encontrado", 404);
    }
    
} catch(Exception $e) {
    Response::error("Error interno del servidor: " . $e->getMessage(), 500);
}
?>