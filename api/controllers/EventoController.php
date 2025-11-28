<?php
// api/controllers/EventoController.php

include_once 'models/Evento.php';

class EventoController {
    private $db;
    private $evento;

    public function __construct($db) {
        $this->db = $db;
        $this->evento = new Evento($db);
    }

    // Obtener todos los eventos
    public function read() {
        $stmt = $this->evento->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $eventos_arr = array();
            $eventos_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $evento_item = array(
                    "TituloEvento" => $TítuloEvento,
                    "FechaEvento" => $FechaEvento,
                    "URLFoto" => $URLFoto,
                    "descripcionEvento" => $descripciónEvento,
                    "enlaceEvento" => $enlaceEvento
                );

                array_push($eventos_arr["records"], $evento_item);
            }

            http_response_code(200);
            echo json_encode($eventos_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron eventos."));
        }
    }

    // Obtener un evento específico (formato: titulo-fecha)
    public function readOne($id) {
        $parts = explode('-', $id, 2);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: titulo-fecha"));
            return;
        }

        $this->evento->TituloEvento = urldecode($parts[0]);
        $this->evento->FechaEvento = urldecode($parts[1]);

        if($this->evento->readOne()) {
            $evento_arr = array(
                "TituloEvento" => $this->evento->TituloEvento,
                "FechaEvento" => $this->evento->FechaEvento,
                "URLFoto" => $this->evento->URLFoto,
                "descripcionEvento" => $this->evento->descripcionEvento,
                "enlaceEvento" => $this->evento->enlaceEvento
            );

            http_response_code(200);
            echo json_encode($evento_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Evento no encontrado."));
        }
    }

    // Crear un nuevo evento
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->TituloEvento) && !empty($data->FechaEvento)) {
            
            $this->evento->TituloEvento = $data->TituloEvento;
            $this->evento->FechaEvento = $data->FechaEvento;
            $this->evento->URLFoto = $data->URLFoto ?? null;
            $this->evento->descripcionEvento = $data->descripcionEvento ?? null;
            $this->evento->enlaceEvento = $data->enlaceEvento ?? null;

            if($this->evento->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Evento creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el evento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Título y fecha son requeridos."));
        }
    }

    // Actualizar un evento
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));

        $parts = explode('-', $id, 2);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: titulo-fecha"));
            return;
        }

        $this->evento->TituloEvento = urldecode($parts[0]);
        $this->evento->FechaEvento = urldecode($parts[1]);

        if(isset($data->URLFoto)) $this->evento->URLFoto = $data->URLFoto;
        if(isset($data->descripcionEvento)) $this->evento->descripcionEvento = $data->descripcionEvento;
        if(isset($data->enlaceEvento)) $this->evento->enlaceEvento = $data->enlaceEvento;

        if($this->evento->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Evento actualizado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar el evento."));
        }
    }

    // Eliminar un evento
    public function delete($id) {
        $parts = explode('-', $id, 2);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: titulo-fecha"));
            return;
        }

        $this->evento->TituloEvento = urldecode($parts[0]);
        $this->evento->FechaEvento = urldecode($parts[1]);

        if($this->evento->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Evento eliminado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el evento."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $id) {
        switch($action) {
            case 'proximos':
                $stmt = $this->evento->readProximos();
                $eventos_arr = array();
                $eventos_arr["records"] = array();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($eventos_arr["records"], $row);
                }

                http_response_code(200);
                echo json_encode($eventos_arr);
                break;
            
            case 'pasados':
                $stmt = $this->evento->readPasados();
                $eventos_arr = array();
                $eventos_arr["records"] = array();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($eventos_arr["records"], $row);
                }

                http_response_code(200);
                echo json_encode($eventos_arr);
                break;
            
            default:
                http_response_code(404);
                echo json_encode(array("message" => "Acción no encontrada."));
                break;
        }
    }
}
?>