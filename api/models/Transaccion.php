<?php
// api/models/Transaccion.php

class Transaccion {
    private $conn;
    private $table = 'transaccion';

    public $idTransaccion;
    public $FechaCompra;
    public $costo;
    public $cliente_DNI;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las transacciones
    public function getAll() {
        $query = "SELECT t.*, cl.Nombre, cl.Apellidos, cl.Email
                  FROM " . $this->table . " t
                  INNER JOIN cliente cl ON t.cliente_DNI = cl.DNI
                  ORDER BY t.FechaCompra DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener transacciones por cliente
    public function getByCliente($dni) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE cliente_DNI = ? 
                  ORDER BY FechaCompra DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $dni);
        $stmt->execute();
        return $stmt;
    }

    // Crear transacción y bono comprado
    public function createWithBono($bono_tipo, $bono_numDias) {
        try {
            $this->conn->beginTransaction();

            // Obtener información del bono
            $queryBono = "SELECT precio, numDias FROM bono WHERE tipo = ? AND numDias = ?";
            $stmtBono = $this->conn->prepare($queryBono);
            $stmtBono->bindParam(1, $bono_tipo);
            $stmtBono->bindParam(2, $bono_numDias);
            $stmtBono->execute();
            $bono = $stmtBono->fetch(PDO::FETCH_ASSOC);

            if(!$bono) {
                throw new Exception("Bono no encontrado");
            }

            // Crear transacción
            $query = "INSERT INTO " . $this->table . " (costo, cliente_DNI) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $bono['precio']);
            $stmt->bindParam(2, $this->cliente_DNI);
            $stmt->execute();
            
            $this->idTransaccion = $this->conn->lastInsertId();

            // Calcular fecha de caducidad
            $fechaCaducidad = date('Y-m-d', strtotime('+' . $bono['numDias'] . ' days'));

            // Determinar saldo inicial según tipo de bono
            $saldoInicial = 0;
            if(stripos($bono_tipo, 'individual') !== false) {
                $saldoInicial = 1;
            } elseif(stripos($bono_tipo, 'trimestral') !== false) {
                $saldoInicial = 12;
            } elseif(stripos($bono_tipo, 'mensual') !== false) {
                $saldoInicial = 4;
            }

            // Crear bono comprado
            $queryBono = "INSERT INTO bonocomprado 
                         (SaldoClases, FechaCaducidad, bono_tipo, bono_numDias, transaccion_idTransaccion, cliente_DNI)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmtBonoC = $this->conn->prepare($queryBono);
            $stmtBonoC->bindParam(1, $saldoInicial);
            $stmtBonoC->bindParam(2, $fechaCaducidad);
            $stmtBonoC->bindParam(3, $bono_tipo);
            $stmtBonoC->bindParam(4, $bono['numDias']);
            $stmtBonoC->bindParam(5, $this->idTransaccion);
            $stmtBonoC->bindParam(6, $this->cliente_DNI);
            $stmtBonoC->execute();

            $this->conn->commit();
            return [
                'idTransaccion' => $this->idTransaccion,
                'idBonoComprado' => $this->conn->lastInsertId()
            ];

        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>