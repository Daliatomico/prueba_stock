<?php

include_once '../models/inventario.php';

//conexion a la base de datos
$dbConnection = new PDO('mysql:host=localhost;dbname=stock_control', 'root', '');


// configura la respuesta a json
header('Content-Type: application/json');

// verificar si se ha recibido solicitud get
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // llama al metodo del modelo para obtener los nivels de inventario
    $nivelesInventario = Inventario::obtenerNivelesInventario();
    
    // me devuelvo los datos como json
    echo json_encode($nivelesInventario);
} else {
    // si no es get devuelte error
    http_response_code(405); 
    echo json_encode(['mensaje' => 'Método no permitido.']);
}



