<?php

include_once '../config/bd.php';


class Movimiento
{
    public $id;
    public $movimiento; //tipo de movimiento entrada o salida 
    public $fecha_movimiento;
    public $inventario_id;
    public $usuario_id;
    public $cliente_id;
    public $salida_id;


    public static function crearInstancia()
    {
        return BD::crearInstancia();
    }


    //metodo para listar los movimientos de inventario en el historial
    public static function listarMovimientos($limit, $offset)
    {
        $conexionBD = self::crearInstancia();
        $sql = "
            SELECT movimiento.*, 
                fecha_movimiento,
                producto.cod_producto,
                producto.nombre_producto,
                cliente.direccion,
                CASE 
                    WHEN movimiento.movimiento = 'entrada' THEN inventario.existencia_inicial
                    ELSE NULL 
                END AS existencia_inicial,
                CASE 
                    WHEN movimiento.movimiento = 'salida' THEN salida.cantidad_salida
                    ELSE NULL 
                END AS cantidad_salida
        FROM movimiento 
        LEFT JOIN inventario ON movimiento.inventario_id_inventario = inventario.id_inventario
        LEFT JOIN salida ON salida.inventario_id_inventario = inventario.id_inventario
        LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
        LEFT JOIN usuario ON inventario.usuario_id_usuario = usuario.id_usuario
        LEFT JOIN cliente ON movimiento.cliente_id_cliente = cliente.id_cliente
        LIMIT :limit OFFSET :offset
        ";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    //listar movimientos para card en dashboard
    public static function listarMovimientosCard()
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "
            SELECT movimiento.*, 
                fecha_movimiento,
                producto.cod_producto,
                producto.nombre_producto,
                cliente.direccion,
                CASE 
                    WHEN movimiento.movimiento = 'entrada' THEN inventario.existencia_inicial
                    ELSE NULL 
                END AS existencia_inicial,
                CASE 
                    WHEN movimiento.movimiento = 'salida' THEN salida.cantidad_salida
                    ELSE NULL 
                END AS cantidad_salida
        FROM movimiento 
        LEFT JOIN inventario ON movimiento.inventario_id_inventario = inventario.id_inventario
        LEFT JOIN salida ON salida.inventario_id_inventario = inventario.id_inventario
        LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
        LEFT JOIN usuario ON inventario.usuario_id_usuario = usuario.id_usuario
        LEFT JOIN cliente ON movimiento.cliente_id_cliente = cliente.id_cliente
        ORDER BY id_movimiento DESC LIMIT 4
        ";
            $consulta = $conexionBD->prepare($sql);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error al listar movimientos:" . $e->getMessage();
            return [];
        }
    }


    //metodo que cuenta el total de filas en la tabla proveedores para la paginacion
    public static function contarMovimientos()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM movimiento");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
}
