<?php
// api/models/ClaseDelDia.php

class ClaseDelDia {
    private $conn;
    private $table_name = "Clase_del_Dia";

    public $horaInicio;
    public $horaFinal;
    public $dia_NumeroDia;
    public $Curso_TipoBaile;
    public $Curso_Nivel;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las clases con información del curso y día
    public function read() {
        $query = "SELECT cd.*, 
                         cu.Descripcion, cu.Aforo, cu.Foto,
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " cd
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes
                  ORDER BY d.NumeroDia, cd.horaInicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener una clase específica
    public function readOne() {
        $query = "SELECT cd.*, 
                         cu.Descripcion, cu.Aforo, cu.Foto,
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " cd
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes
                  WHERE cd.horaInicio = ? AND cd.dia_NumeroDia = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->horaInicio);
        $stmt->bindParam(2, $this->dia_NumeroDia);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->horaFinal = $row['horaFinal'];
            $this->Curso_TipoBaile = $row['Curso_TipoBaile'];
            $this->Curso_Nivel = $row['Curso_Nivel'];
            return true;
        }
        
        return false;
    }

    // Crear una nueva clase
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET horaInicio=:horaInicio, horaFinal=:horaFinal,
                    dia_NumeroDia=:numeroDia, Curso_TipoBaile=:tipoBaile,
                    Curso_Nivel=:nivel";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->horaInicio = htmlspecialchars(strip_tags($this->horaInicio));
        $this->horaFinal = htmlspecialchars(strip_tags($this->horaFinal));
        $this->dia_NumeroDia = htmlspecialchars(strip_tags($this->dia_NumeroDia));
        $this->Curso_TipoBaile = htmlspecialchars(strip_tags($this->Curso_TipoBaile));
        $this->Curso_Nivel = htmlspecialchars(strip_tags($this->Curso_Nivel));

        // Bind
        $stmt->bindParam(":horaInicio", $this->horaInicio);
        $stmt->bindParam(":horaFinal", $this->horaFinal);
        $stmt->bindParam(":numeroDia", $this->dia_NumeroDia);
        $stmt->bindParam(":tipoBaile", $this->Curso_TipoBaile);
        $stmt->bindParam(":nivel", $this->Curso_Nivel);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Actualizar una clase
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET horaFinal=:horaFinal, Curso_TipoBaile=:tipoBaile,
                    Curso_Nivel=:nivel
                WHERE horaInicio=:horaInicio AND dia_NumeroDia=:numeroDia";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->horaInicio = htmlspecialchars(strip_tags($this->horaInicio));
        $this->horaFinal = htmlspecialchars(strip_tags($this->horaFinal));
        $this->dia_NumeroDia = htmlspecialchars(strip_tags($this->dia_NumeroDia));
        $this->Curso_TipoBaile = htmlspecialchars(strip_tags($this->Curso_TipoBaile));
        $this->Curso_Nivel = htmlspecialchars(strip_tags($this->Curso_Nivel));

        // Bind
        $stmt->bindParam(":horaInicio", $this->horaInicio);
        $stmt->bindParam(":horaFinal", $this->horaFinal);
        $stmt->bindParam(":numeroDia", $this->dia_NumeroDia);
        $stmt->bindParam(":tipoBaile", $this->Curso_TipoBaile);
        $stmt->bindParam(":nivel", $this->Curso_Nivel);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar una clase
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE horaInicio = ? AND dia_NumeroDia = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->horaInicio = htmlspecialchars(strip_tags($this->horaInicio));
        $this->dia_NumeroDia = htmlspecialchars(strip_tags($this->dia_NumeroDia));
        
        $stmt->bindParam(1, $this->horaInicio);
        $stmt->bindParam(2, $this->dia_NumeroDia);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener clases por día
    public function readByDia() {
        $query = "SELECT cd.*, 
                         cu.Descripcion, cu.Aforo, cu.Foto
                  FROM " . $this->table_name . " cd
                  LEFT JOIN Curso cu ON cd.Curso_TipoBaile = cu.TipoBaile 
                       AND cd.Curso_Nivel = cu.Nivel
                  WHERE cd.dia_NumeroDia = ?
                  ORDER BY cd.horaInicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->dia_NumeroDia);
        $stmt->execute();
        return $stmt;
    }

    // Obtener clases por curso
    public function readByCurso() {
        $query = "SELECT cd.*, 
                         d.NumeroDia, m.NombreMes
                  FROM " . $this->table_name . " cd
                  LEFT JOIN dia d ON cd.dia_NumeroDia = d.NumeroDia
                  LEFT JOIN mes m ON d.mes_NombreMes = m.NombreMes
                  WHERE cd.Curso_TipoBaile = ? AND cd.Curso_Nivel = ?
                  ORDER BY d.NumeroDia, cd.horaInicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Curso_TipoBaile);
        $stmt->bindParam(2, $this->Curso_Nivel);
        $stmt->execute();
        return $stmt;
    }
}
?>