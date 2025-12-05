<!-- plantilla_factura.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    
    <!-- 1. IMPORTANTE: Cargar la fuente Montserrat de Google para que el PDF la reconozca -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* Definimos los márgenes de la página (opcional, ayuda a que no se corte) */
        @page { margin: 0px; }
        
        body { 
            font-family: 'Montserrat', sans-serif; 
            color: #333; 
            font-size: 14px; 
            margin: 30px; /* Margen interior del cuerpo */
        }

        .header { 
            width: 100%; 
            border-bottom: 2px solid #E72059; 
            padding-bottom: 20px; 
            margin-bottom: 20px; 
        }

        /* 2. Estilo específico para la imagen (quitamos las clases raras) */
        .logo-img {
            width: 80px; /* Ajusta este tamaño a tu gusto */
            height: auto;
            display: block;
            margin-bottom: 10px;
        }

        .invoice-details { float: left; width: 50%; }
        .company-details { float: right; width: 40%; text-align: right; }
        
        /* El "clear" es vital en Dompdf después de usar floats */
        .clear { clear: both; } 
        
        .client-section { 
            margin-top: 20px; 
            background: #f9f9f9; 
            padding: 15px; 
            border-radius: 5px; 
            border-left: 4px solid #E72059; /* Un toque estético extra */
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background-color: #E72059; color: white; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 10px; }
        
        .total-box { 
            text-align: right; 
            margin-top: 20px; 
            font-size: 20px; 
            font-weight: bold; 
            color: #E72059; 
        }
        
        .footer { 
            position: fixed; 
            bottom: -20px; /* Ajuste para que quede al pie */
            left: 0; 
            right: 0; 
            height: 50px;
            text-align: center; 
            font-size: 10px; 
            color: #aaa; 
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="invoice-details">
            <!-- Imagen limpia sin clases de animación que rompen el PDF -->
            <img src="https://raw.githubusercontent.com/ualjcg219/antonio-raquel-bachata/main/images/logo1.png" class="logo-img" alt="Logo Antonio y Raquel">
            
            <h1 style="margin: 0; color: #E72059; font-size: 24px;">FACTURA</h1>
            <strong>Nº Transacción:</strong> #<?php echo str_pad($datosCliente['idTransaccion'], 6, "0", STR_PAD_LEFT); ?><br>
            <strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($datosCliente['FechaCompra'])); ?>
        </div>
        
        <div class="company-details">
            <strong style="font-size: 16px; color: #E72059;">A&R Bachata S.L.</strong><br>
            Ctra. Alhadra, 205, 04009<br>
            Almería, España<br>
            CIF: B-12345678<br>
        </div>
        <div class="clear"></div>
    </div>

    <div class="client-section">
        <strong style="color: #E72059;">Datos del Cliente:</strong><br>
        <span style="font-size: 16px;"><b><?php echo $datosCliente['Nombre'] . ' ' . $datosCliente['Apellidos']; ?></b></span><br>
        DNI: <?php echo $datosCliente['DNI']; ?><br>
        Email: <?php echo $datosCliente['Email']; ?><br>
        CP: <?php echo $datosCliente['CodigoPostal']; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto / Bono</th>
                <th>Días</th>
                <th style="text-align: right;">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itemsCompra as $item): ?>
            <tr>
                <td>
                    <strong style="color: #333;"><?php echo $item['bono_tipo']; ?></strong><br>
                    <small style="color: #777;"><?php echo $item['descripcion']; ?></small>
                </td>
                <td style="vertical-align: top;"><?php echo $item['bono_numDias']; ?></td>
                <td style="text-align: right; vertical-align: top;"><?php echo number_format($item['PrecioUnitario'], 2); ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-box">
        TOTAL PAGADO: <?php echo number_format($datosCliente['TotalPagar'], 2); ?> €
    </div>

    <div class="footer">
        Gracias por bailar con nosotros. Esta factura se ha generado automáticamente.<br>
        A&R Bachata S.L. - Almería
    </div>

</body>
</html>