<?php
// api/models/Clase.php

class Clase {
    private $conn;
    private $table = 'clase';

    public $idClase;
    public $fechaInicio;
    public $fechaFin;
    public $baile;
    public $nivel;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las clases
    public function getAll() {
        $query = "SELECT c.idClase, c.fechaInicio, c.fechaFin, c.baile, c.nivel,
                  cu.Descripcion, cu.Aforo, cu.Foto,
                  COUNT(r.idReserva) as reservasActuales
                  FROM " . $this->table . " c
                  LEFT JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                  LEFT JOIN reserva r ON c.idClase = r.idClase
                  GROUP BY c.idClase
                  ORDER BY c.fechaInicio";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener clase por ID
    public function getById() {
        $query = "SELECT c.idClase, c.fechaInicio, c.fechaFin, c.baile, c.nivel,
                  cu.Descripcion, cu.Aforo, cu.Foto,
                  COUNT(r.idReserva) as reservasActuales
                  FROM " . $this->table . " c
                  LEFT JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                  LEFT JOIN reserva r ON c.idClase = r.idReserva
                  WHERE c.idClase = ?
                  GROUP BY c.idClase";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idClase);
        $stmt->execute();
        return $stmt;
    }

    // Obtener clases disponibles (próximas)
    public function getAvailable() {
        $query = "SELECT c.idClase, c.fechaInicio, c.fechaFin, c.baile, c.nivel,
                  cu.Descripcion, cu.Aforo, cu.Foto,
                  COUNT(r.idReserva) as reservasActuales
                  FROM " . $this->table . " c
                  LEFT JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                  LEFT JOIN reserva r ON c.idClase = r.idClase
                  WHERE c.fechaInicio > NOW()
                  GROUP BY c.idClase
                  HAVING reservasActuales < cu.Aforo
                  ORDER BY c.fechaInicio";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear clase
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (fechaInicio, fechaFin, baile, nivel)
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->fechaInicio);
        $stmt->bindParam(2, $this->fechaFin);
        $stmt->bindParam(3, $this->baile);
        $stmt->bindParam(4, $this->nivel);
        
        if($stmt->execute()) {
            $this->idClase = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Actualizar clase
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET fechaInicio = ?, fechaFin = ?, baile = ?, nivel = ?
                  WHERE idClase = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->fechaInicio);
        $stmt->bindParam(2, $this->fechaFin);
        $stmt->bindParam(3, $this->baile);
        $stmt->bindParam(4, $this->nivel);
        $stmt->bindParam(5, $this->idClase);
        
        return $stmt->execute();
    }

    // Eliminar clase
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE idClase = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idClase);
        return $stmt->execute();
    }

    // Verificar disponibilidad
    public function checkAvailability() {
        $query = "SELECT cu.Aforo, COUNT(r.idReserva) as reservasActuales
                  FROM " . $this->table . " c
                  LEFT JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                  LEFT JOIN reserva r ON c.idClase = r.idClase
                  WHERE c.idClase = ?
                  GROUP BY c.idClase";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idClase);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['reservasActuales'] < $row['Aforo'];
        }
        return false;
    }
}
?>