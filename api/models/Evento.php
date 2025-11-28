<?php
// api/models/Evento.php

class Evento {
    private $conn;
    private $table_name = "Evento";

    public $TituloEvento;
    public $FechaEvento;
    public $URLFoto;
    public $descripcionEvento;
    public $enlaceEvento;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los eventos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY FechaEvento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un evento específico
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE TítuloEvento = ? AND FechaEvento = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->TituloEvento);
        $stmt->bindParam(2, $this->FechaEvento);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->URLFoto = $row['URLFoto'];
            $this->descripcionEvento = $row['descripciónEvento'];
            $this->enlaceEvento = $row['enlaceEvento'];
            return true;
        }
        
        return false;
    }

    // Crear un nuevo evento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET TítuloEvento=:titulo, FechaEvento=:fecha,
                    URLFoto=:urlFoto, descripciónEvento=:descripcion,
                    enlaceEvento=:enlace";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->TituloEvento = htmlspecialchars(strip_tags($this->TituloEvento));
        $this->FechaEvento = htmlspecialchars(strip_tags($this->FechaEvento));
        $this->URLFoto = htmlspecialchars(strip_tags($this->URLFoto));
        $this->descripcionEvento = htmlspecialchars(strip_tags($this->descripcionEvento));
        $this->enlaceEvento = htmlspecialchars(strip_tags($this->enlaceEvento));

        // Bind
        $stmt->bindParam(":titulo", $this->TituloEvento);
        $stmt->bindParam(":fecha", $this->FechaEvento);
        $stmt->bindParam(":urlFoto", $this->URLFoto);
        $stmt->bindParam(":descripcion", $this->descripcionEvento);
        $stmt->bindParam(":enlace", $this->enlaceEvento);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Actualizar un evento
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET URLFoto=:urlFoto, descripciónEvento=:descripcion,
                    enlaceEvento=:enlace
                WHERE TítuloEvento=:titulo AND FechaEvento=:fecha";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->TituloEvento = htmlspecialchars(strip_tags($this->TituloEvento));
        $this->FechaEvento = htmlspecialchars(strip_tags($this->FechaEvento));
        $this->URLFoto = htmlspecialchars(strip_tags($this->URLFoto));
        $this->descripcionEvento = htmlspecialchars(strip_tags($this->descripcionEvento));
        $this->enlaceEvento = htmlspecialchars(strip_tags($this->enlaceEvento));

        // Bind
        $stmt->bindParam(":titulo", $this->TituloEvento);
        $stmt->bindParam(":fecha", $this->FechaEvento);
        $stmt->bindParam(":urlFoto", $this->URLFoto);
        $stmt->bindParam(":descripcion", $this->descripcionEvento);
        $stmt->bindParam(":enlace", $this->enlaceEvento);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar un evento
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE TítuloEvento = ? AND FechaEvento = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->TituloEvento = htmlspecialchars(strip_tags($this->TituloEvento));
        $this->FechaEvento = htmlspecialchars(strip_tags($this->FechaEvento));
        
        $stmt->bindParam(1, $this->TituloEvento);
        $stmt->bindParam(2, $this->FechaEvento);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener eventos próximos
    public function readProximos() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE FechaEvento >= CURDATE() 
                  ORDER BY FechaEvento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener eventos pasados
    public function readPasados() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE FechaEvento < CURDATE() 
                  ORDER BY FechaEvento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>