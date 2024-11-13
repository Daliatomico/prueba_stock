<?php
include_once '../config/bd.php';

class Categoria {
    public $id;
    public $nombre_categoria;
    public $descripcion;
    

    //instancia de conexion con la base de datos
    public static function crearInstancia() {
        return BD::crearInstancia();
    }

    // metodo para listar las categorias
    public static function listarCategorias($limit, $offset) {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT * FROM categoria LIMIT :limit OFFSET :offset");
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // metodo que cuenta el total de filas en la tabla categorias para paginacion
    public static function contarCategorias() {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM categoria");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }


    //metodo para agregar una categorias
    public static function agregarCategoria($nombre_categoria, $descripcion) {
        try {
            $conexionBD = self::crearInstancia();


            $sql = "INSERT INTO categoria (nombre_categoria, descripcion) VALUES (:nombre_categoria, :descripcion)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':nombre_categoria', $nombre_categoria);
            $consulta->bindParam(':descripcion', $descripcion);
            $consulta->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }

    //metodo para editar categorias
    public static function editarCategoria($id, $nombre_categoria, $descripcion) {
        $conexionBD = self::crearInstancia();
        $sql = "UPDATE categoria SET nombre_categoria = :nombre_categoria, descripcion = :descripcion WHERE id_categoria = :id";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':nombre_categoria', $nombre_categoria);
        $consulta->bindParam(':descripcion', $descripcion);
        $consulta->bindParam(':id', $id);
        $consulta->execute();
    }

    //metodo que obtiene el id de la categoria para poder editarla
    public static function obtenerCategoriaPorId($id) {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT * FROM categoria WHERE id_categoria = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC); // Devuelve un array  con los datos del proveedor
        } catch (PDOException $e) {
            echo "Error al obtener la categoría: " . $e->getMessage();
            return false;
        }
    }


    //metodo para borrar una categoria
    public static function borrarCategoria($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "DELETE FROM categoria WHERE id_categoria = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            
            return true;
        } catch (Exception $e) {
            echo "Error al borrar la categoría: " .$e->getMessage();
            return false;
        }
    }
}

?>