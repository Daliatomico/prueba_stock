<?php
include_once '../config/bd.php';

class Usuario
{
    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $rol;
    public $contrasena;
    public $estado;

    //crea instancia de conexion
    public static function crearInstancia()
    {
        return BD::crearInstancia();
    }


    //metodo para la autenciacion de usuarios
    public static function autenticarUsuario($correo, $contrasena)
    {
        $conexionBD = self::crearInstancia();
        $sql = "SELECT id_usuario, nombre, apellido, correo, rol, contrasena, estado FROM usuario WHERE correo = :correo";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':correo', $correo);
        $consulta->execute();

        if ($resultado = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $hash_alojado = $resultado['contrasena'];

            // Verificar si el usuario está activo
            if ($resultado['estado'] !== 'Activo') {
                return "El usuario esta inactivo."; // Usuario inactivo
            }

            // Verificar la contraseña ingresada
            if (password_verify($contrasena, $hash_alojado)) {
                session_start();
                $_SESSION['usuario'] = [
                    'id_usuario' => $resultado['id_usuario'],
                    'nombre' => $resultado['nombre'],
                    'apellido' => $resultado['apellido'],
                    'correo' => $resultado['correo'],
                    'rol' => $resultado['rol'],
                    'contrasena' => $resultado['contrasena']
                ];
                return true; //autenticacion exitosa
            } else {
                return "La contraseña es incorrecta";
            }
        } else {
            return "El usuario ingresado no existe.";
        }
    }




    //mediante para obtener el rol de usuario mediante su correo
    public static function obtenerRolPorCorreo($correo)
    {
        $conexionBD = self::crearInstancia();
        $sql = "SELECT rol FROM usuario WHERE correo = :correo";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':correo', $correo);
        $consulta->execute();

        if ($resultado = $consulta->fetch(PDO::FETCH_ASSOC)) {
            return $resultado['rol']; // Retornar el rol
        }

        return null; // Retornar null si no se encuentra el usuario
    }



    //metodo para manejar el cierres de sesion
    public static function cerrarSesion()
    {
        session_start();
        session_unset();
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }



    //metodo para listar los usuarios
    public static function listarUsuarios($limit, $offset)
    {
        $conexionBD = self::crearInstancia();
        $sql = "SELECT * FROM usuario LIMIT :limit OFFSET :offset";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }



    // metodo que cuenta el total de filas de la tabla usuario para la paginacion
    public static function UsuariosPaginacion()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM usuario");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }




    //metodo para registrar usuarios
    public static function agregarUsuario($nombre, $apellido, $correo, $rol, $contrasena)
    {
        try {
            $conexionBD = self::crearInstancia();
            $estado = 'Activo';

            // Hashear la contraseña antes de almacenarla
            $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (nombre, apellido, correo, rol, contrasena, estado)
                    VALUES (:nombre, :apellido, :correo, :rol, :contrasena, :estado)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':nombre', $nombre);
            $consulta->bindParam(':apellido', $apellido);
            $consulta->bindParam(':correo', $correo);
            $consulta->bindParam(':rol', $rol);
            $consulta->bindParam(':contrasena', $hashedPassword); // Usa la contraseña hasheada
            $consulta->bindParam(':estado', $estado);


            return $consulta->execute();
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }




    //metodo para cambiar el estado de un usuario
    public static function cambiarEstadoUsuario($id, $nuevoEstado)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE usuario SET estado = :estado WHERE id_usuario = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':estado', $nuevoEstado);
            $consulta->bindParam(':id', $id);


            var_dump($id, $nuevoEstado);

            if ($consulta->execute()) {
                return true; // Estado cambiado
            } else {
                // imprimir error
                var_dump($consulta->errorInfo());
                return false; // No se pudo cambiar el estado
            }
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }


    //metodo para editar usuarios
    public static function editarUsuario($id, $nombre, $apellido, $correo, $rol, $contrasena = null)
    {
        $conexionBD = self::crearInstancia();
        if ($contrasena) {
            //hashea la nueva contraseña si se proporciona
            $hashedPsw = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET nombre = :nombre, apellido = :apellido, correo = :correo,   rol = :rol, contrasena = :contrasena WHERE id_usuario = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':contrasena', $hashedPsw);
        } else {
            $sql = "UPDATE usuario SET nombre = :nombre, apellido = :apellido, correo = :correo, rol = :rol WHERE id_usuario = :id";
            $consulta = $conexionBD->prepare($sql);
        }
        $consulta->bindParam(':nombre', $nombre);
        $consulta->bindParam(':apellido', $apellido);
        $consulta->bindParam(':correo', $correo);
        $consulta->bindParam(':rol', $rol);
        $consulta->bindParam(':id', $id);
        $consulta->execute();
    }

    //actualizar contraseña del usuario
    public static function actualizarContrasena($correo_usuario, $nueva_contrasena)
    {
        try {
            // Crear la conexión a la base de datos
            $conexionBD = self::crearInstancia();

            // Consulta SQL para actualizar la contraseña
            $sql = "UPDATE usuario SET contrasena = :contrasena WHERE correo = :correo";

            // Preparar la consulta
            $consulta = $conexionBD->prepare($sql);

            // Enlazar los parámetros de la consulta con las variables
            $consulta->bindParam(':contrasena', $nueva_contrasena, PDO::PARAM_STR);
            $consulta->bindParam(':correo', $correo_usuario, PDO::PARAM_STR);

            // Ejecutar la consulta
            $consulta->execute();

            return true; // Retorna true si se actualizó correctamente
        } catch (PDOException $e) {
            // Captura y muestra el error en caso de fallo
            echo "Error al actualizar la contraseña: " . $e->getMessage();
            return false;
        }
    }

    //metodo para obtener obtener el id del usuario y poder editar sus datos
    public static function obtenerUsuarioPorId($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT * FROM usuario WHERE id_usuario = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC); // Devuelve un array con los datos del proveedor
        } catch (PDOException $e) {
            echo "Error al obtener el usuario: " . $e->getMessage();
            return false;
        }
    }




    //metodo que obtiene el usuario mediante su correo
    public static function obtenerUsuarioPorCorreo($correo)
    {
        try {
            $conexionBD = self::crearInstancia();

            $sql = "SELECT * FROM usuario WHERE correo = :correo";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':correo', $correo, PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener el usuario: " . $e->getMessage();
            return false;
        }
    }


    //metodo para obtener los datos del usuario logeado
    public static function obtenerUsuarioLogeado()
    {
        if (isset($_SESSION['usuario']['id_usuario'])) {
            $conexionBD = self::crearInstancia();
            $id_usuario = $_SESSION['usuario']['id_usuario'];

            $sql = "SELECT nombre, apellido, correo, rol, contrasena FROM usuario WHERE id_usuario = :id_usuario";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $consulta->execute();

            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        }
        return null;
    }


    //metodo para eliminar usuarios
    public static function borrarUsuario($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "DELETE FROM usuario WHERE id_usuario = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();

            return true;
        } catch (Exception $e) {
            echo "Error al eliminar el usuario:" . $e->getMessage();
            return false;
        }
    }
}
