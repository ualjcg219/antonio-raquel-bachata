<?php
// api/controllers/CursoController.php

include_once 'models/Curso.php';

class CursoController {
    private $db;
    private $curso;

    public function __construct($db) {
        $this->db = $db;
        $this->curso = new Curso($db);
    }

    // Obtener todos los cursos
    public function read() {
        $stmt = $this->curso->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $cursos_arr = array();
            $cursos_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $curso_item = array(
                    "TipoBaile" => $TipoBaile,
                    "Nivel" => $Nivel,
                    "Descripcion" => $Descripcion,
                    "Aforo" => $Aforo,
                    "Foto" => $Foto
                );

                array_push($cursos_arr["records"], $curso_item);
            }

            http_response_code(200);
            echo json_encode($cursos_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron cursos."));
        }
    }

    // Obtener un curso específico (formato: tipo-nivel)
    public function readOne($id) {
        // El ID viene como "Salsa-Principiante" por ejemplo
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: TipoBaile-Nivel"));
            return;
        }

        $this->curso->TipoBaile = urldecode($parts[0]);
        $this->curso->Nivel = urldecode($parts[1]);

        if($this->curso->readOne()) {
            $curso_arr = array(
                "TipoBaile" => $this->curso->TipoBaile,
                "Nivel" => $this->curso->Nivel,
                "Descripcion" => $this->curso->Descripcion,
                "Aforo" => $this->curso->Aforo,
                "Foto" => $this->curso->Foto
            );

            http_response_code(200);
            echo json_encode($curso_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Curso no encontrado."));
        }
    }

    // Crear un nuevo curso
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->TipoBaile) && !empty($data->Nivel) && 
           !empty($data->Descripcion) && isset($data->Aforo) && !empty($data->Foto)) {
            
            $this->curso->TipoBaile = $data->TipoBaile;
            $this->curso->Nivel = $data->Nivel;
            $this->curso->Descripcion = $data->Descripcion;
            $this->curso->Aforo = $data->Aforo;
            $this->curso->Foto = $data->Foto;

            if($this->curso->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Curso creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el curso."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
    }

    // Actualizar un curso
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));

        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: TipoBaile-Nivel"));
            return;
        }

        $this->curso->TipoBaile = urldecode($parts[0]);
        $this->curso->Nivel = urldecode($parts[1]);

        if(!empty($data->Descripcion)) $this->curso->Descripcion = $data->Descripcion;
        if(isset($data->Aforo)) $this->curso->Aforo = $data->Aforo;
        if(!empty($data->Foto)) $this->curso->Foto = $data->Foto;

        if($this->curso->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Curso actualizado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar el curso."));
        }
    }

    // Eliminar un curso
    public function delete($id) {
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: TipoBaile-Nivel"));
            return;
        }

        $this->curso->TipoBaile = urldecode($parts[0]);
        $this->curso->Nivel = urldecode($parts[1]);

        if($this->curso->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Curso eliminado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el curso."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $id) {
        http_response_code(404);
        echo json_encode(array("message" => "Acción no encontrada."));
    }
}
?>