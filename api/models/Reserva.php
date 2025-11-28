<?php
// api/models/Reserva.php

class Reserva {
    private $conn;
    private $table_name = "Reserva";

    public $idReserva;
    public $Cliente_DNI;
    public $Clase_del_Dia_horaInicio;
    public $Clase_del_Dia_dia_NumeroDia;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las reservas con detalles de clase y cliente
    public function read() {
        $query = "SELECT r.*, 
                         c.Nombre, c.Apellidos, c.Email,
                         cd.horaInicio, cd.horaFinal,
                         cu.TipoBaile, cu.Nivel,
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " r
                  LEFT JOIN Cliente c ON r.Cliente_DNI = c.DNI
                  LEFT JOIN Clase_del_Dia cd ON r.Clase_del_Dia_horaInicio = cd.horaInicio 
                       AND r.Clase_del_Dia_dia_NumeroDia = cd.dia_NumeroDia
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener una reserva específica
    public function readOne() {
        $query = "SELECT r.*, 
                         c.Nombre, c.Apellidos, c.Email,
                         cd.horaInicio, cd.horaFinal,
                         cu.TipoBaile, cu.Nivel,
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " r
                  LEFT JOIN Cliente c ON r.Cliente_DNI = c.DNI
                  LEFT JOIN Clase_del_Dia cd ON r.Clase_del_Dia_horaInicio = cd.horaInicio 
                       AND r.Clase_del_Dia_dia_NumeroDia = cd.dia_NumeroDia
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes
                  WHERE r.idReserva = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idReserva);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->Cliente_DNI = $row['Cliente_DNI'];
            $this->Clase_del_Dia_horaInicio = $row['horaInicio'];
            $this->Clase_del_Dia_dia_NumeroDia = $row['NumeroDia'];
            return true;
        }
        
        return false;
    }

    // Crear una nueva reserva
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET idReserva=:idReserva, Cliente_DNI=:clienteDNI,
                    Clase_del_Dia_horaInicio=:horaInicio,
                    Clase_del_Dia_dia_NumeroDia=:numeroDia";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->idReserva = htmlspecialchars(strip_tags($this->idReserva));
        $this->Cliente_DNI = htmlspecialchars(strip_tags($this->Cliente_DNI));
        $this->Clase_del_Dia_horaInicio = htmlspecialchars(strip_tags($this->Clase_del_Dia_horaInicio));
        $this->Clase_del_Dia_dia_NumeroDia = htmlspecialchars(strip_tags($this->Clase_del_Dia_dia_NumeroDia));

        // Bind
        $stmt->bindParam(":idReserva", $this->idReserva);
        $stmt->bindParam(":clienteDNI", $this->Cliente_DNI);
        $stmt->bindParam(":horaInicio", $this->Clase_del_Dia_horaInicio);
        $stmt->bindParam(":numeroDia", $this->Clase_del_Dia_dia_NumeroDia);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar una reserva
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE idReserva = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->idReserva = htmlspecialchars(strip_tags($this->idReserva));
        $stmt->bindParam(1, $this->idReserva);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener reservas de un cliente
    public function readByCliente() {
        $query = "SELECT r.*, 
                         cd.horaInicio, cd.horaFinal,
                         cu.TipoBaile, cu.Nivel,
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " r
                  LEFT JOIN Clase_del_Dia cd ON r.Clase_del_Dia_horaInicio = cd.horaInicio 
                       AND r.Clase_del_Dia_dia_NumeroDia = cd.dia_NumeroDia
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes
                  WHERE r.Cliente_DNI = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Cliente_DNI);
        $stmt->execute();
        return $stmt;
    }

    // Verificar disponibilidad de una clase
    public function checkDisponibilidad() {
        $query = "SELECT COUNT(*) as total, cu.Aforo
                  FROM " . $this->table_name . " r
                  LEFT JOIN Clase_del_Dia cd ON r.Clase_del_Dia_horaInicio = cd.horaInicio 
                       AND r.Clase_del_Dia_dia_NumeroDia = cd.dia_NumeroDia
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  WHERE r.Clase_del_Dia_horaInicio = ? 
                       AND r.Clase_del_Dia_dia_NumeroDia = ?
                  GROUP BY cu.Aforo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Clase_del_Dia_horaInicio);
        $stmt->bindParam(2, $this->Clase_del_Dia_dia_NumeroDia);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && $row['total'] < $row['Aforo']) {
            return true; // Hay disponibilidad
        }
        
        return false; // No hay disponibilidad
    }

    // Generar ID automático
    public function generateId() {
        $query = "SELECT MAX(idReserva) as maxId FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['maxId'] ?? 0) + 1;
    }
}
?>