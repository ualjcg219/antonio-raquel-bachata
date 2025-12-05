<?php
// api/models/Reserva.php

class Reserva {
    private $conn;
    private $table = 'reserva';

    public $idReserva;
    public $idClase;
    public $idBonoComprado;
    public $FechaReserva;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las reservas
    public function getAll() {
        $query = "SELECT r.idReserva, r.idClase, r.idBonoComprado, r.FechaReserva,
                  c.fechaInicio, c.fechaFin, c.baile, c.nivel,
                  bc.cliente_DNI, cl.Nombre, cl.Apellidos
                  FROM " . $this->table . " r
                  INNER JOIN clase c ON r.idClase = c.idClase
                  INNER JOIN bonocomprado bc ON r.idBonoComprado = bc.idBonoComprado
                  INNER JOIN cliente cl ON bc.cliente_DNI = cl.DNI
                  ORDER BY c.fechaInicio DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener reservas por cliente
    public function getByCliente($dni) {
        $query = "SELECT r.idReserva, r.idClase, r.idBonoComprado, r.FechaReserva,
                  c.fechaInicio, c.fechaFin, c.baile, c.nivel,
                  cu.Descripcion, cu.Foto
                  FROM " . $this->table . " r
                  INNER JOIN clase c ON r.idClase = c.idClase
                  INNER JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                  INNER JOIN bonocomprado bc ON r.idBonoComprado = bc.idBonoComprado
                  WHERE bc.cliente_DNI = ?
                  ORDER BY c.fechaInicio DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $dni);
        $stmt->execute();
        return $stmt;
    }

    // Obtener reservas por clase
    public function getByClase() {
        $query = "SELECT r.idReserva, r.idBonoComprado, r.FechaReserva,
                  bc.cliente_DNI, cl.Nombre, cl.Apellidos, cl.Email
                  FROM " . $this->table . " r
                  INNER JOIN bonocomprado bc ON r.idBonoComprado = bc.idBonoComprado
                  INNER JOIN cliente cl ON bc.cliente_DNI = cl.DNI
                  WHERE r.idClase = ?
                  ORDER BY r.FechaReserva";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idClase);
        $stmt->execute();
        return $stmt;
    }

    // Crear reserva
    public function create() {
        try {
            $this->conn->beginTransaction();

            // Verificar que el bono tiene saldo
            $queryBono = "SELECT SaldoClases, FechaCaducidad FROM bonocomprado WHERE idBonoComprado = ?";
            $stmtBono = $this->conn->prepare($queryBono);
            $stmtBono->bindParam(1, $this->idBonoComprado);
            $stmtBono->execute();
            $bono = $stmtBono->fetch(PDO::FETCH_ASSOC);

            if(!$bono || $bono['SaldoClases'] <= 0) {
                throw new Exception("El bono no tiene saldo disponible");
            }

            if(strtotime($bono['FechaCaducidad']) < time()) {
                throw new Exception("El bono ha caducado");
            }

            // Verificar disponibilidad de la clase
            $queryAforo = "SELECT cu.Aforo, COUNT(r.idReserva) as reservasActuales
                          FROM clase c
                          LEFT JOIN curso cu ON c.baile = cu.TipoBaile AND c.nivel = cu.Nivel
                          LEFT JOIN reserva r ON c.idClase = r.idClase
                          WHERE c.idClase = ?
                          GROUP BY c.idClase";
            $stmtAforo = $this->conn->prepare($queryAforo);
            $stmtAforo->bindParam(1, $this->idClase);
            $stmtAforo->execute();
            $clase = $stmtAforo->fetch(PDO::FETCH_ASSOC);

            if($clase && $clase['reservasActuales'] >= $clase['Aforo']) {
                throw new Exception("La clase está completa");
            }

            // Crear la reserva
            $query = "INSERT INTO " . $this->table . " (idClase, idBonoComprado) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->idClase);
            $stmt->bindParam(2, $this->idBonoComprado);
            $stmt->execute();

            // Descontar del saldo del bono
            $queryUpdate = "UPDATE bonocomprado SET SaldoClases = SaldoClases - 1 WHERE idBonoComprado = ?";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(1, $this->idBonoComprado);
            $stmtUpdate->execute();

            $this->idReserva = $this->conn->lastInsertId();
            $this->conn->commit();
            return true;

        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Cancelar reserva
    public function delete() {
        try {
            $this->conn->beginTransaction();

            // Obtener el idBonoComprado antes de eliminar
            $queryGet = "SELECT idBonoComprado FROM " . $this->table . " WHERE idReserva = ?";
            $stmtGet = $this->conn->prepare($queryGet);
            $stmtGet->bindParam(1, $this->idReserva);
            $stmtGet->execute();
            $reserva = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if(!$reserva) {
                throw new Exception("Reserva no encontrada");
            }

            // Eliminar la reserva
            $query = "DELETE FROM " . $this->table . " WHERE idReserva = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->idReserva);
            $stmt->execute();

            // Devolver el saldo al bono
            $queryUpdate = "UPDATE bonocomprado SET SaldoClases = SaldoClases + 1 
                           WHERE idBonoComprado = ?";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(1, $reserva['idBonoComprado']);
            $stmtUpdate->execute();

            $this->conn->commit();
            return true;

        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>