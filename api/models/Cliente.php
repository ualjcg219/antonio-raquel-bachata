<?php
// api/models/Cliente.php

class Cliente {
    private $conn;
    private $table_name = "cliente"; // coincide con la tabla real en la BD

    public $DNI;
    public $Nombre;
    public $Apellidos;
    public $Telefono;
    public $FechaNacimiento;
    public $Email;
    public $Contrasena; // cuando se use en create, debe contener el hash
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
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $stmt->bindParam(1, $this->DNI);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
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

    // Devuelve un array asociativo con los datos del cliente por Email o false
    public function getByEmail(string $email) {
        $query = "SELECT DNI, Nombre, Apellidos, Telefono, FechaNacimiento, Email, Contrasena, CodigoPostal, Genero
                  FROM " . $this->table_name . " WHERE Email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt->bindParam(':email', $cleanEmail);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    // Crear un nuevo cliente
    // IMPORTANTE: $this->Contrasena debe contener el hash (password_hash) antes de llamar create()
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (DNI, Nombre, Apellidos, Telefono, FechaNacimiento, Email, Contrasena, CodigoPostal, Genero)
                VALUES (:dni, :nombre, :apellidos, :telefono, :fechaNacimiento, :email, :contrasena, :codigoPostal, :genero)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar entradas (pero NO modificar el hash de la contraseña)
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Apellidos = htmlspecialchars(strip_tags($this->Apellidos));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->FechaNacimiento = htmlspecialchars(strip_tags($this->FechaNacimiento));
        $this->Email = filter_var($this->Email, FILTER_SANITIZE_EMAIL);
        // NO aplicar htmlspecialchars a Contrasena (ya debe ser hash)
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

        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            // En producción registra el error en logs en lugar de silenciarlo
            return false;
        }
    }

    // Actualizar un cliente (si se actualiza la contraseña, enviar el hash)
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET Nombre=:nombre, Apellidos=:apellidos,
                    Telefono=:telefono, FechaNacimiento=:fechaNacimiento,
                    Email=:email, Contrasena=:contrasena,
                    CodigoPostal=:codigoPostal, Genero=:genero
                WHERE DNI=:dni";

        $stmt = $this->conn->prepare($query);

        // Sanitizar (no modificar Contrasena si ya es hash)
        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Apellidos = htmlspecialchars(strip_tags($this->Apellidos));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->FechaNacimiento = htmlspecialchars(strip_tags($this->FechaNacimiento));
        $this->Email = filter_var($this->Email, FILTER_SANITIZE_EMAIL);
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

        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Eliminar un cliente
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE DNI = ?";
        $stmt = $this->conn->prepare($query);

        $this->DNI = htmlspecialchars(strip_tags($this->DNI));
        $stmt->bindParam(1, $this->DNI);

        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Login - verificar credenciales usando password_verify contra el hash almacenado
    // Debe llamarse estableciendo $this->Email (email) y $this->Contrasena (texto plano de la contraseña)
    public function login() {
        // Obtener usuario por email
        $user = $this->getByEmail($this->Email ?? '');
        if (!$user) {
            return false;
        }

        $hash = $user['Contrasena'] ?? null;
        if (!$hash) {
            return false;
        }

        // Verificar contraseña
        if (!password_verify($this->Contrasena, $hash)) {
            return false;
        }

        // Autenticación correcta: poblar propiedades (no sobrescribimos Contrasena con el hash)
        $this->DNI = $user['DNI'] ?? null;
        $this->Nombre = $user['Nombre'] ?? null;
        $this->Apellidos = $user['Apellidos'] ?? null;
        $this->Telefono = $user['Telefono'] ?? null;
        $this->FechaNacimiento = $user['FechaNacimiento'] ?? null;
        $this->Email = $user['Email'] ?? null;
        $this->CodigoPostal = $user['CodigoPostal'] ?? null;
        $this->Genero = $user['Genero'] ?? null;

        return true;
    }
}
?>