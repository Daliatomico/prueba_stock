<?php


include_once '../models/Producto.php';
include_once '../models/Ubicacion.php';



// filtrar y sanitzar los datos recibidos por POST
$id = filter_input(INPUT_POST, 'id_producto', FILTER_SANITIZE_NUMBER_INT);
// $codigo = filter_input(INPUT_POST, 'cod_producto', FILTER_SANITIZE_STRING);
$nombre_producto = filter_input(INPUT_POST, 'nombre_producto', FILTER_SANITIZE_STRING);
$unidad_medida = filter_input(INPUT_POST, 'unidad_medida', FILTER_SANITIZE_STRING);
$valor = filter_input(INPUT_POST, 'valor_unitario', FILTER_SANITIZE_NUMBER_INT);
$fecha_registro_prod = date('Y-m-d'); // Fecha actual en formato 'Y-m-d'
$proveedor_id = filter_input(INPUT_POST, 'proveedor_id', FILTER_SANITIZE_NUMBER_INT); // obtener id proveedor
$categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_SANITIZE_NUMBER_INT); // obtener id categoria
$ubicacion_id = filter_input(INPUT_POST, 'ubicacion_id', FILTER_SANITIZE_NUMBER_INT); // obtener id ubicacion
$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

// ejecuta la logia de acuerdo a la accion
if ($accion) {
    $mensaje = ''; // variable para mensaje de estado


    // menejar las distintas acciones
    switch ($accion) {
        case 'agregar':
            // Verifica que los campos obligatorios no estén vacíos
            if (!empty($nombre_producto) && !empty($proveedor_id) && !empty($categoria_id)) {
                try {
                    // Intenta agregar el producto
                    Producto::agregarProducto($nombre_producto, $unidad_medida, $valor, $fecha_registro_prod, $proveedor_id, $categoria_id, $ubicacion_id);
                    $mensaje = 'Producto registrado exitosamente.';
                } catch (Exception $e) {
                    // Captura la excepción y muestra el mensaje
                    $mensaje = $e->getMessage();
                }
            } else {
                $mensaje = 'Por favor, complete todos los campos obligatorios.';
            }
            break;


        case 'editar':
            if (!empty($id) && !empty($nombre_producto)) {
                // llama al metodo para editar un producto existente
                Producto::editarProducto($id, $nombre_producto,  $unidad_medida,  $valor,  $fecha_registro_prod, $proveedor_id, $categoria_id, $ubicacion_id);
                $mensaje = 'Producto editado exitosamente.';
            } else {
                $mensaje = 'ID o nombre del producto no puede estar vacío.';
            }
            break;

        case 'borrar':
            if (!empty($id)) {
                // Llama al método para borrar una entrada y verifica si tiene salidas asociadas
                $resultado = Producto::borrarProducto($id);

                if ($resultado === true) {
                    // Mensaje de alerta cuando la entrada tiene salidas asociadas
                    $mensaje = 'Producto Eliminado existosamente.';
                } else {

                    $mensaje = 'No se puede eliminar el producto porque esta asociado a un inventario.';
                }
            } else {
                // Mensaje cuando no se proporciona un ID
                $mensaje = 'ID del producto no puede estar vacío.';
            }
            break;


        case 'cambiar_estado':
            // cambiar el estado de un producto
            $id = $_POST['id_producto']; // obtener el id
            $estadoActual = $_POST['estado']; // obtener el estado actual

            // determina el nuevo estado en base al estado actual del producto
            $nuevoEstado = ($estadoActual === 'Activo') ? 'Inactivo' : 'Activo'; // alterna entre activo e inactivo
            // llama al metodo para cambiar el estado
            Producto::cambiarEstadoProducto($id, $nuevoEstado);
            echo "<script>
                    alert('Estado del producto cambiado a $nuevoEstado.'); 
                    window.location.href='../views/vista_productos.php'; 
                </script>";
            break;
    }

    // mostrar mensaje y redirigiar si hay alguno configurado
    if ($mensaje) {
        echo "<script>
            alert('$mensaje'); // Mostrar mensaje de alerta
            window.location.href='../views/vista_productos.php'; 
        </script>";
        exit();
    }
}


//necesario para obtener los datos del producto que voy a editar
$producto = null; // inicializa la variable para producto
if (!empty($id)) {
    // obtiene los datos del producto que se van a editar
    $producto = Producto::obtenerProductoPorId($id);

    // verifica la existencia del producto
    if (!$producto && $accion == 'editar') {
        echo "<script>
            alert('No se encontró el producto con ID: $id'); 
            window.location.href='../views/vista_productos.php'; 
        </script>";
        exit();
    }
}


//necesario para la paginacion, se estable el limite y offset para el listado de categorias
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;


//  lista de productos para mostrar en la vista_productos
$listaProductos = Producto::listarProductos($limit, $offset);
//paginacion de los productos
$totalProductos = Producto::ProductosPaginacion();
$totalPaginas = ceil($totalProductos / $limit);


//funcion total de productos
$totalContar = Producto::contarProductos();


// esto carga la vista para editar un producto
if ($accion == 'editar' && $producto) {
    include '../views/editar_prod.php';
}


// obtener listado de ubicaciones
$listaUbicaciones = Ubicacion::listarUbicaciones();
