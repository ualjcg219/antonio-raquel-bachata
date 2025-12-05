<?php
// Para poder probar que todo funciona correctamente

// Activamos reporte de errores para ver si algo falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Iniciando prueba de facturación...</h1>";

// Incluimos la lógica
require_once 'funciones_factura.php';

// Definimos el ID de la transacción que acabamos de crear en el paso 1
$idTransaccionPrueba = 1;

try {
    echo "Intentando generar factura para la transacción ID: $idTransaccionPrueba ...<br>";
    
    // Llamamos a la función
    $resultado = procesarFactura($idTransaccionPrueba);
    
    // Mostramos el resultado
    if (strpos($resultado, 'Error') !== false) {
        echo "<h2 style='color:red'>❌ FAILED: $resultado</h2>";
    } else {
        echo "<h2 style='color:green'>✅ ÉXITO: $resultado</h2>";
        echo "<p>Revisa tu bandeja de entrada (y la carpeta de SPAM).</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Error Fatal: " . $e->getMessage() . "</h2>";
}
?>