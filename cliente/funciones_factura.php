<?php
require 'vendor/autoload.php'; // Cargar librerías
require 'db.php';              // Cargar conexión

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Función principal para procesar la factura
function procesarFactura($idTransaccion) {
    
    // 1. OBTENER DATOS DE LA BASE DE DATOS
    $pdo = conectarDB();
    
    // SQL: Unimos 4 tablas para sacar toda la info de una sola vez.
    // Transaccion (fecha, total) -> Cliente (datos personales) -> BonoComprado (qué compró) -> Bono (descripción y precio original)
    $sql = "
        SELECT 
            t.idTransaccion, t.FechaCompra, t.costo AS TotalPagar,
            c.Nombre, c.Apellidos, c.DNI, c.Email, c.CodigoPostal,
            bc.bono_tipo, bc.bono_numDias,
            b.descripcion, b.precio AS PrecioUnitario
        FROM transaccion t
        JOIN cliente c ON t.cliente_DNI = c.DNI
        JOIN bonocomprado bc ON t.idTransaccion = bc.transaccion_idTransaccion
        JOIN bono b ON (bc.bono_tipo = b.tipo AND bc.bono_numDias = b.numDias)
        WHERE t.idTransaccion = :id
    ";

    // Ejecutamos la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idTransaccion]);
    $resultados = $stmt->fetchAll();

    if (!$resultados) {
        return "Error: Transacción no encontrada.";
    }

    // Separamos los datos para pasarlos limpios a la plantilla
    // $datosCliente: Tomamos la primera fila (los datos del cliente son iguales en todas las filas)
    $datosCliente = $resultados[0]; 
    // $itemsCompra: Son todas las filas (si compró 2 bonos, habrá 2 filas)
    $itemsCompra = $resultados;


    // 2. GENERAR HTML (USANDO LA PLANTILLA)
    // -----------------------------------------------------
    // 'ob_start' inicia un búfer. Todo lo que se haga 'echo' o HTML plano NO se muestra en pantalla, se guarda en memoria.
    ob_start();
    
    // Incluimos el archivo visual. Como $datosCliente e $itemsCompra ya existen aquí, la plantilla los puede usar.
    include 'plantilla_factura.php'; 
    
    // 'ob_get_clean' guarda todo ese HTML en la variable $html y limpia la memoria.
    $html = ob_get_clean();


    // 3. CONVERTIR A PDF (DOMPDF)
    // -----------------------------------------------------
    $options = new Options();
    $options->set('isRemoteEnabled', true); // Permitir imágenes externas si las hubiera
    $dompdf = new Dompdf($options);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // Formato folio vertical
    $dompdf->render();
    
    $pdfString = $dompdf->output(); // Obtenemos el fichero PDF "crudo" (en binario)


    // 4. ENVIAR EMAIL (PHPMAILER)
    // -----------------------------------------------------
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST']; // Leemos del .env
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; // Leemos del .env
        $mail->Password   = $_ENV['SMTP_PASS']; // Leemos del .env (¡SEGURO!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'];

        // Configuración del correo
        $mail->setFrom($_ENV['SMTP_USER'], 'Escuela de Baile'); // Usamos el mismo mail de envío
        $mail->addAddress($datosCliente['Email'], $datosCliente['Nombre']);

        $mail->isHTML(true);
        $mail->Subject = 'Factura de tu compra #' . $idTransaccion;
        $mail->Body    = 'Hola, adjuntamos tu factura en PDF. ¡Gracias!';
        
        // Adjuntamos el string binario del PDF sin guardarlo en disco
        $mail->addStringAttachment($pdfString, 'Factura_' . $idTransaccion . '.pdf');

        $mail->send();
        return "Factura enviada correctamente.";

    } catch (Exception $e) {
        return "Error al enviar correo: {$mail->ErrorInfo}";
    }
}
?>