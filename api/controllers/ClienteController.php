<?php
// api/controllers/ClienteController.php
// Stub mínimo para evitar errores de inclusión. Implementa métodos básicos como no-op.

class ClienteController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }
    public function read() { echo json_encode(['success' => true, 'data' => []]); }
    public function create() { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function readOne($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function update($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function delete($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
}
?>