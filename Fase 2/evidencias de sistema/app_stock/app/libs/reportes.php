<?php

require_once('tcpdf/tcpdf.php');

// Obtener los parámetros del formulario
$reporteTipo = $_POST['reporteTipo'];
$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];

// Crear una nueva instancia de TCPDF
$pdf = new TCPDF();
$pdf->AddPage();

// Establecer título y formato
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de ' . ucfirst($reporteTipo), 0, 1, 'C');
$pdf->Ln(5);

// Conectar a la base de datos y consultar los datos según el reporte
try {
    $conexion = new PDO('mysql:host=localhost;dbname=stock_control', 'root', '');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($reporteTipo == 'inventario') {
        // Consulta para el reporte de inventario
        $query = "SELECT producto.cod_producto, producto.nombre_producto, proveedor.nombre_prove, 
                          producto.valor_unitario, inventario.existencia_inicial, inventario.existencia_actual,
                          (producto.valor_unitario * inventario.existencia_actual) AS valorTotal
                    
                  FROM inventario
                  JOIN producto ON inventario.producto_id_producto = producto.id_producto
                  JOIN proveedor ON producto.proveedor_id_proveedor = proveedor.id_proveedor
                  WHERE inventario.fecha BETWEEN :fechaInicio AND :fechaFin";

        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(25, 10, 'Código', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Producto', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Proveedor', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Ex. Inicial', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Ex. Actual', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Valor Unitario', 1, 0, 'C');
            $pdf->Cell(32, 10, 'Total', 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 11);

            $subtotal = 0;
            $iva = 0;
            $iva_porcentaje = 0.19;

            foreach ($resultados as $row) {
                $total_producto = $row['existencia_actual'] * $row['valor_unitario'];
                $subtotal += $total_producto;

                $pdf->Cell(25, 10, $row['cod_producto'], 1);
                $pdf->Cell(30, 10, $row['nombre_producto'], 1);
                $pdf->Cell(30, 10, $row['nombre_prove'], 1);
                $pdf->Cell(25, 10, $row['existencia_inicial'], 1);
                $pdf->Cell(25, 10, $row['existencia_actual'], 1);
                $pdf->Cell(30, 10, '$' . number_format($row['valor_unitario'], 2), 1);
                $pdf->Cell(32, 10, '$' . number_format($total_producto, 2), 1);
                $pdf->Ln();
            }

             // Calcular el IVA y el total
             $iva = $subtotal * $iva_porcentaje;
             $total_con_iva = $subtotal + $iva;
 
             // Mostrar el subtotal, el IVA y el total con IVA
             $pdf->Ln(10);
             $pdf->SetFont('helvetica', 'B', 10);
             $pdf->Cell(155, 10, 'Subtotal:', 0, 0, 'R');
             $pdf->Cell(30, 10, number_format($subtotal, 0, '', '.'), 1, 1, 'R');
             $pdf->Cell(155, 10, 'IVA (19%):', 0, 0, 'R');
             $pdf->Cell(30, 10, number_format($iva, 0, '', '.'), 1, 1, 'R');
             $pdf->Cell(155, 10, 'Total con IVA:', 0, 0, 'R');
             $pdf->Cell(30, 10, number_format($total_con_iva, 0, '', '.'), 1, 1, 'R');
        } else {
            $pdf->Cell(0, 10, 'No se encontraron resultados para el reporte de inventario', 0, 1, 'C');
        }
    } elseif ($reporteTipo == 'movimientos') {
        // Consulta para el reporte de movimientos
        $query = "SELECT movimiento.movimiento, producto.cod_producto, producto.nombre_producto, 
                         movimiento.fecha_movimiento,
                         usuario.nombre
                  FROM movimiento
                  LEFT JOIN inventario ON movimiento.inventario_id_inventario = inventario.id_inventario
                  LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
                  LEFT JOIN usuario ON movimiento.usuario_id_usuario = usuario.id_usuario
                  WHERE movimiento.fecha_movimiento BETWEEN :fechaInicio AND :fechaFin";

        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(35, 10, 'Tipo Mov.', 1, 0, 'C');
            $pdf->Cell(35, 10, 'Cod. Producto', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Producto', 1, 0, 'C');
            // $pdf->Cell(25, 10, 'Cantidad', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Responsable', 1, 0, 'C');
            $pdf->Cell(35, 10, 'Fecha Mov.', 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 11);
            foreach ($resultados as $row) {
                $pdf->Cell(35, 10, $row['movimiento'], 1);
                $pdf->Cell(35, 10, $row['cod_producto'], 1);
                $pdf->Cell(40, 10, $row['nombre_producto'], 1);
                // $pdf->Cell(25, 10, $row['existencia_actual'], 1);
                $pdf->Cell(30, 10, $row['nombre'], 1);
                $pdf->Cell(35, 10, $row['fecha_movimiento'], 1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell(0, 10, 'No se encontraron resultados para el reporte de movimientos', 0, 1, 'C');
        }
    } elseif ($reporteTipo == 'existencia baja') {
        // Consulta para el reporte de productos con existencia baja
        $query = "SELECT producto.nombre_producto, producto.cod_producto, proveedor.nombre_prove, 
                         inventario.existencia_actual 
                  FROM inventario 
                  JOIN producto ON inventario.producto_id_producto = producto.id_producto 
                  JOIN proveedor ON producto.proveedor_id_proveedor = proveedor.id_proveedor 
                  WHERE inventario.existencia_actual < :limiteBajo";

        $limiteBajo = 10;
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':limiteBajo', $limiteBajo);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(30, 10, 'Código', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Proveedor', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Producto', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Existencia Actual', 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 11);
            foreach ($resultados as $row) {
                $pdf->Cell(30, 10, $row['cod_producto'], 1);
                $pdf->Cell(40, 10, $row['nombre_prove'], 1);
                $pdf->Cell(60, 10, $row['nombre_producto'], 1);
                $pdf->Cell(40, 10, $row['existencia_actual'], 1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell(0, 10, 'No existen productos con existencia crítica en el inventario.', 0, 1, 'C');
        }
    } else {
        $pdf->Cell(0, 10, 'Tipo de reporte no válido', 0, 1, 'C');
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Salida del PDF
$pdf->Output('reporte_' . $reporteTipo . '.pdf', 'I');
