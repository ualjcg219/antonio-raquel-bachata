<?php
// api/models/Cliente.php

class Cliente {
    private $conn;
    private $table_name = "Cliente";

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
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un cliente por DNI
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE DNI = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->DNI);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->Nombre = $row['Nombre'];
            $this->Apellidos = $row['Apellidos'];
            $this->Telefono = $row['Telefono'];
            $this->FechaNacimiento = $row['FechaNacimiento'];
            $this->Email = $row['Email'];
            $this->Contrasena = $row['Contrasena'];
            $this->CodigoPostal = $row['CodigoPostal'];
            $this->Genero = $row['Genero'];
            return true;
        }
        
        return false;
    }

    // Crear un nuevo cliente
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET DNI=:dni, Nombre=:nombre, Apellidos=:apellidos, 
                    Telefono=:telefono, FechaNacimiento=:fechaNacimiento,
                    Email=:email, Contrasena=:contrasena, 
                    CodigoPostal=:codigoPostal, Genero=:genero";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Apellidos = htmlspecialchars(strip_tags($this->Apellidos));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->FechaNacimiento = htmlspecialchars(strip_tags($this->FechaNacimiento));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        $this->Contrasena = htmlspecialchars(strip_tags($this->Contrasena));
        $this->CodigoPostal = htmlspecialchars(strip_tags($this->CodigoPostal));
        $this->Genero = htmlspecialchars(strip_tags($this->Genero));

        // Bind
        $stmt->bindParam(":dni", $this->DNI);
        $stmt->bindParam(":nombre", $this->Nombre);
        $stmt->bindParam(":apellidos", $this->Apellidos);
        $stmt->bindParam(":telefono", $this->Telefono);
        $stmt->bindParam(":fechaNacimiento", $this->FechaNacimiento);
        $stmt->bindParam(":email", $this->Email);
        $stmt->bindParam(":contrasena", $this->Contrasena);
        $stmt->bindParam(":codigoPostal", $this->CodigoPostal);
        $stmt->bindParam(":genero", $this->Genero);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Actualizar un cliente
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET Nombre=:nombre, Apellidos=:apellidos, 
                    Telefono=:telefono, FechaNacimiento=:fechaNacimiento,
                    Email=:email, Contrasena=:contrasena, 
                    CodigoPostal=:codigoPostal, Genero=:genero
                WHERE DNI=:dni";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Apellidos = htmlspecialchars(strip_tags($this->Apellidos));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->FechaNacimiento = htmlspecialchars(strip_tags($this->FechaNacimiento));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        $this->Contrasena = htmlspecialchars(strip_tags($this->Contrasena));
        $this->CodigoPostal = htmlspecialchars(strip_tags($this->CodigoPostal));
        $this->Genero = htmlspecialchars(strip_tags($this->Genero));

        // Bind
        $stmt->bindParam(":dni", $this->DNI);
        $stmt->bindParam(":nombre", $this->Nombre);
        $stmt->bindParam(":apellidos", $this->Apellidos);
        $stmt->bindParam(":telefono", $this->Telefono);
        $stmt->bindParam(":fechaNacimiento", $this->FechaNacimiento);
        $stmt->bindParam(":email", $this->Email);
        $stmt->bindParam(":contrasena", $this->Contrasena);
        $stmt->bindParam(":codigoPostal", $this->CodigoPostal);
        $stmt->bindParam(":genero", $this->Genero);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar un cliente
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE DNI = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $stmt->bindParam(1, $this->DNI);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Login - verificar credenciales
    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE Email = :email AND Contrasena = :contrasena 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        $this->Contrasena = htmlspecialchars(strip_tags($this->Contrasena));
        
        $stmt->bindParam(":email", $this->Email);
        $stmt->bindParam(":contrasena", $this->Contrasena);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->DNI = $row['DNI'];
            $this->Nombre = $row['Nombre'];
            $this->Apellidos = $row['Apellidos'];
            $this->Telefono = $row['Telefono'];
            $this->FechaNacimiento = $row['FechaNacimiento'];
            $this->CodigoPostal = $row['CodigoPostal'];
            $this->Genero = $row['Genero'];
            return true;
        }
        
        return false;
    }
}
?>