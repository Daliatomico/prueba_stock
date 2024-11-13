<?php  
include_once '../config/bd.php';

class Proveedor {
    public $id;
    public $nombre_prove;
    public $direccion;
    public $telefono;
    public $correo;
    public $contacto;
    public $estado;
    public $ciudad;
    

    public static function crearInstancia() {
        return BD::crearInstancia();
    }

    //metodo para listar proveedores
    public static function listarProveedores($limit,$offset) {
        $conexionBD = BD::crearInstancia();
        $sql = "SELECT * FROM proveedor LIMIT :limit OFFSET :offset";  
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam('offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    //metodo que cuenta el total de filas en la tabla proveedores para la paginacion
    public static function contarProveedores() {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM proveedor");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    //metodo para agregar proveedores
    public static function agregarProveedor($nombre_prove, $direccion, $telefono , $correo, $contacto, $ciudad ) {
        try {
            $conexionBD = self::crearInstancia();
            $estado = 'Activo';

            $sql = "INSERT INTO proveedor (nombre_prove, direccion, telefono , correo , contacto, estado,ciudad) VALUES (:nombre_prove, :direccion, :telefono, :correo, :contacto, :estado,:ciudad)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':nombre_prove', $nombre_prove);
            $consulta->bindParam(':direccion', $direccion);
            $consulta->bindParam(':telefono', $telefono);
            $consulta->bindParam(':correo', $correo);
            $consulta->bindParam(':contacto', $contacto);
            $consulta->bindParam(':estado', $estado);
            $consulta->bindParam(':ciudad', $ciudad);
            $consulta->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }

    //metodo para cambiar el estado de un proveedor
    public static function cambiarEstadoProveedor($id, $nuevoEstado)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE proveedor SET estado = :estado WHERE id_proveedor = :id";
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

    //metodo para editar proveedores
    public static function editarProveedor($id, $nombre_prove, $direccion, $telefono, $correo, $contacto,$ciudad) {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE proveedor SET nombre_prove = :nombre_prove, direccion = :direccion, telefono = :telefono, correo = :correo, contacto = :contacto, ciudad = :ciudad WHERE id_proveedor = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':nombre_prove', $nombre_prove);
            $consulta->bindParam(':direccion', $direccion);
            $consulta->bindParam(':telefono', $telefono);
            $consulta->bindParam(':correo', $correo);
            $consulta->bindParam(':contacto', $contacto);
            $consulta->bindParam(':ciudad', $ciudad);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
        } catch (PDOException $e) {
            echo "Error al editar el proveedor: " . $e->getMessage();
        }
    }

    //metodo para eliminar proveedores
    public static function borrarProveedor($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "DELETE FROM proveedor WHERE id_proveedor = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            
            return true;
        } catch (Exception $e) {
            echo "Error al borrar el proveedor: " .$e->getMessage();
            return false;
        }
    }

    //metodo para obtener proveedor mediante su id y poder editar
    public static function obtenerProveedorPorId($id) {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT * FROM proveedor WHERE id_proveedor = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC); // Devuelve un array con los datos del proveedor
        } catch (PDOException $e) {
            echo "Error al obtener el proveedor: " . $e->getMessage();
            return false;
        }
    }
}
?>
