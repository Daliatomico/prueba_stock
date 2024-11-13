<?php
include_once '../config/bd.php';


class Ubicacion {
    public $id_ubicacion;
    public $nombre_zona;
    public $descripcion_ubi;

    // Crear instancia de la conexión a la base de datos
    public static function crearInstancia() {
        return BD::crearInstancia();
    }

    // metodo para listar las ubicaciones
    public static function listarUbicaciones() {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT * FROM ubicacion");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // metedo para obtener ubicaciones a traves de su id
    public static function obtenerUbicacionPorId($id_ubicacion) {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT * FROM ubicacion WHERE id_ubicacion = :id_ubicacion");
        $consulta->bindParam(':id_ubicacion', $id_ubicacion);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
}

?>