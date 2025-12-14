<?php
// api/controllers/AuthController.php

require_once __DIR__ . '/../models/Cliente.php';

class AuthController {
    private $db;
    private $cliente;

    public function __construct($db) {
        $this->db = $db;
        $this->cliente = new Cliente($db);
    }

    // Login de cliente
    public function login() {
        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents("php://input");
        $data = json_decode($raw);

        if (!is_object($data)) {
            http_response_code(400);
            echo json_encode(["message" => "JSON inválido."]);
            return;
        }

        $email = isset($data->Email) ? trim($data->Email) : '';
        $password = isset($data->Contrasena) ? $data->Contrasena : '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["message" => "Email y contraseña son requeridos."]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Email inválido."]);
            return;
        }

        // Obtener usuario por email (el modelo debe devolver el cliente o null/false)
        $user = $this->cliente->getByEmail($email);
        if (!$user) {
            // No damos información detallada para evitar enumeración de usuarios
            http_response_code(401);
            echo json_encode(["message" => "Credenciales incorrectas."]);
            return;
        }

        // $user['Contrasena'] debe ser el hash guardado en la BD
        if (!isset($user['Contrasena']) || !password_verify($password, $user['Contrasena'])) {
            http_response_code(401);
            echo json_encode(["message" => "Credenciales incorrectas."]);
            return;
        }

        // Autenticación ok — poblar propiedades del objeto cliente (si lo necesitas)
        $this->cliente->DNI = $user['DNI'];
        $this->cliente->Nombre = $user['Nombre'];
        $this->cliente->Apellidos = $user['Apellidos'];
        $this->cliente->Email = $user['Email'];
        $this->cliente->Telefono = $user['Telefono'];
        $this->cliente->FechaNacimiento = $user['FechaNacimiento'];
        $this->cliente->CodigoPostal = $user['CodigoPostal'];
        $this->cliente->Genero = $user['Genero'];

        // Opcional: generar y devolver un JWT (recomiendo usar tokens para APIs REST).
        // Si quieres JWT activa el bloque siguiente y asegúrate de instalar firebase/php-jwt con Composer.
        /*
        require_once __DIR__ . '/../../vendor/autoload.php';
        use Firebase\JWT\JWT;
        $secret = getenv('JWT_SECRET') ?: 'cambia_esto';
        $now = time();
        $payload = [
            'iat' => $now,
            'exp' => $now + 60*60*24, // 24h
            'sub' => $this->cliente->DNI,
            'email' => $this->cliente->Email
        ];
        $token = JWT::encode($payload, $secret, 'HS256');
        */

        http_response_code(200);
        echo json_encode([
            "message" => "Login exitoso.",
            //"token" => $token, // activar si usas JWT
            "cliente" => [
                "DNI" => $this->cliente->DNI,
                "Nombre" => $this->cliente->Nombre,
                "Apellidos" => $this->cliente->Apellidos,
                "Email" => $this->cliente->Email,
                "Telefono" => $this->cliente->Telefono,
                "FechaNacimiento" => $this->cliente->FechaNacimiento,
                "CodigoPostal" => $this->cliente->CodigoPostal,
                "Genero" => $this->cliente->Genero
            ]
        ]);
    }

    // Métodos no utilizados pero requeridos por la interfaz del router
    public function read() {}
    public function readOne($id) {}
    public function create() {}
    public function update($id) {}
    public function delete($id) {}
    public function customAction($action, $id) {}
}
?>