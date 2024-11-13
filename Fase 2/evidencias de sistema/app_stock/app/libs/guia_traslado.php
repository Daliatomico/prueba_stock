<?php

require_once('tcpdf/tcpdf.php');

// Crear clase para el PDF
class GuiaTrasladoPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Guía de Traslado', 0, 1, 'C');
        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Obtener el número de guía seleccionado del formulario
$nro_guia_seleccionado = $_GET['nro_guia'] ?? null;

if ($nro_guia_seleccionado) {
    // Crear instancia del PDF
    $pdf = new GuiaTrasladoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Guía de Traslado');
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();

    // Conexión y consulta
    try {
        $conexionBD = new PDO('mysql:host=localhost;dbname=stock_control', 'root', '');
        $sql = "SELECT 
                    nro_guia, 
                    producto.cod_producto, 
                    producto.nombre_producto, 
                    detalle_guia.cantidad, 
                    detalle_guia.cantidad * producto.valor_unitario as Total,
                    proveedor.nombre_prove, 
                    cliente.nombre AS Cliente, 
                    guia_traslado.destino AS Direccion, 
                    guia_traslado.fecha_emision AS Fecha
                FROM guia_traslado
                LEFT JOIN cliente ON guia_traslado.cliente_id_cliente = cliente.id_cliente
                LEFT JOIN inventario ON guia_traslado.inventario_id_inventario = inventario.id_inventario
                LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
                LEFT JOIN detalle_guia ON detalle_guia.guia_traslado_id_guia_traslado = guia_traslado.id_guia_traslado
                LEFT JOIN proveedor ON inventario.producto_id_producto = producto.id_producto
                WHERE nro_guia = :nro_guia
                GROUP BY nro_guia";

        $stmt = $conexionBD->prepare($sql);
        $stmt->bindParam(':nro_guia', $nro_guia_seleccionado);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($datos) {
            $pdf->SetFont('helvetica', '', 10);
            // Información de la guía
            $pdf->Cell(30, 10, 'Nro. de Guía:', 0, 0, 'L');
            $pdf->Cell(40, 10, $datos[0]['nro_guia'], 0, 1, 'L');
            $pdf->Cell(30, 10, 'Cliente:', 0, 0, 'L');
            $pdf->Cell(40, 10, $datos[0]['Cliente'], 0, 1, 'L');
            $pdf->Cell(30, 10, 'Proveedor:', 0, 0, 'L');
            $pdf->Cell(40, 10, $datos[0]['nombre_prove'], 0, 1, 'L');
            $pdf->Cell(30, 10, 'Dirección:', 0, 0, 'L');
            $pdf->Cell(40, 10, $datos[0]['Direccion'], 0, 1, 'L');
            $pdf->Cell(30, 10, 'Fecha de Emisión:', 0, 0, 'L');
            $pdf->Cell(40, 10, $datos[0]['Fecha'], 0, 1, 'L');
            $pdf->Ln(10);

            // Encabezado de la tabla de productos
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(30, 10, 'Código', 1, 0, 'C');
            $pdf->Cell(70, 10, 'Producto', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Total', 1, 1, 'C');
            $pdf->SetFont('helvetica', '', 10);

            $subtotal = 0;
            $iva = 0;
            $iva_porcentaje = 0.19;

            // Datos de la tabla de productos
            foreach ($datos as $fila) {
                $total_producto = $fila['Total'];
                $subtotal += $total_producto;

                $pdf->Cell(30, 10, $fila['cod_producto'], 1, 0, 'C');
                $pdf->Cell(70, 10, $fila['nombre_producto'], 1, 0, 'C');
                $pdf->Cell(30, 10, $fila['cantidad'], 1, 0, 'C');
                $pdf->Cell(30, 10, number_format($fila['Total'], 2), 1, 1, 'C');
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
            $pdf->Cell(0, 10, 'No se encontraron datos para la guía de traslado.', 0, 1, 'C');
        }

        // Descargar el PDF
        $pdf->Output('guia_traslado_' . $nro_guia_seleccionado . '.pdf', 'D');
    } catch (PDOException $e) {
        echo "Error en la conexión: " . $e->getMessage();
    }
} else {
    echo "No se ha seleccionado un número de guía válido.";
}
