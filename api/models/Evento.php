<?php
// api/models/Evento.php

class Evento {
    private $conn;
    private $table = 'evento';

    public $idEvento;
    public $TítuloEvento;
    public $FechaEvento;
    public $URLFoto;
    public $descripciónEvento;
    public $enlaceEvento;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los eventos
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY FechaEvento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener eventos próximos
    public function getUpcoming() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE FechaEvento >= CURDATE() 
                  ORDER BY FechaEvento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener evento por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE idEvento = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idEvento);
        $stmt->execute();
        return $stmt;
    }

    // Crear evento
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (TítuloEvento, FechaEvento, URLFoto, descripciónEvento, enlaceEvento)
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->TítuloEvento);
        $stmt->bindParam(2, $this->FechaEvento);
        $stmt->bindParam(3, $this->URLFoto);
        $stmt->bindParam(4, $this->descripciónEvento);
        $stmt->bindParam(5, $this->enlaceEvento);
        
        if($stmt->execute()) {
            $this->idEvento = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Actualizar evento
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET TítuloEvento = ?, FechaEvento = ?, URLFoto = ?, 
                      descripciónEvento = ?, enlaceEvento = ?
                  WHERE idEvento = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->TítuloEvento);
        $stmt->bindParam(2, $this->FechaEvento);
        $stmt->bindParam(3, $this->URLFoto);
        $stmt->bindParam(4, $this->descripciónEvento);
        $stmt->bindParam(5, $this->enlaceEvento);
        $stmt->bindParam(6, $this->idEvento);
        
        return $stmt->execute();
    }

    // Eliminar evento
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE idEvento = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idEvento);
        return $stmt->execute();
    }
}
?>