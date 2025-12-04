<?php
// api/models/Cliente.php

class Cliente {
    private $conn;
    private $table = 'cliente';

    public $DNI;
    public $Nombre;
    public $Apellidos;
    public $Telefono;
    public $FechaNacimiento;
    public $Email;
    public $Contrasena;
    public $CodigoPostal;
    public $Genero;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los clientes
    public function getAll() {
        $query = "SELECT DNI, Nombre, Apellidos, Telefono, FechaNacimiento, Email, CodigoPostal, Genero 
                  FROM " . $this->table . " ORDER BY Nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener cliente por DNI
    public function getByDNI() {
        $query = "SELECT DNI, Nombre, Apellidos, Telefono, FechaNacimiento, Email, CodigoPostal, Genero 
                  FROM " . $this->table . " WHERE DNI = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->DNI);
        $stmt->execute();
        return $stmt;
    }

    // Crear cliente
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (DNI, Nombre, Apellidos, Telefono, FechaNacimiento, Email, Contrasena, CodigoPostal, Genero)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash de la contraseña
        $hashedPassword = password_hash($this->Contrasena, PASSWORD_BCRYPT);
        
        $stmt->bindParam(1, $this->DNI);
        $stmt->bindParam(2, $this->Nombre);
        $stmt->bindParam(3, $this->Apellidos);
        $stmt->bindParam(4, $this->Telefono);
        $stmt->bindParam(5, $this->FechaNacimiento);
        $stmt->bindParam(6, $this->Email);
        $stmt->bindParam(7, $hashedPassword);
        $stmt->bindParam(8, $this->CodigoPostal);
        $stmt->bindParam(9, $this->Genero);
        
        return $stmt->execute();
    }

    // Actualizar cliente
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET Nombre = ?, Apellidos = ?, Telefono = ?, Email = ?, CodigoPostal = ?
                  WHERE DNI = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->Nombre);
        $stmt->bindParam(2, $this->Apellidos);
        $stmt->bindParam(3, $this->Telefono);
        $stmt->bindParam(4, $this->Email);
        $stmt->bindParam(5, $this->CodigoPostal);
        $stmt->bindParam(6, $this->DNI);
        
        return $stmt->execute();
    }

    // Eliminar cliente
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE DNI = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->DNI);
        return $stmt->execute();
    }

    // Login
    public function login($email, $password) {
        $query = "SELECT DNI, Nombre, Apellidos, Email, Contrasena FROM " . $this->table . " WHERE Email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['Contrasena'])) {
                unset($row['Contrasena']);
                return $row;
            }
        }
        return false;
    }

    // Verificar si email existe
    public function emailExists() {
        $query = "SELECT DNI FROM " . $this->table . " WHERE Email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>