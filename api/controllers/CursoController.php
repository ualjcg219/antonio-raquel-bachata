<?php
// api/controllers/CursoController.php
class CursoController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }
    public function read() { echo json_encode(['success' => true, 'data' => []]); }
    public function create() { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function readOne($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function update($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
    public function delete($id) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
}
?>