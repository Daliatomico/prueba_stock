<?php
// modelo cliente que contiene la logica para menejar los clientes
include_once '../models/cliente.php';

//obtiene datos del formulario con filter input para sanitizar las entradas
$id = filter_input(INPUT_POST, 'id_cliente', FILTER_SANITIZE_NUMBER_INT); 
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING); 
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING); 
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING); 
$correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL); 
$contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING); 
$ciudad = filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING); 
$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING); 

// ejuctar logica de acuerdo a la accion
if ($accion) {
    $mensaje = ''; // variable que almacena mensajes de estado

    // manejas las distintas acciones
    switch ($accion) {
        case 'agregar':
            // si el nombre no esta vacio, se agrega el cliente
            if (!empty($nombre)) {
                Cliente::agregarCliente($nombre, $direccion, $telefono, $correo, $contacto,$ciudad);
                $mensaje = "Cliente registrado exitosamente."; 
            } else {
                $mensaje = "El nombre del cliente no puede estar vacío.";
            }
            break;

        case 'editar':
            if (!empty($id) && !empty($nombre)) {
                Cliente::editarCliente($id, $nombre, $direccion, $telefono, $correo, $contacto,$ciudad);
                $mensaje = "Cliente editado exitosamente."; 
            } else {
                $mensaje = "El nombre del cliente no puede estar vacío."; 
            }
            break;

        //cambiar estado del cliente
        case 'cambiar_estado':
            $id = $_POST['id_cliente'];
            $estadoActual = $_POST['estado'];
            $nuevoEstado = ($estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
            Cliente::cambiarEstadoCliente($id,$nuevoEstado);

            echo "<script>
                 alert('Estado del cliente cambiado a $nuevoEstado.');
                 window.location.href='../views/vista_clientes.php';
             </script>";
             break;

        case 'borrar':
            if (!empty($id)) {
                Cliente::borrarCliente($id);
                $mensaje = "Cliente eliminado exitosamente."; 
            }
            break;
    }

    // redirigir con mensaje de alerta
    if ($mensaje) {
        echo "<script>
            alert('$mensaje'); 
            window.location.href='../views/vista_clientes.php'; 
        </script>";
        exit(); 
    }
}

// obtener datos del cliente para poder editarlo
$cliente = null; // esta variable almacena los datos del cliente
if (!empty($id)) {
    $cliente = Cliente::obtenerClientePorId($id); // obtengo los datos del cliente que voy a editar

    // verificar si el cliente existe y la accion es igual a editar
    if (!$cliente && $accion == 'editar') {
        echo "<script>
            alert('No se encontró el cliente con ID: $id'); 
            window.location.href='../views/vista_clientes.php'; 
        </script>";
        exit(); 
    }
}


//necesario para la paginacion, se estable el limite y offset para el listado de categorias
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;


//carga la vista editar_cliente 
if ($accion == 'editar' && $cliente) {
    include '../views/editar_cliente.php'; // muestra la vista
}
?>

