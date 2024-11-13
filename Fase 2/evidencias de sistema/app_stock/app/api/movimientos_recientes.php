<?php
// Incluir el archivo de conexión a la base de datos o clase de acceso
include_once '../config/bd.php';
include_once '../models/movimiento.php';

try {
    // Obtener los últimos 4 movimientos
    $movimientos = Movimiento::listarMovimientosCard();

    // Enviar encabezado JSON
    header('Content-Type: application/json');
    
    // Convertir el array en formato JSON y devolverlo al frontend
    echo json_encode($movimientos);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

?>