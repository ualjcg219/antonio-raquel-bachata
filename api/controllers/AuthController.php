<?php
// api/controllers/AuthController.php

include_once 'models/Cliente.php';
require_once '../vendor/autoload.php'; // Para JWT
use \Firebase\JWT\JWT;

class AuthController {
    private $db;
    private $cliente;

    public function __construct($db) {
        $this->db = $db;
        $this->cliente = new Cliente($db);
    }

    // Login de cliente/admin
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->Email) && !empty($data->Contrasena)) {
            $this->cliente->Email = $data->Email;
            $this->cliente->Contrasena = $data->Contrasena;

            if($this->cliente->login()) {

                // Verificar si es el admin HAY QUE SUSITUIR EL CORREO Y LA CONTRASEÑA POR LOS REALES
                $isAdmin = false;
                if($this->cliente->Email === 'admin@mail.com' && $this->cliente->Contrasena === '123456') {
                    $isAdmin = true;
                }

                // Generar JWT
                $payload = [
                    'iss' => 'tu-dominio.com',
                    'iat' => time(),
                    'exp' => time() + 3600, // Token válido 1 hora
                    'data' => [
                        'DNI' => $this->cliente->DNI,
                        'Email' => $this->cliente->Email,
                        'Nombre' => $this->cliente->Nombre,
                        'isAdmin' => $isAdmin
                    ]
                ];

                $jwt = JWT::encode($payload, SECRET_KEY, 'HS256');

                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login exitoso.",
                    "token" => $jwt,
                    "isAdmin" => $isAdmin,
                    "cliente" => array(
                        "DNI" => $this->cliente->DNI,
                        "Nombre" => $this->cliente->Nombre,
                        "Apellidos" => $this->cliente->Apellidos,
                        "Email" => $this->cliente->Email,
                        "Telefono" => $this->cliente->Telefono,
                        "FechaNacimiento" => $this->cliente->FechaNacimiento,
                        "CodigoPostal" => $this->cliente->CodigoPostal,
                        "Genero" => $this->cliente->Genero
                    )
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Credenciales incorrectas."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Email y contraseña son requeridos."));
        }
    }

    // Métodos vacíos requeridos por la interfaz del router
    public function read() {}
    public function readOne($id) {}
    public function create() {}
    public function update($id) {}
    public function delete($id) {}
    public function customAction($action, $id) {}
}
?>
