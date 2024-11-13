<?php

include_once "../config/bd.php";

class Salida
{
    public $id_salida;
    public $tipo_movimiento;
    public $cantidad_salida;
    public $fecha_salida;
    public $registrado_por;
    public $inventario_id;
    public $ciente_id;

    public static function crearInstancia()
    {
        return BD::crearInstancia();
    }

    // Listar salidas
    public static function listarSalidas($limit, $offset)
    {
        $conexionBD = self::crearInstancia();
        $sql = "SELECT salida.*, 
                producto.cod_producto, 
                producto.nombre_producto,
                cliente.nombre,
                cliente.direccion,
                (SELECT nro_guia FROM guia_traslado 
                    WHERE guia_traslado.inventario_id_inventario = inventario.id_inventario 
                    ORDER BY guia_traslado.nro_guia DESC LIMIT 1) AS nro_guia
            FROM salida 
            LEFT JOIN inventario ON salida.inventario_id_inventario = inventario.id_inventario 
            LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
            LEFT JOIN cliente ON salida.cliente_id_cliente = cliente.id_cliente
            WHERE salida.tipo_movimiento = 'Salida'
            LIMIT :limit OFFSET :offset";
        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // metodo que cuenta el total de filas de la tabla salidas para la paginacion
    public static function contarSalidas()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM salida");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }



    // Método para registrar una salida
    public static function registrarSalida($inventario_id, $cliente_id, $cantidad_salida, $fecha_salida, $registrado_por)
    {
        try {
            $conexionBD = self::crearInstancia();

            // Obtener el registro actual de inventario junto con estado y el usuario asociado
            $consultaInventario = $conexionBD->prepare("
            SELECT id_inventario, estado_inve, existencia_actual, valor_unitario, usuario_id_usuario
            FROM inventario 
            LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
            WHERE id_inventario = :inventario_id
        ");
            $consultaInventario->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaInventario->execute();
            $inventario = $consultaInventario->fetch(PDO::FETCH_ASSOC);

            if (!$inventario) {
                throw new Exception("Registro de inventario no encontrado para el ID proporcionado.");
            }

            if ($inventario['estado_inve'] !== 'Activo') {
                throw new Exception("Inventario de producto agotado.");
            }

            $usuario_id = $inventario['usuario_id_usuario'];

            if ($cantidad_salida <= 0) {
                throw new Exception("La cantidad de salida debe ser mayor que cero.");
            }

            $existencia_actual = $inventario['existencia_actual'] - $cantidad_salida;
            if ($existencia_actual < 0) {
                throw new Exception("No hay suficiente existencia para registrar la salida.");
            }

            $valor_unitario = $inventario['valor_unitario'];
            $valor_total = $existencia_actual * $valor_unitario;

            // Insertar el registro en la tabla `salida`
            $sqlSalida = "INSERT INTO salida (tipo_movimiento, cantidad_salida, fecha_salida, registrado_por, inventario_id_inventario, cliente_id_cliente)
                      VALUES ('Salida', :cantidad_salida, :fecha_salida, :registrado_por, :inventario_id, :cliente_id)";
            $consultaSalida = $conexionBD->prepare($sqlSalida);
            $consultaSalida->bindParam(':cantidad_salida', $cantidad_salida, PDO::PARAM_INT);
            $consultaSalida->bindParam(':fecha_salida', $fecha_salida);
            $consultaSalida->bindParam(':registrado_por', $registrado_por, PDO::PARAM_INT);
            $consultaSalida->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaSalida->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $consultaSalida->execute();

            $salida_id = $conexionBD->lastInsertId();
            if (!$salida_id) {
                throw new Exception("No se pudo obtener el ID de la salida recién insertada.");
            }

            // Insertar en la tabla `movimiento`
            $movimiento = 'Salida';
            $fecha_movimiento = $fecha_salida;

            $sqlMovimiento = "INSERT INTO movimiento (movimiento, fecha_movimiento, inventario_id_inventario, usuario_id_usuario, cliente_id_cliente, salida_id_salida)
                          VALUES (:movimiento, :fecha_movimiento, :inventario_id, :usuario_id, :cliente_id, :salida_id)";
            $consultaMovimiento = $conexionBD->prepare($sqlMovimiento);
            $consultaMovimiento->bindParam(':movimiento', $movimiento);
            $consultaMovimiento->bindParam(':fecha_movimiento', $fecha_movimiento);
            $consultaMovimiento->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaMovimiento->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $consultaMovimiento->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $consultaMovimiento->bindParam(':salida_id', $salida_id, PDO::PARAM_INT);
            $consultaMovimiento->execute();

            // Actualizar la existencia actual y valor total en la tabla `inventario`
            $sqlUpdateInventario = "UPDATE inventario SET existencia_actual = :existencia_actual, valor_total = :valor_total WHERE id_inventario = :inventario_id";
            $consultaUpdateInventario = $conexionBD->prepare($sqlUpdateInventario);
            $consultaUpdateInventario->bindParam(':existencia_actual', $existencia_actual, PDO::PARAM_INT);
            $consultaUpdateInventario->bindParam(':valor_total', $valor_total);
            $consultaUpdateInventario->bindParam(':inventario_id', $inventario['id_inventario'], PDO::PARAM_INT);
            $consultaUpdateInventario->execute();

            // Obtener la dirección del cliente
            $consultaCliente = $conexionBD->prepare("SELECT direccion FROM cliente WHERE id_cliente = :cliente_id");
            $consultaCliente->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $consultaCliente->execute();
            $cliente = $consultaCliente->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                throw new Exception("No se encontró el cliente asociado para la salida.");
            }

            $destino = $cliente['direccion'];
            $nro_guia = 'GT' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT); // Ejemplo: GT000001

            // Insertar la guía en la tabla `guia_traslado`
            $sqlGuia = "INSERT INTO guia_traslado (nro_guia, fecha_emision, destino, cliente_id_cliente, usuario_id_usuario, inventario_id_inventario)
                    VALUES (:nro_guia, :fecha_emision, :destino, :cliente_id, :usuario_id, :inventario_id)";
            $consultaGuia = $conexionBD->prepare($sqlGuia);
            $consultaGuia->bindParam(':nro_guia', $nro_guia);
            $consultaGuia->bindParam(':fecha_emision', $fecha_salida);
            $consultaGuia->bindParam(':destino', $destino);
            $consultaGuia->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            $consultaGuia->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $consultaGuia->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaGuia->execute();


            //obtiene el id de la factura reciente para poder llenar el detalle de la factura
            $guia_id = $conexionBD->lastInsertId();
            if (!$guia_id) {
                throw new Exception("No se pudo obtener el ID de la guia de traslado.");
            }

            //inserta en la tabla detalle factura
            $sqlDetalleGuia = "INSERT INTO detalle_guia (cantidad,guia_traslado_id_guia_traslado)
            VALUES(:cantidad, :guia_traslado_id_guia_traslado)";

            $consultaDetalleGuia = $conexionBD->prepare($sqlDetalleGuia);
            $consultaDetalleGuia->bindParam(':cantidad', $cantidad_salida, PDO::PARAM_INT);
            $consultaDetalleGuia->bindParam(':guia_traslado_id_guia_traslado', $guia_id, PDO::PARAM_INT);

            $consultaDetalleGuia->execute();

            return true;
        } catch (Exception $e) {
            echo "Error al registrar salida: " . $e->getMessage();
            return false;
        }
    }

    //listar nro de guia
    public static function listarNroGuia(){
        $conexionBD = self::crearInstancia();

        $sql = "SELECT nro_guia FROM guia_traslado";
        $consulta = $conexionBD->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);

    }

}
