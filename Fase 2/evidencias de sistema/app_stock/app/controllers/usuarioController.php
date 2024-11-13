<?php

// modelo usuario con la logica para manejar las operaciones
include_once '../models/usuario.php';


$id = $_POST['id_usuario'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
// genera un correo automaticamente en base al nombre y apellido del usuario
$correo = strtolower($nombre) . '.' . strtolower($apellido) . '@stock.cl';
$rol = $_POST['rol'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$contrasena_nueva = $_POST['nueva_contrasena'] ?? '';
$accion = $_POST['accion'] ?? '';



// maneja las acciones
if (!empty($accion)) {
    switch ($accion) {

        //agregar un nuevo usuario
        case 'agregar':
            // llama al metodo para agregar un nuevo usuario
            Usuario::agregarUsuario($nombre, $apellido, $correo, $rol, $contrasena);
            echo "<script>
                    alert('Usuario registrado exitosamente.');
                    window.location.href='../views/vista_usuarios.php';
                </script>";
            break;



        //editar usuario
        case 'editar':
            // verifica si el usuario es administrador principal, no podra cambiar el estado
            if ($id == 1) {  //admin principal id 1
                echo "<script>
                    alert('No puedes editar este usuario.');
                    window.location.href='../views/vista_usuarios.php';
                </script>";
            } else {
                Usuario::editarUsuario($id, $nombre, $apellido, $correo, $rol);
                $mensaje = "Usuario editado exitosamente.";
            }
            break;



        //cambia de estado del usuario
        case 'cambiar_estado':
            // verifica si el usuario es administrador principal, no podra cambiar el estado
            if ($id == 1) {  //admin principal id 1
                echo "<script>
                    alert('No puedes cambiar el estado de este usuario.');
                    window.location.href='../views/vista_usuarios.php';
                </script>";
            } else {
                // cambia el estado del usuario si no es admin principal
                $id = $_POST['id_usuario'];
                $estadoActual = $_POST['estado']; // obtiene el estado actual del usuario
                // cambiar el estado del usuario y alternar entre Activo e Inactivo
                $nuevoEstado = ($estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
                Usuario::cambiarEstadoUsuario($id, $nuevoEstado); // llama al metodo 

                echo "<script>
                    alert('Estado del usuario cambiado a $nuevoEstado.');
                    window.location.href='../views/vista_usuarios.php';
                </script>";
            }
            break;



        //borrar usuario
        case 'borrar':
            // verifica si el usuario que se intenta eliminar es el administrador principal
            if ($id == 1) { //en este caso el admin principal tiene el id 1
                echo "<script>
                    alert('No puedes eliminar este usuario.');
                    window.location.href='../views/vista_usuarios.php';
                </script>";
            } else {
                $resultado =Usuario::borrarUsuario($id);
                if ($resultado === true) {
                    $mensaje = "Usuario eliminado exitosamente.";  
                }else {
                    $mensaje="Este usuario esta asociado a operaciones, no puede ser eliminado.";
                }
            }
                
            
            break;

        
        //actulizar la contraseña
        case 'cambiar_contrasena':
            session_start(); // Inicia la sesión si no lo has hecho ya

            $mensaje = ''; // Inicializamos la variable de mensaje
            if (isset($_SESSION['usuario'])) {
                // Obtener el correo del usuario logueado desde la sesión
                $correo_usuario = $_SESSION['usuario']['correo'];

                // Recoger los datos del formulario
                $contrasena_actual = $_POST['contrasena']; // Contraseña actual ingresada
                $contrasena_nueva = $_POST['contrasena_nueva']; // Nueva contraseña
                $confirmar_contrasena = $_POST['confirmar_contrasena']; // Confirmar nueva contraseña

                // Verificar que las contraseñas coincidan
                if ($contrasena_nueva !== $confirmar_contrasena) {
                    $mensaje = 'Las contraseñas no coinciden.';
                    break; // Termina el flujo si las contraseñas no coinciden
                }

                // Obtener el usuario desde la base de datos usando el correo
                $usuario = new Usuario();
                $usuario_data = $usuario->obtenerUsuarioPorCorreo($correo_usuario); // Método que obtiene el usuario por correo

                // Verificar si el usuario existe
                if ($usuario_data) {
                    // Verificar si la contraseña actual es correcta usando password_verify
                    if (password_verify($contrasena_actual, $usuario_data['contrasena'])) {
                        // Hashear la nueva contraseña
                        $nueva_contrasena_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);

                        // Actualizar la contraseña en la base de datos
                        $actualizado = $usuario->actualizarContrasena($correo_usuario, $nueva_contrasena_hash);

                        if ($actualizado) {
                            $mensaje = 'Contraseña cambiada exitosamente.';
                        } else {
                            $mensaje = 'Error al cambiar la contraseña. Intenta nuevamente.';
                        }
                    } else {
                        $mensaje = 'La contraseña actual es incorrecta.';
                    }
                } else {
                    $mensaje = 'No se encontró el usuario.';
                }
            } else {
                $mensaje = 'Debes estar logueado para cambiar la contraseña.';
            }
            break;
    }



    // mostrar mensaje y redirigiar si hay alguno configurado
    if ($mensaje) {
        echo "<script>
            alert('$mensaje'); // Mostrar mensaje de alerta
            window.location.href='../views/vista_usuarios.php'; 
        </script>";
        exit();
    }
}




//necesario para la paginacion, se estable el limite y offset para el listado de categorias
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;


// lista de usuarios para mostrar en la vista_usuarios
$listaUsuarios = Usuario::listarUsuarios($limit, $offset);
$totalUsuarios = Usuario::UsuariosPaginacion(); //contar el total de categorias
$totalPaginas = ceil($totalUsuarios / $limit); // calular el total de paginas en vista_categorias
