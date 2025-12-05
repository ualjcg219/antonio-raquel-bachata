<?php
// api/models/BonoComprado.php

class BonoComprado {
    private $conn;
    private $table = 'bonocomprado';

    public $idBonoComprado;
    public $SaldoClases;
    public $FechaCaducidad;
    public $bono_tipo;
    public $bono_numDias;
    public $transaccion_idTransaccion;
    public $cliente_DNI;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los bonos comprados
    public function getAll() {
        $query = "SELECT bc.*, b.descripcion, b.precio, cl.Nombre, cl.Apellidos
                  FROM " . $this->table . " bc
                  INNER JOIN bono b ON bc.bono_tipo = b.tipo AND bc.bono_numDias = b.numDias
                  INNER JOIN cliente cl ON bc.cliente_DNI = cl.DNI
                  ORDER BY bc.FechaCaducidad DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener bonos por cliente
    public function getByCliente($dni) {
        $query = "SELECT bc.*, b.descripcion, b.precio, b.foto
                  FROM " . $this->table . " bc
                  INNER JOIN bono b ON bc.bono_tipo = b.tipo AND bc.bono_numDias = b.numDias
                  WHERE bc.cliente_DNI = ?
                  ORDER BY bc.FechaCaducidad DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $dni);
        $stmt->execute();
        return $stmt;
    }

    // Obtener bonos activos por cliente
    public function getActiveByCliente($dni) {
        $query = "SELECT bc.*, b.descripcion, b.precio, b.foto
                  FROM " . $this->table . " bc
                  INNER JOIN bono b ON bc.bono_tipo = b.tipo AND bc.bono_numDias = b.numDias
                  WHERE bc.cliente_DNI = ? 
                  AND bc.SaldoClases > 0 
                  AND bc.FechaCaducidad >= CURDATE()
                  ORDER BY bc.FechaCaducidad ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $dni);
        $stmt->execute();
        return $stmt;
    }

    // Obtener bono por ID
    public function getById() {
        $query = "SELECT bc.*, b.descripcion, b.precio, b.foto
                  FROM " . $this->table . " bc
                  INNER JOIN bono b ON bc.bono_tipo = b.tipo AND bc.bono_numDias = b.numDias
                  WHERE bc.idBonoComprado = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idBonoComprado);
        $stmt->execute();
        return $stmt;
    }

    // Crear bono comprado (parte de una transacción)
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (SaldoClases, FechaCaducidad, bono_tipo, bono_numDias, transaccion_idTransaccion, cliente_DNI)
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->SaldoClases);
        $stmt->bindParam(2, $this->FechaCaducidad);
        $stmt->bindParam(3, $this->bono_tipo);
        $stmt->bindParam(4, $this->bono_numDias);
        $stmt->bindParam(5, $this->transaccion_idTransaccion);
        $stmt->bindParam(6, $this->cliente_DNI);
        
        if($stmt->execute()) {
            $this->idBonoComprado = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>