<?php
// api/models/Curso.php

class Curso {
    private $conn;
    private $table = 'curso';

    public $TipoBaile;
    public $Nivel;
    public $Descripcion;
    public $Aforo;
    public $Foto;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los cursos
    public function getAll() {
        $query = "SELECT TipoBaile, Nivel, Descripcion, Aforo, Foto FROM " . $this->table . " 
                  ORDER BY TipoBaile, Nivel";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener curso específico
    public function getByTipoAndNivel() {
        $query = "SELECT TipoBaile, Nivel, Descripcion, Aforo, Foto FROM " . $this->table . " 
                  WHERE TipoBaile = ? AND Nivel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->bindParam(2, $this->Nivel);
        $stmt->execute();
        return $stmt;
    }

    // Crear curso
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (TipoBaile, Nivel, Descripcion, Aforo, Foto)
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->bindParam(2, $this->Nivel);
        $stmt->bindParam(3, $this->Descripcion);
        $stmt->bindParam(4, $this->Aforo);
        $stmt->bindParam(5, $this->Foto);
        
        return $stmt->execute();
    }

    // Actualizar curso
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET Descripcion = ?, Aforo = ?, Foto = ?
                  WHERE TipoBaile = ? AND Nivel = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->Descripcion);
        $stmt->bindParam(2, $this->Aforo);
        $stmt->bindParam(3, $this->Foto);
        $stmt->bindParam(4, $this->TipoBaile);
        $stmt->bindParam(5, $this->Nivel);
        
        return $stmt->execute();
    }

    // Eliminar curso
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE TipoBaile = ? AND Nivel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->TipoBaile);
        $stmt->bindParam(2, $this->Nivel);
        return $stmt->execute();
    }
}
?>