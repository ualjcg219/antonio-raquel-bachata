<?php
// api/controllers/ClienteController.php

include_once 'models/Cliente.php';

class ClienteController {
    private $db;
    private $cliente;

    public function __construct($db) {
        $this->db = $db;
        $this->cliente = new Cliente($db);
    }

    // Obtener todos los clientes
    public function read() {
        $stmt = $this->cliente->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $clientes_arr = array();
            $clientes_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $cliente_item = array(
                    "DNI" => $DNI,
                    "Nombre" => $Nombre,
                    "Apellidos" => $Apellidos,
                    "Telefono" => $Telefono,
                    "FechaNacimiento" => $FechaNacimiento,
                    "Email" => $Email,
                    "CodigoPostal" => $CodigoPostal,
                    "Genero" => $Genero
                );

                array_push($clientes_arr["records"], $cliente_item);
            }

            http_response_code(200);
            echo json_encode($clientes_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron clientes."));
        }
    }

    // Obtener un cliente específico
    public function readOne($dni) {
        $this->cliente->DNI = $dni;

        if($this->cliente->readOne()) {
            $cliente_arr = array(
                "DNI" => $this->cliente->DNI,
                "Nombre" => $this->cliente->Nombre,
                "Apellidos" => $this->cliente->Apellidos,
                "Telefono" => $this->cliente->Telefono,
                "FechaNacimiento" => $this->cliente->FechaNacimiento,
                "Email" => $this->cliente->Email,
                "CodigoPostal" => $this->cliente->CodigoPostal,
                "Genero" => $this->cliente->Genero
            );

            http_response_code(200);
            echo json_encode($cliente_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Cliente no encontrado."));
        }
    }

    // Crear un nuevo cliente
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->DNI) && !empty($data->Nombre) && !empty($data->Apellidos) &&
           !empty($data->Telefono) && !empty($data->FechaNacimiento) && 
           !empty($data->Email) && !empty($data->Contrasena) && 
           !empty($data->CodigoPostal) && !empty($data->Genero)) {
            
            $this->cliente->DNI = $data->DNI;
            $this->cliente->Nombre = $data->Nombre;
            $this->cliente->Apellidos = $data->Apellidos;
            $this->cliente->Telefono = $data->Telefono;
            $this->cliente->FechaNacimiento = $data->FechaNacimiento;
            $this->cliente->Email = $data->Email;
            $this->cliente->Contrasena = $data->Contrasena;
            $this->cliente->CodigoPostal = $data->CodigoPostal;
            $this->cliente->Genero = $data->Genero;

            if($this->cliente->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Cliente creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el cliente."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
    }

    // Actualizar un cliente
    public function update($dni) {
        $data = json_decode(file_get_contents("php://input"));

        $this->cliente->DNI = $dni;

        if(!empty($data->Nombre)) $this->cliente->Nombre = $data->Nombre;
        if(!empty($data->Apellidos)) $this->cliente->Apellidos = $data->Apellidos;
        if(!empty($data->Telefono)) $this->cliente->Telefono = $data->Telefono;
        if(!empty($data->FechaNacimiento)) $this->cliente->FechaNacimiento = $data->FechaNacimiento;
        if(!empty($data->Email)) $this->cliente->Email = $data->Email;
        if(!empty($data->Contrasena)) $this->cliente->Contrasena = $data->Contrasena;
        if(!empty($data->CodigoPostal)) $this->cliente->CodigoPostal = $data->CodigoPostal;
        if(!empty($data->Genero)) $this->cliente->Genero = $data->Genero;

        if($this->cliente->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Cliente actualizado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar el cliente."));
        }
    }

    // Eliminar un cliente
    public function delete($dni) {
        $this->cliente->DNI = $dni;

        if($this->cliente->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Cliente eliminado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el cliente."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $dni) {
        switch($action) {
            case 'reservas':
                include_once 'models/Reserva.php';
                $reserva = new Reserva($this->db);
                $reserva->Cliente_DNI = $dni;
                $stmt = $reserva->readByCliente();
                
                $reservas_arr = array();
                $reservas_arr["records"] = array();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($reservas_arr["records"], $row);
                }

                http_response_code(200);
                echo json_encode($reservas_arr);
                break;
            
            default:
                http_response_code(404);
                echo json_encode(array("message" => "Acción no encontrada."));
                break;
        }
    }
}
?>