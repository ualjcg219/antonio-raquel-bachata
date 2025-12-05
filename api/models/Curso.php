<?php
// api/models/Curso.php

class Curso {
    private $conn;
    private $table_name = "Curso";

    public $TipoBaile;
    public $Nivel;
    public $Descripcion;
    public $Aforo;
    public $Foto;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los cursos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un curso específico
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE TipoBaile = ? AND Nivel = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->bindParam(2, $this->Nivel);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->Descripcion = $row['Descripcion'];
            $this->Aforo = $row['Aforo'];
            $this->Foto = $row['Foto'];
            return true;
        }
        
        return false;
    }

    // Crear un nuevo curso
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET TipoBaile=:tipoBaile, Nivel=:nivel, 
                    Descripcion=:descripcion, Aforo=:aforo, Foto=:foto";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->TipoBaile = htmlspecialchars(strip_tags($this->TipoBaile));
        $this->Nivel = htmlspecialchars(strip_tags($this->Nivel));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        $this->Aforo = htmlspecialchars(strip_tags($this->Aforo));
        $this->Foto = htmlspecialchars(strip_tags($this->Foto));

        // Bind
        $stmt->bindParam(":tipoBaile", $this->TipoBaile);
        $stmt->bindParam(":nivel", $this->Nivel);
        $stmt->bindParam(":descripcion", $this->Descripcion);
        $stmt->bindParam(":aforo", $this->Aforo);
        $stmt->bindParam(":foto", $this->Foto);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Actualizar un curso
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET Descripcion=:descripcion, Aforo=:aforo, Foto=:foto
                WHERE TipoBaile=:tipoBaile AND Nivel=:nivel";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->TipoBaile = htmlspecialchars(strip_tags($this->TipoBaile));
        $this->Nivel = htmlspecialchars(strip_tags($this->Nivel));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        $this->Aforo = htmlspecialchars(strip_tags($this->Aforo));
        $this->Foto = htmlspecialchars(strip_tags($this->Foto));

        // Bind
        $stmt->bindParam(":tipoBaile", $this->TipoBaile);
        $stmt->bindParam(":nivel", $this->Nivel);
        $stmt->bindParam(":descripcion", $this->Descripcion);
        $stmt->bindParam(":aforo", $this->Aforo);
        $stmt->bindParam(":foto", $this->Foto);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar un curso
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE TipoBaile = ? AND Nivel = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->TipoBaile = htmlspecialchars(strip_tags($this->TipoBaile));
        $this->Nivel = htmlspecialchars(strip_tags($this->Nivel));
        
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->bindParam(2, $this->Nivel);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener cursos por tipo de baile
    public function readByTipo() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE TipoBaile = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->execute();
        return $stmt;
    }
}
?>