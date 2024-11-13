<?php
// modelo proveedor para manejar las operaciones
include_once '../models/proveedor.php';

//filtrar y sanitizar datos obtenidos por post o get
$id = filter_input(INPUT_POST, 'id_proveedor', FILTER_SANITIZE_NUMBER_INT) ?: filter_input(INPUT_GET, 'id_proveedor', FILTER_SANITIZE_NUMBER_INT);
$nombre_prove = filter_input(INPUT_POST, 'nombre_prove', FILTER_SANITIZE_STRING);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
$contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING);
$ciudad = filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING);
$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

// ejecutar la logica de acuerdo a la accion
if ($accion) {
    $mensaje = ''; // variable para mensajes de estado

    // manejar las distintas acciones
    switch ($accion) {
        case 'agregar':
            //validad que el nombre del proveedor no este vacio
            if (!empty($nombre_prove)) {
                //llama al metodo para agregar producto
                Proveedor::agregarProveedor($nombre_prove, $direccion, $telefono, $correo, $contacto,$ciudad);
                $mensaje = 'Proveedor registrado exitosamente.';
            } else {
                $mensaje = 'El nombre del proveedor es obligatorio.';
            }
            break;

            //editar informacion del proveedor
        case 'editar':
            if (!empty($id) && !empty($nombre_prove)) {
                Proveedor::editarProveedor($id, $nombre_prove, $direccion, $telefono, $correo, $contacto,$ciudad);
                $mensaje = 'Proveedor editado exitosamente.';
            } else {
                $mensaje = 'El ID o el nombre del proveedor no pueden estar vacíos.';
            }
            break;


            //cambiar estado del proveedor
        case 'cambiar_estado':

            $id = $_POST['id_proveedor'];
            $estadoActual = $_POST['estado']; // obtiene el estado actual del usuario
            // cambiar el estado del proveedor y alternar entre Activo e Inactivo
            $nuevoEstado = ($estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
            Proveedor::cambiarEstadoProveedor($id, $nuevoEstado); // llama al metodo 

            echo "<script>
                 alert('Estado del usuario cambiado a $nuevoEstado.');
                 window.location.href='../views/vista_proveedores.php';
             </script>";
            break;


        case 'borrar':
            if (!empty($id)) {
                // Llama al método para borrar una entrada y verifica si tiene salidas asociadas
                $resultado = Proveedor::borrarProveedor($id);

                if ($resultado === true) {
                    // Mensaje de alerta cuando la entrada tiene salidas asociadas
                    $mensaje = 'Proveedor Eliminado existosamente.';
                } else {

                    $mensaje = 'No se puede eliminar el proveedor porque esta asociado a un inventario.';
                }
            } else {
                // Mensaje cuando no se proporciona un ID
                $mensaje = 'ID del proveedor no puede estar vacío.';
            }
            break;
    }

    // muestra mensaje configurado y redirige
    if ($mensaje) {
        echo "<script>
            alert('$mensaje'); 
            window.location.href='../views/vista_proveedores.php'; 
        </script>";
        exit();
    }
}

// obtener el proveedor a editar
$proveedor = null; // almacena los datos del proveedor
if (!empty($id)) {
    //llama al metodo
    $proveedor = Proveedor::obtenerProveedorPorId($id);

    // verificar si existe el proveedor y la accion es editar
    if (!$proveedor && $accion == 'editar') {
        echo "<script>
            alert('No se encontró el proveedor con ID: $id'); 
            window.location.href='../views/vista_proveedores.php'; 
        </script>";
        exit();
    }
}


//necesario para la paginacion, se estable el limite y offset para el listado de categorias
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;

// obtener lista de proveedor para mostrar en la vista_proveedores
$listaProveedores = Proveedor::listarProveedores($limit, $offset);
$totalProveedores = Proveedor::contarProveedores(); 
$totalPaginas = ceil($totalProveedores / $limit); 
// carga la vista editar proveedor
if ($accion == 'editar' && $proveedor) {
    include '../views/editar_prove.php';
}
