<?php  
include_once '../config/bd.php';

class Cliente{
    public $id;
    public $nombre;
    public $direccion;
    public $telefono;
    public $correo;
    public $contacto;
    public $estado;
    public $ciudad;
    

    public static function crearInstancia() {
        return BD::crearInstancia();
    }

    
    //metodo para listar clientes
    public static function listarClientes($limit, $offset) {
        $conexionBD = BD::crearInstancia();
        $sql = "SELECT * FROM cliente LIMIT :limit OFFSET :offset";  
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit',$limit,PDO::PARAM_INT);
        $consulta->bindParam(':offset',$offset,PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
       
    }

    //listar clientes para la salida
    public static function listarClienteSalida(){
        $conexionBD = self::crearInstancia();
        $sql = "SELECT * FROM  cliente";
        $consulta = $conexionBD->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // metodo que cuenta el total de filas de la tabla clientes para la paginacion
    public static function contarClientes() {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM cliente");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }



    //metodo para agregar clientes
    public static function agregarCliente($nombre, $direccion, $telefono , $correo, $contacto,$ciudad ) {
        try {
            $conexionBD = self::crearInstancia();
            $estado = "Activo";
            
            $sql = "INSERT INTO cliente (nombre, direccion, telefono , correo , contacto, estado, ciudad ) VALUES (:nombre, :direccion, :telefono, :correo, :contacto, :estado, :ciudad)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':nombre', $nombre);
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


    //metodo que permite cambiar el estado de un cliente
    public static function cambiarEstadoCliente($id,$nuevoEstado)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE cliente SET estado = :estado WHERE id_cliente = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':estado', $nuevoEstado);
            $consulta->bindParam(':id', $id);

            var_dump($id, $nuevoEstado);
            if ($consulta->execute()) {
                return true;
            }else{
                var_dump($consulta->errorInfo());
                return false;
            }
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }

    //metodo para editar clientes
    public static function editarCliente($id, $nombre, $direccion, $telefono, $correo, $contacto,$ciudad ) {
        $conexionBD = self::crearInstancia();
        $sql = "UPDATE cliente SET nombre = :nombre, direccion = :direccion, telefono = :telefono, correo = :correo, contacto = :contacto, ciudad = :ciudad WHERE id_cliente = :id";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':nombre', $nombre);
        $consulta->bindParam(':direccion', $direccion);
        $consulta->bindParam(':telefono', $telefono);
        $consulta->bindParam(':correo', $correo);
        $consulta->bindParam(':contacto', $contacto);
        $consulta->bindParam(':ciudad', $ciudad);
        $consulta->bindParam(':id', $id);
        $consulta->execute();
    }

    //metodo para borrar clientes
    public static function borrarCliente($id) {
        $conexionBD = self::crearInstancia();
        $sql = "DELETE FROM cliente WHERE id_cliente = :id";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':id', $id);
        $consulta->execute();
    }

    //metodo que obtiene el id del cliente para editarlo
    public static function obtenerClientePorId($id) {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT * FROM cliente WHERE id_cliente = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC); // devuelve  array asociativo con los datos del cliente
        } catch (PDOException $e) {
            echo "Error al obtener el proveedor: " . $e->getMessage();
            return false;
        }
    }


}



?>