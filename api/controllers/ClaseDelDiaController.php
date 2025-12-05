<?php
// api/controllers/ClaseDelDiaController.php

include_once 'models/ClaseDelDia.php';

class ClaseDelDiaController {
    private $db;
    private $clase;

    public function __construct($db) {
        $this->db = $db;
        $this->clase = new ClaseDelDia($db);
    }

    // Obtener todas las clases
    public function read() {
        $stmt = $this->clase->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $clases_arr = array();
            $clases_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($clases_arr["records"], $row);
            }

            http_response_code(200);
            echo json_encode($clases_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron clases."));
        }
    }

    // Obtener una clase específica (formato: horaInicio-numeroDia)
    public function readOne($id) {
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: horaInicio-numeroDia"));
            return;
        }

        $this->clase->horaInicio = urldecode($parts[0]);
        $this->clase->dia_NumeroDia = urldecode($parts[1]);

        if($this->clase->readOne()) {
            $clase_arr = array(
                "horaInicio" => $this->clase->horaInicio,
                "horaFinal" => $this->clase->horaFinal,
                "dia_NumeroDia" => $this->clase->dia_NumeroDia,
                "Curso_TipoBaile" => $this->clase->Curso_TipoBaile,
                "Curso_Nivel" => $this->clase->Curso_Nivel
            );

            http_response_code(200);
            echo json_encode($clase_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Clase no encontrada."));
        }
    }

    // Crear una nueva clase
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->horaInicio) && !empty($data->horaFinal) && 
           isset($data->dia_NumeroDia) && !empty($data->Curso_TipoBaile) && 
           !empty($data->Curso_Nivel)) {
            
            $this->clase->horaInicio = $data->horaInicio;
            $this->clase->horaFinal = $data->horaFinal;
            $this->clase->dia_NumeroDia = $data->dia_NumeroDia;
            $this->clase->Curso_TipoBaile = $data->Curso_TipoBaile;
            $this->clase->Curso_Nivel = $data->Curso_Nivel;

            if($this->clase->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Clase creada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear la clase."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
    }

    // Actualizar una clase
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));

        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: horaInicio-numeroDia"));
            return;
        }

        $this->clase->horaInicio = urldecode($parts[0]);
        $this->clase->dia_NumeroDia = urldecode($parts[1]);

        if(!empty($data->horaFinal)) $this->clase->horaFinal = $data->horaFinal;
        if(!empty($data->Curso_TipoBaile)) $this->clase->Curso_TipoBaile = $data->Curso_TipoBaile;
        if(!empty($data->Curso_Nivel)) $this->clase->Curso_Nivel = $data->Curso_Nivel;

        if($this->clase->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Clase actualizada exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar la clase."));
        }
    }

    // Eliminar una clase
    public function delete($id) {
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: horaInicio-numeroDia"));
            return;
        }

        $this->clase->horaInicio = urldecode($parts[0]);
        $this->clase->dia_NumeroDia = urldecode($parts[1]);

        if($this->clase->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Clase eliminada exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar la clase."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $id) {
        switch($action) {
            case 'dia':
                // GET /clases/{numeroDia}/dia
                $this->clase->dia_NumeroDia = $id;
                $stmt = $this->clase->readByDia();
                
                $clases_arr = array();
                $clases_arr["records"] = array();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($clases_arr["records"], $row);
                }

                http_response_code(200);
                echo json_encode($clases_arr);
                break;
            
            default:
                http_response_code(404);
                echo json_encode(array("message" => "Acción no encontrada."));
                break;
        }
    }
}
?>