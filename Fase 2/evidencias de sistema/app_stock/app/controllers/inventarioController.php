<?php

include_once '../models/inventario.php';
include_once '../models/producto.php';
include_once '../models/usuario.php';
include_once '../models/salidas.php';

session_start();



if (isset($_SESSION['usuario']['id_usuario'])) {
    $usuario_id = $_SESSION['usuario']['id_usuario'];
    $registrado_por = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido'];
}

// Filtrar y sanitizar los datos recibidos por POST o GET

//entrada
$id = filter_input(INPUT_POST, 'id_inventario', FILTER_SANITIZE_NUMBER_INT);
$fecha = date('Y-m-d');
$producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_SANITIZE_NUMBER_INT);
$valor_total = filter_input(INPUT_POST, 'valor_total', FILTER_SANITIZE_NUMBER_FLOAT);
$existencia_inicial = filter_input(INPUT_POST, 'existencia_inicial', FILTER_SANITIZE_NUMBER_INT);
//salida
$id_salida = filter_input(INPUT_POST, 'id_salida', FILTER_SANITIZE_NUMBER_INT);
$cantidad_salida = filter_input(INPUT_POST, 'cantidad_salida', FILTER_SANITIZE_NUMBER_INT);
$fecha_salida = date('Y-m-d'); 
$inventario_id = filter_input(INPUT_POST, 'inventario_id', FILTER_SANITIZE_NUMBER_INT);
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_SANITIZE_NUMBER_INT);


$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING) ?? filter_input(INPUT_GET, 'accion', FILTER_SANITIZE_STRING); // Permitir acción desde GET

// Variables para mensajes de estado
$mensaje_aumento = '';
$mensaje_entrada = '';
$mensaje_salida = '';
$mensaje = '';

// Acciones principales del controlador
if ($accion) {
    switch ($accion) {


        case 'agregarEntrada':

            if (!empty($producto_id) && !empty($existencia_inicial) && !empty($usuario_id)) {
                // Llama a agregarEntrada con el ID del producto y el ID del usuario
                $resultado = Inventario::agregarEntrada($existencia_inicial, $fecha, $registrado_por, $usuario_id, $producto_id);
                
                $mensaje_entrada = $resultado ? 'Entrada agregada exitosamente.' :  'Ya existe un producto registrado con este código en el inventario o su estado es agotado.';
            } else {
                $mensaje_entrada = 'El ID del producto, la cantidad y el usuario no pueden estar vacíos para una entrada.';
            }
            break;



        case 'registrarSalida':
            if (!empty($inventario_id) && !empty($cliente_id) && !empty($cantidad_salida)) {
                // Verificar que el registrado_por tenga un valor válido
                if (!empty($registrado_por)) {
                    // Intentar registrar la salida
                    $resultado = Salida::registrarSalida($inventario_id, $cliente_id, $cantidad_salida, $fecha_salida, $registrado_por);

                    $mensaje_salida = $resultado ? 'Salida registrada exitosamente.' : 'Producto agotado.';
                } else {
                    $mensaje_salida = 'El ID del usuario que registra la salida no puede estar vacío.';
                }
            } else {

                $mensaje_salida = 'El ID del inventario y la cantidad no pueden estar vacíos para una salida.';
            }
            break;



        case 'aumentarExistencia':
            if (!empty($_POST['inventario_id']) && !empty($_POST['cantidad_aumentar'])) {
                $inventario_id = $_POST['inventario_id'];
                $cantidad_aumentar = (int)$_POST['cantidad_aumentar'];

                // Llamar a la función para aumentar la existencia
                $resultado = Inventario::aumentarExistencia($inventario_id, $cantidad_aumentar);

                // Mensaje basado en el resultado de la operación
                if ($resultado) {
                    $mensaje_aumento = 'Existencia actualizada correctamente.';
                } else {
                    $mensaje_aumento = 'Error al aumentar la existencia.';
                }
            } else {
                $mensaje_aumento = "El ID del inventario y la cantidad no pueden estar vacíos.";
            }
            break;



        case 'cambiar_estado':
            // cambiar el estado de un producto
            $id = $_POST['id_inventario']; // obtener el id
            $estadoActual = $_POST['estado_inve']; // obtener el estado actual

            // determina el nuevo estado en base al estado actual del producto
            $nuevoEstado = ($estadoActual === 'Activo') ? 'Agotado' : 'Activo'; // alterna entre activo e inactivo
            // llama al metodo para cambiar el estado
            Inventario::cambiarEstadoInventario($id, $nuevoEstado);
            echo "<script>
                        alert('Estado del inventario cambiado a $nuevoEstado.'); 
                        window.location.href='../views/vista_inventario.php'; 
                    </script>";
            break;



        case 'borrar':
            if (!empty($id)) {
                // Llama al método para borrar una entrada y verifica si tiene salidas asociadas
                $resultado = Inventario::borrarEntrada($id);

                if ($resultado === true) {
                    $mensaje = 'La entrada ha sido cancelada.';
                } elseif ($resultado) {

                    $mensaje = 'No se puede cancelar la entrada porque ya registro una salida.';
                } else {
                    var_dump($_POST);
                    //$mensaje = 'Error al cancelar la entrada.';
                }
            } else {

                $mensaje = 'ID de la entrada no puede estar vacío.';
            }
            break;
    }


    // Redirigir con mensajes de alerta después de la acción
    if ($mensaje) {
        echo "<script>
            alert('$mensaje');
            window.location.href='../views/vista_entradas.php';
        </script>";
        exit();
    }

    if ($mensaje_aumento) {
        echo "<script>
            alert('$mensaje_aumento');
            window.location.href='../views/vista_inventario.php';
        </script>";
        exit();
    }

    if ($mensaje_entrada) {
        echo "<script>
            alert('$mensaje_entrada');
            window.location.href='../views/vista_entradas.php';
        </script>";
        exit();
    }

    if ($mensaje_salida) {
        echo "<script>
            alert('$mensaje_salida');
            window.location.href='../views/vista_salidas.php';
        </script>";
        exit();
    }
}

// Configuración de paginación
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;




// funcion Suma total existencias de los productos
$totalExistencias = Inventario::contarExistencias();

// funcion Total valorización inventario
$total_valorizacion = Inventario::calcularValorizacion();

// Incluir la vista de inventario
include '../views/vista_inventario.php';
