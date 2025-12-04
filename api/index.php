<?php
// api/index.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/Database.php';
require_once 'utils/Response.php';

// Obtener la URI y el método
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Remover elementos vacíos
$uri = array_values(array_filter($uri));

// Obtener el recurso y el ID si existe
$resource = isset($uri[1]) ? $uri[1] : null;
$id = isset($uri[2]) ? $uri[2] : null;
$subresource = isset($uri[3]) ? $uri[3] : null;

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

if(!$db) {
    Response::error("Error de conexión a la base de datos", 500);
}

// Obtener datos del body
$data = json_decode(file_get_contents("php://input"));

// Enrutamiento
try {
    switch($resource) {
        case 'clientes':
            require_once 'models/Cliente.php';
            $cliente = new Cliente($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        // GET /api/clientes/{dni}
                        $cliente->DNI = $id;
                        $stmt = $cliente->getByDNI();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Cliente no encontrado", 404);
                        }
                    } else {
                        // GET /api/clientes
                        $stmt = $cliente->getAll();
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($clientes);
                    }
                    break;
                    
                case 'POST':
                    if($subresource === 'login') {
                        // POST /api/clientes/login
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
                        // POST /api/clientes
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
                    // PUT /api/clientes/{dni}
                    if(!$id) {
                        Response::error("DNI requerido", 400);
                    }
                    
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
                    // DELETE /api/clientes/{dni}
                    if(!$id) {
                        Response::error("DNI requerido", 400);
                    }
                    
                    $cliente->DNI = $id;
                    if($cliente->delete()) {
                        Response::success(null, "Cliente eliminado exitosamente");
                    } else {
                        Response::error("Error al eliminar el cliente", 500);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'bonos':
            require_once 'models/Bono.php';
            $bono = new Bono($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        // GET /api/bonos/{tipo}/{numDias}
                        $bono->tipo = $id;
                        $bono->numDias = $subresource;
                        $stmt = $bono->getByTipoAndDias();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Bono no encontrado", 404);
                        }
                    } else {
                        // GET /api/bonos
                        $stmt = $bono->getAll();
                        $bonos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($bonos);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/bonos
                    $bono->tipo = $data->tipo;
                    $bono->numDias = $data->numDias;
                    $bono->descripcion = $data->descripcion;
                    $bono->precio = $data->precio;
                    $bono->foto = $data->foto;
                    
                    if($bono->create()) {
                        Response::success(['tipo' => $bono->tipo, 'numDias' => $bono->numDias], 
                                        "Bono creado exitosamente", 201);
                    } else {
                        Response::error("Error al crear el bono", 500);
                    }
                    break;
                    
                case 'PUT':
                    // PUT /api/bonos/{tipo}/{numDias}
                    if(!$id || !$subresource) {
                        Response::error("Tipo y número de días requeridos", 400);
                    }
                    
                    $bono->tipo = $id;
                    $bono->numDias = $subresource;
                    $bono->descripcion = $data->descripcion;
                    $bono->precio = $data->precio;
                    $bono->foto = $data->foto;
                    
                    if($bono->update()) {
                        Response::success(null, "Bono actualizado exitosamente");
                    } else {
                        Response::error("Error al actualizar el bono", 500);
                    }
                    break;
                    
                case 'DELETE':
                    // DELETE /api/bonos/{tipo}/{numDias}
                    if(!$id || !$subresource) {
                        Response::error("Tipo y número de días requeridos", 400);
                    }
                    
                    $bono->tipo = $id;
                    $bono->numDias = $subresource;
                    if($bono->delete()) {
                        Response::success(null, "Bono eliminado exitosamente");
                    } else {
                        Response::error("Error al eliminar el bono", 500);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'cursos':
            require_once 'models/Curso.php';
            $curso = new Curso($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id) {
                        // GET /api/cursos/{tipoBaile}/{nivel}
                        $curso->TipoBaile = urldecode($id);
                        $curso->Nivel = urldecode($subresource);
                        $stmt = $curso->getByTipoAndNivel();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Curso no encontrado", 404);
                        }
                    } else {
                        // GET /api/cursos
                        $stmt = $curso->getAll();
                        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($cursos);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/cursos
                    $curso->TipoBaile = $data->TipoBaile;
                    $curso->Nivel = $data->Nivel;
                    $curso->Descripcion = $data->Descripcion;
                    $curso->Aforo = $data->Aforo;
                    $curso->Foto = $data->Foto;
                    
                    if($curso->create()) {
                        Response::success(['TipoBaile' => $curso->TipoBaile, 'Nivel' => $curso->Nivel], 
                                        "Curso creado exitosamente", 201);
                    } else {
                        Response::error("Error al crear el curso", 500);
                    }
                    break;
                    
                case 'PUT':
                    // PUT /api/cursos/{tipoBaile}/{nivel}
                    if(!$id || !$subresource) {
                        Response::error("Tipo de baile y nivel requeridos", 400);
                    }
                    
                    $curso->TipoBaile = urldecode($id);
                    $curso->Nivel = urldecode($subresource);
                    $curso->Descripcion = $data->Descripcion;
                    $curso->Aforo = $data->Aforo;
                    $curso->Foto = $data->Foto;
                    
                    if($curso->update()) {
                        Response::success(null, "Curso actualizado exitosamente");
                    } else {
                        Response::error("Error al actualizar el curso", 500);
                    }
                    break;
                    
                case 'DELETE':
                    // DELETE /api/cursos/{tipoBaile}/{nivel}
                    if(!$id || !$subresource) {
                        Response::error("Tipo de baile y nivel requeridos", 400);
                    }
                    
                    $curso->TipoBaile = urldecode($id);
                    $curso->Nivel = urldecode($subresource);
                    if($curso->delete()) {
                        Response::success(null, "Curso eliminado exitosamente");
                    } else {
                        Response::error("Error al eliminar el curso", 500);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'clases':
            require_once 'models/Clase.php';
            $clase = new Clase($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id === 'disponibles') {
                        // GET /api/clases/disponibles
                        $stmt = $clase->getAvailable();
                        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($clases);
                    } elseif($id) {
                        // GET /api/clases/{id}
                        $clase->idClase = $id;
                        $stmt = $clase->getById();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Clase no encontrada", 404);
                        }
                    } else {
                        // GET /api/clases
                        $stmt = $clase->getAll();
                        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($clases);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/clases
                    $clase->fechaInicio = $data->fechaInicio;
                    $clase->fechaFin = $data->fechaFin;
                    $clase->baile = $data->baile;
                    $clase->nivel = $data->nivel;
                    
                    if($clase->create()) {
                        Response::success(['idClase' => $clase->idClase], "Clase creada exitosamente", 201);
                    } else {
                        Response::error("Error al crear la clase", 500);
                    }
                    break;
                    
                case 'PUT':
                    // PUT /api/clases/{id}
                    if(!$id) {
                        Response::error("ID de clase requerido", 400);
                    }
                    
                    $clase->idClase = $id;
                    $clase->fechaInicio = $data->fechaInicio;
                    $clase->fechaFin = $data->fechaFin;
                    $clase->baile = $data->baile;
                    $clase->nivel = $data->nivel;
                    
                    if($clase->update()) {
                        Response::success(null, "Clase actualizada exitosamente");
                    } else {
                        Response::error("Error al actualizar la clase", 500);
                    }
                    break;
                    
                case 'DELETE':
                    // DELETE /api/clases/{id}
                    if(!$id) {
                        Response::error("ID de clase requerido", 400);
                    }
                    
                    $clase->idClase = $id;
                    if($clase->delete()) {
                        Response::success(null, "Clase eliminada exitosamente");
                    } else {
                        Response::error("Error al eliminar la clase", 500);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'reservas':
            require_once 'models/Reserva.php';
            $reserva = new Reserva($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        // GET /api/reservas/cliente/{dni}
                        $stmt = $reserva->getByCliente($subresource);
                        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($reservas);
                    } elseif($id === 'clase' && $subresource) {
                        // GET /api/reservas/clase/{idClase}
                        $reserva->idClase = $subresource;
                        $stmt = $reserva->getByClase();
                        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($reservas);
                    } else {
                        // GET /api/reservas
                        $stmt = $reserva->getAll();
                        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($reservas);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/reservas
                    $reserva->idClase = $data->idClase;
                    $reserva->idBonoComprado = $data->idBonoComprado;
                    
                    try {
                        if($reserva->create()) {
                            Response::success(['idReserva' => $reserva->idReserva], 
                                            "Reserva creada exitosamente", 201);
                        }
                    } catch(Exception $e) {
                        Response::error($e->getMessage(), 400);
                    }
                    break;
                    
                case 'DELETE':
                    // DELETE /api/reservas/{id}
                    if(!$id) {
                        Response::error("ID de reserva requerido", 400);
                    }
                    
                    $reserva->idReserva = $id;
                    try {
                        if($reserva->delete()) {
                            Response::success(null, "Reserva cancelada exitosamente");
                        }
                    } catch(Exception $e) {
                        Response::error($e->getMessage(), 400);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'bonos-comprados':
            require_once 'models/BonoComprado.php';
            $bonoComprado = new BonoComprado($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        // GET /api/bonos-comprados/cliente/{dni}
                        $stmt = $bonoComprado->getByCliente($subresource);
                        $bonos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($bonos);
                    } elseif($id === 'activos' && $subresource) {
                        // GET /api/bonos-comprados/activos/{dni}
                        $stmt = $bonoComprado->getActiveByCliente($subresource);
                        $bonos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($bonos);
                    } elseif($id) {
                        // GET /api/bonos-comprados/{id}
                        $bonoComprado->idBonoComprado = $id;
                        $stmt = $bonoComprado->getById();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Bono comprado no encontrado", 404);
                        }
                    } else {
                        // GET /api/bonos-comprados
                        $stmt = $bonoComprado->getAll();
                        $bonos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($bonos);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'transacciones':
            require_once 'models/Transaccion.php';
            $transaccion = new Transaccion($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id === 'cliente' && $subresource) {
                        // GET /api/transacciones/cliente/{dni}
                        $stmt = $transaccion->getByCliente($subresource);
                        $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($transacciones);
                    } else {
                        // GET /api/transacciones
                        $stmt = $transaccion->getAll();
                        $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($transacciones);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/transacciones
                    $transaccion->cliente_DNI = $data->cliente_DNI;
                    
                    try {
                        $result = $transaccion->createWithBono($data->bono_tipo, $data->bono_numDias);
                        Response::success($result, "Compra realizada exitosamente", 201);
                    } catch(Exception $e) {
                        Response::error($e->getMessage(), 400);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        case 'eventos':
            require_once 'models/Evento.php';
            $evento = new Evento($db);
            
            switch($requestMethod) {
                case 'GET':
                    if($id === 'proximos') {
                        // GET /api/eventos/proximos
                        $stmt = $evento->getUpcoming();
                        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($eventos);
                    } elseif($id) {
                        // GET /api/eventos/{id}
                        $evento->idEvento = $id;
                        $stmt = $evento->getById();
                        if($stmt->rowCount() > 0) {
                            Response::success($stmt->fetch(PDO::FETCH_ASSOC));
                        } else {
                            Response::error("Evento no encontrado", 404);
                        }
                    } else {
                        // GET /api/eventos
                        $stmt = $evento->getAll();
                        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        Response::success($eventos);
                    }
                    break;
                    
                case 'POST':
                    // POST /api/eventos
                    $evento->TítuloEvento = $data->TítuloEvento;
                    $evento->FechaEvento = $data->FechaEvento;
                    $evento->URLFoto = $data->URLFoto;
                    $evento->descripciónEvento = $data->descripciónEvento;
                    $evento->enlaceEvento = $data->enlaceEvento;
                    
                    if($evento->create()) {
                        Response::success(['idEvento' => $evento->idEvento], 
                                        "Evento creado exitosamente", 201);
                    } else {
                        Response::error("Error al crear el evento", 500);
                    }
                    break;
                    
                case 'PUT':
                    // PUT /api/eventos/{id}
                    if(!$id) {
                        Response::error("ID de evento requerido", 400);
                    }
                    
                    $evento->idEvento = $id;
                    $evento->TítuloEvento = $data->TítuloEvento;
                    $evento->FechaEvento = $data->FechaEvento;
                    $evento->URLFoto = $data->URLFoto;
                    $evento->descripciónEvento = $data->descripciónEvento;
                    $evento->enlaceEvento = $data->enlaceEvento;
                    
                    if($evento->update()) {
                        Response::success(null, "Evento actualizado exitosamente");
                    } else {
                        Response::error("Error al actualizar el evento", 500);
                    }
                    break;
                    
                case 'DELETE':
                    // DELETE /api/eventos/{id}
                    if(!$id) {
                        Response::error("ID de evento requerido", 400);
                    }
                    
                    $evento->idEvento = $id;
                    if($evento->delete()) {
                        Response::success(null, "Evento eliminado exitosamente");
                    } else {
                        Response::error("Error al eliminar el evento", 500);
                    }
                    break;
                    
                default:
                    Response::error("Método no permitido", 405);
            }
            break;
            
        default:
            Response::error("Recurso no encontrado", 404);
    }
    
} catch(Exception $e) {
    Response::error("Error interno del servidor: " . $e->getMessage(), 500);
}
?>