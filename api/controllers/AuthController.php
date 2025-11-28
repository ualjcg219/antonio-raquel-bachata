<?php
// api/controllers/AuthController.php

include_once 'models/Cliente.php';

class AuthController {
    private $db;
    private $cliente;

    public function __construct($db) {
        $this->db = $db;
        $this->cliente = new Cliente($db);
    }

    // Login de cliente
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->Email) && !empty($data->Contrasena)) {
            
            $this->cliente->Email = $data->Email;
            $this->cliente->Contrasena = $data->Contrasena;

            if($this->cliente->login()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login exitoso.",
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

    // Métodos no utilizados pero requeridos por la interfaz del router
    public function read() {}
    public function readOne($id) {}
    public function create() {}
    public function update($id) {}
    public function delete($id) {}
    public function customAction($action, $id) {}
}
?>