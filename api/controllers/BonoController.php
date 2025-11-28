<?php
// api/controllers/BonoController.php

include_once 'models/Bono.php';

class BonoController {
    private $db;
    private $bono;

    public function __construct($db) {
        $this->db = $db;
        $this->bono = new Bono($db);
    }

    // Obtener todos los bonos
    public function read() {
        $stmt = $this->bono->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $bonos_arr = array();
            $bonos_arr["records"] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $bono_item = array(
                    "tipo" => $tipo,
                    "numDias" => $numDias,
                    "descripcion" => $descripcion,
                    "precio" => $precio,
                    "foto" => $foto,
                    "mes_NombreMes" => $mes_NombreMes
                );

                array_push($bonos_arr["records"], $bono_item);
            }

            http_response_code(200);
            echo json_encode($bonos_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron bonos."));
        }
    }

    // Obtener un bono específico (formato: tipo-numDias)
    public function readOne($id) {
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: tipo-numDias"));
            return;
        }

        $this->bono->tipo = urldecode($parts[0]);
        $this->bono->numDias = urldecode($parts[1]);

        if($this->bono->readOne()) {
            $bono_arr = array(
                "tipo" => $this->bono->tipo,
                "numDias" => $this->bono->numDias,
                "descripcion" => $this->bono->descripcion,
                "precio" => $this->bono->precio,
                "foto" => $this->bono->foto,
                "mes_NombreMes" => $this->bono->mes_NombreMes
            );

            http_response_code(200);
            echo json_encode($bono_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Bono no encontrado."));
        }
    }

    // Crear un nuevo bono
    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->tipo) && isset($data->numDias) && 
           !empty($data->descripcion) && isset($data->precio) && 
           !empty($data->foto) && !empty($data->mes_NombreMes)) {
            
            $this->bono->tipo = $data->tipo;
            $this->bono->numDias = $data->numDias;
            $this->bono->descripcion = $data->descripcion;
            $this->bono->precio = $data->precio;
            $this->bono->foto = $data->foto;
            $this->bono->mes_NombreMes = $data->mes_NombreMes;

            if($this->bono->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Bono creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el bono."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
    }

    // Actualizar un bono
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));

        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: tipo-numDias"));
            return;
        }

        $this->bono->tipo = urldecode($parts[0]);
        $this->bono->numDias = urldecode($parts[1]);

        if(!empty($data->descripcion)) $this->bono->descripcion = $data->descripcion;
        if(isset($data->precio)) $this->bono->precio = $data->precio;
        if(!empty($data->foto)) $this->bono->foto = $data->foto;
        if(!empty($data->mes_NombreMes)) $this->bono->mes_NombreMes = $data->mes_NombreMes;

        if($this->bono->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Bono actualizado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar el bono."));
        }
    }

    // Eliminar un bono
    public function delete($id) {
        $parts = explode('-', $id);
        
        if(count($parts) < 2) {
            http_response_code(400);
            echo json_encode(array("message" => "Formato de ID inválido. Use: tipo-numDias"));
            return;
        }

        $this->bono->tipo = urldecode($parts[0]);
        $this->bono->numDias = urldecode($parts[1]);

        if($this->bono->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Bono eliminado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el bono."));
        }
    }

    // Acciones personalizadas
    public function customAction($action, $id) {
        http_response_code(404);
        echo json_encode(array("message" => "Acción no encontrada."));
    }
}
?>