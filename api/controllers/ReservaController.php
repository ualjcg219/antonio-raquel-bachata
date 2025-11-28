<?php
// api/controllers/ReservaController.php

include_once 'models/Reserva.php';

class ReservaController {
    private $db;
    private $reserva;

    public function __construct($db) {
        $this->db = $db;
        $this->reserva = new Reserva($db);
    }

    // Obtener todas las reservas
    public function read() {
        $stmt = $this->reserva->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $reservas_arr = array();
            $reservas_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($reservas_arr["records"], $row);
            }

            http_response_code(200);
            echo json_encode($reservas_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron reservas."));
        }
    }

    // Obtener una reserva específica
    public function readOne($id) {
        $this->reserva->idReserva = $id;

        if($this->reserva->readOne()) {
            $reserva_arr = array(
                "idReserva" => $this->reserva->idReserva,
                "Cliente_DNI" => $this->reserva->Cliente_DNI,
                "Clase_del_Dia_horaInicio" => $this->reserva->Clase_del_Dia_horaInicio,
                "Clase_del_Dia_dia_NumeroDia" => $this->reserva->Clase_del_Dia_dia_NumeroDia
            );

            http_response_code(200);
            echo json_encode($reserva_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Reserva no encontrada."));
        }
    }

    // Crear una nueva reserva
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->Cliente_DNI) && !empty($data->Clase_del_Dia_horaInicio) && 
           isset($data->Clase_del_Dia_dia_NumeroDia)) {
            
            // Generar ID automático
            $this->reserva->idReserva = $this->reserva->generateId();
            $this->reserva->Cliente_DNI = $data->Cliente_DNI;
            $this->reserva->Clase_del_Dia_horaInicio = $data->Clase_del_Dia_horaInicio;
            $this->reserva->Clase_del_Dia_dia_NumeroDia = $data->Clase_del_Dia_dia_NumeroDia;

            // Verificar disponibilidad
            if(!$this->reserva->checkDisponibilidad()) {
                http_response_code(409);
                echo json_encode(array("message" => "La clase está llena."));
                return;
            }

            if($this->reserva->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Reserva creada exitosamente.",
                    "idReserva" => $this->reserva->idReserva
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear la reserva."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
    }

    // Actualizar una reserva (generalmente no se hace, pero está disponible)
    public function update($id) {
        http_response_code(405);
        echo json_encode(array("message" => "No se permite actualizar reservas. Elimine y cree una nueva."));
    }

    // Eliminar una reserva (cancelar)
    public function delete($id) {
        $this->reserva->idReserva = $id;

        if($this->reserva->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Reserva cancelada exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo cancelar la reserva."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $id) {
        switch($action) {
            case 'disponibilidad':
                // GET /reservas/{horaInicio}-{numeroDia}/disponibilidad
                $parts = explode('-', $id);
                if(count($parts) < 2) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Formato inválido. Use: horaInicio-numeroDia"));
                    return;
                }
                
                $this->reserva->Clase_del_Dia_horaInicio = urldecode($parts[0]);
                $this->reserva->Clase_del_Dia_dia_NumeroDia = urldecode($parts[1]);
                
                $disponible = $this->reserva->checkDisponibilidad();
                
                http_response_code(200);
                echo json_encode(array("disponible" => $disponible));
                break;
            
            default:
                http_response_code(404);
                echo json_encode(array("message" => "Acción no encontrada."));
                break;
        }
    }
}
?>