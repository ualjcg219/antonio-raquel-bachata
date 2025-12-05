<?php
// api/models/Bono.php

class Bono {
    private $conn;
    private $table_name = "Bono";

    public $tipo;
    public $numDias;
    public $descripcion;
    public $precio;
    public $foto;
    public $mes_NombreMes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los bonos
    public function read() {
        $query = "SELECT b.*, m.NombreMes 
                  FROM " . $this->table_name . " b
                  LEFT JOIN mes m ON b.mes_NombreMes = m.NombreMes";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un bono específico
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tipo = ? AND numDias = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tipo);
        $stmt->bindParam(2, $this->numDias);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->descripcion = $row['descripcion'];
            $this->precio = $row['precio'];
            $this->foto = $row['foto'];
            $this->mes_NombreMes = $row['mes_NombreMes'];
            return true;
        }
        
        return false;
    }

    // Crear un nuevo bono
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET tipo=:tipo, numDias=:numDias, descripcion=:descripcion,
                    precio=:precio, foto=:foto, mes_NombreMes=:mes";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->numDias = htmlspecialchars(strip_tags($this->numDias));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->foto = htmlspecialchars(strip_tags($this->foto));
        $this->mes_NombreMes = htmlspecialchars(strip_tags($this->mes_NombreMes));

        // Bind
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":numDias", $this->numDias);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":foto", $this->foto);
        $stmt->bindParam(":mes", $this->mes_NombreMes);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Actualizar un bono
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET descripcion=:descripcion, precio=:precio, 
                    foto=:foto, mes_NombreMes=:mes
                WHERE tipo=:tipo AND numDias=:numDias";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->numDias = htmlspecialchars(strip_tags($this->numDias));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->foto = htmlspecialchars(strip_tags($this->foto));
        $this->mes_NombreMes = htmlspecialchars(strip_tags($this->mes_NombreMes));

        // Bind
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":numDias", $this->numDias);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":foto", $this->foto);
        $stmt->bindParam(":mes", $this->mes_NombreMes);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar un bono
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE tipo = ? AND numDias = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->numDias = htmlspecialchars(strip_tags($this->numDias));
        
        $stmt->bindParam(1, $this->tipo);
        $stmt->bindParam(2, $this->numDias);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener bonos por mes
    public function readByMes() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE mes_NombreMes = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->mes_NombreMes);
        $stmt->execute();
        return $stmt;
    }
}
?>