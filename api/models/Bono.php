<?php
// api/models/Bono.php

class Bono {
    private $conn;
    private $table = 'bono';

    public $tipo;
    public $numDias;
    public $descripcion;
    public $precio;
    public $foto;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los bonos
    public function getAll() {
        $query = "SELECT tipo, numDias, descripcion, precio, foto FROM " . $this->table . " ORDER BY precio";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener bono específico
    public function getByTipoAndDias() {
        $query = "SELECT tipo, numDias, descripcion, precio, foto FROM " . $this->table . " 
                  WHERE tipo = ? AND numDias = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tipo);
        $stmt->bindParam(2, $this->numDias);
        $stmt->execute();
        return $stmt;
    }

    // Crear bono
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (tipo, numDias, descripcion, precio, foto)
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->tipo);
        $stmt->bindParam(2, $this->numDias);
        $stmt->bindParam(3, $this->descripcion);
        $stmt->bindParam(4, $this->precio);
        $stmt->bindParam(5, $this->foto);
        
        return $stmt->execute();
    }

    // Actualizar bono
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET descripcion = ?, precio = ?, foto = ?
                  WHERE tipo = ? AND numDias = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->descripcion);
        $stmt->bindParam(2, $this->precio);
        $stmt->bindParam(3, $this->foto);
        $stmt->bindParam(4, $this->tipo);
        $stmt->bindParam(5, $this->numDias);
        
        return $stmt->execute();
    }

    // Eliminar bono
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE tipo = ? AND numDias = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tipo);
        $stmt->bindParam(2, $this->numDias);
        return $stmt->execute();
    }
}
?>