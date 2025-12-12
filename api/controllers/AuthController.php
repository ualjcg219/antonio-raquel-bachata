<?php
// api/controllers/AuthController.php
class AuthController {
    private $conn;
    public function __construct($db) { $this->conn = $db; }
    public function login() { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
}
?>