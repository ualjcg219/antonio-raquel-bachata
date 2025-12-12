<?php
// api/controllers/BonoController.php
// Controller robusto para creación y lectura de bonos.

class BonoController {
    private $conn;
    private $table = 'bono';

    public function __construct($db) {
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : null;
        $numDias = isset($_POST['numDias']) ? intval($_POST['numDias']) : null;
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $precioRaw = isset($_POST['precio']) ? trim($_POST['precio']) : null;

        if (empty($tipo) || empty($numDias) || empty($descripcion) || $precioRaw === null || $precioRaw === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios: tipo, numDias, descripcion o precio.']);
            return;
        }

        // Normalizar precio
        $precioSan = preg_replace('/[^0-9\.,]/', '', $precioRaw);
        $precioSan = str_replace(',', '.', $precioSan);
        if (!is_numeric($precioSan)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Precio no válido.']);
            return;
        }
        $precio = number_format((float)$precioSan, 2, '.', '');

        // Manejo de imagen (opcional)
        $fotoPath = '';
        if (!empty($_FILES) && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $file = $_FILES['foto'];
            $tmpName = $file['tmp_name'];
            $origName = $file['name'];
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $uploadDir = __DIR__ . '/../../images/bonos';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '.jpg');
            $destination = $uploadDir . '/' . $filename;
            if (@move_uploaded_file($tmpName, $destination)) {
                $fotoPath = 'images/bonos/' . $filename;
            } else {
                $fotoPath = '';
            }
        }

        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO {$this->table} (tipo, numDias, descripcion, precio, foto)
                      VALUES (:tipo, :numDias, :descripcion, :precio, :foto)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->bindValue(':numDias', $numDias, PDO::PARAM_INT);
            $stmt->bindValue(':descripcion', $descripcion);
            $stmt->bindValue(':precio', $precio);
            $stmt->bindValue(':foto', $fotoPath);
            $stmt->execute();
            $affected = $stmt->rowCount();

            $this->conn->commit();

            $select = $this->conn->prepare("SELECT tipo, numDias, descripcion, precio, foto FROM {$this->table} WHERE tipo = :tipo AND numDias = :numDias LIMIT 1");
            $select->bindValue(':tipo', $tipo);
            $select->bindValue(':numDias', $numDias, PDO::PARAM_INT);
            $select->execute();
            $inserted = $select->fetch(PDO::FETCH_ASSOC);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Bono creado correctamente.',
                'debug' => [
                    'affected_rows' => $affected,
                    'inserted_row' => $inserted
                ]
            ]);
            return;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage(), 'sqlstate' => $e->getCode()]);
            return;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
            return;
        }
    }

    public function read() {
        try {
            $stmt = $this->conn->prepare("SELECT tipo, numDias, descripcion, precio, foto FROM {$this->table}");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
            return;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
            return;
        }
    }
}
?>