<?php


include_once '../config/bd.php';


class Inventario
{
    public $id;
    public $tipo_movimiento;
    public $existencia_inicial;
    public $existencia_actual;
    public $fecha;
    public $valor_total;
    public $registrado_por;
    public $estado;
    public $producto_id;
    public $usuario_id;


    //crea una instancia de conexion con la base de datos
    public static function crearInstancia()
    {
        return BD::crearInstancia();
    }


    // Listar inventario entrada
    public static function listarInventarios($limit, $offset)
    {
        $conexionBD = self::crearInstancia();
        $sql = "SELECT 
                inventario.*, 
                proveedor.nombre_prove,
                producto.cod_producto,
                producto.nombre_producto,
                producto.valor_unitario,
                producto.unidad_medida, 
                ubicacion.nombre_zona,
                factura.nro_factura
            FROM 
                inventario
            LEFT JOIN 
                producto ON inventario.producto_id_producto = producto.id_producto
            LEFT JOIN 
                proveedor ON producto.proveedor_id_proveedor = proveedor.id_proveedor
            LEFT JOIN 
                ubicacion ON producto.ubicacion_id_ubicacion = ubicacion.id_ubicacion
            LEFT JOIN 
                factura ON factura.inventario_id_inventario = inventario.id_inventario
            WHERE 
                inventario.tipo_movimiento = 'Entrada'
            LIMIT :limit OFFSET :offset";

        $consulta = $conexionBD->prepare($sql);
        $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
        $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }




    // Método para registrar una entrada al inventario
    public static function agregarEntrada($existencia_inicial, $fecha, $registrado_por, $usuario_id, $producto_id, $cliente_id = null, $salida_id = null)
    {
        try {
            $conexionBD = self::crearInstancia();

            //verifica si el producto ya existe en el inventario y no esta en estado agotado
            $consultarExistencia = $conexionBD->prepare("SELECT * FROM inventario WHERE producto_id_producto = :producto_id AND estado_inve IN ('Activo','Agotado')");
            $consultarExistencia->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
            $consultarExistencia->execute();

            //si el producto existe en el inventario se muestra una excepcion
            if ($consultarExistencia->rowCount() > 0) {

                $productoExistente = $consultarExistencia->fetch(PDO::FETCH_ASSOC);

                if ($productoExistente['estado_inve']== 'Agotado') {
                   throw new Exception("El producto esta agotado y no se generar entrada.");
                }else {
                    throw new Exception("El producto ya existe en el inventario y no se puede agregar nuevamente.");
                }

                
            }

            // Configuración de valores
            $existencia_actual = $existencia_inicial;
            $estado = 'Activo';
            $tipo_movimiento = 'Entrada';

            // Obtiene el valor unitario del producto para calcular el valor total
            $consultaProducto = $conexionBD->prepare("SELECT valor_unitario FROM producto WHERE id_producto = :producto_id");
            $consultaProducto->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
            $consultaProducto->execute();
            $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

            // Verifica si el producto existe
            if (!$producto) {
                throw new Exception("Producto no encontrado");
            }

            // Limpiar valor_unitario de símbolos de moneda y puntos
            $valor_unitario = $producto['valor_unitario'];


            // Verificar si el valor unitario es válido
            if ($valor_unitario <= 0) {
                throw new Exception("El valor unitario no es válido o es cero.");
            }

            // Calcular el valor total
            $valor_total = $existencia_actual * $valor_unitario;

            // Verificar si el valor total es mayor que cero
            if ($valor_total <= 0) {
                throw new Exception("El total facturado no puede ser cero. Verifica los valores del producto o la existencia.");
            }

            //calculo del iva 
            $iva = $valor_total * 0.19;

            $valor_total_con_iva= $valor_total + $iva;

            // Inserción en la tabla inventario
            $sql = "INSERT INTO inventario (tipo_movimiento, existencia_inicial, existencia_actual, fecha, registrado_por, estado_inve, producto_id_producto, usuario_id_usuario, valor_total)
            VALUES (:tipo_movimiento, :existencia_inicial, :existencia_actual, :fecha, :registrado_por, :estado_inve, :producto_id, :usuario_id, :valor_total)";

            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':tipo_movimiento', $tipo_movimiento);
            $consulta->bindParam(':existencia_inicial', $existencia_inicial);
            $consulta->bindParam(':existencia_actual', $existencia_actual);
            $consulta->bindParam(':fecha', $fecha);
            $consulta->bindParam(':registrado_por', $registrado_por);
            $consulta->bindParam(':estado_inve', $estado);
            $consulta->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
            $consulta->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $consulta->bindParam(':valor_total', $valor_total_con_iva);

            $consulta->execute();

            // Proceso de inserción en la tabla movimiento al registrar una entrada
            $inventario_id = $conexionBD->lastInsertId();
            if (!$inventario_id) {
                throw new Exception("No se pudo obtener el ID del inventario recién insertado.");
            }


            // Insertar en la tabla movimiento
            $movimiento = 'Entrada';
            $fecha_movimiento = $fecha;

            $sqlMovimiento = "INSERT INTO movimiento (movimiento, fecha_movimiento, inventario_id_inventario, usuario_id_usuario, cliente_id_cliente, salida_id_salida)
                        VALUES (:movimiento, :fecha_movimiento, :inventario_id, :usuario_id, :cliente_id, :salida_id)";

            $consultaMovimiento = $conexionBD->prepare($sqlMovimiento);
            $consultaMovimiento->bindParam(':movimiento', $movimiento);
            $consultaMovimiento->bindParam(':fecha_movimiento', $fecha_movimiento);
            $consultaMovimiento->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaMovimiento->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

            // Asignación de cliente_id
            if ($cliente_id !== null) {
                $consultaMovimiento->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
            } else {
                $consultaMovimiento->bindValue(':cliente_id', null, PDO::PARAM_NULL);
            }

            // Asignación de salida_id
            if ($salida_id !== null) {
                $consultaMovimiento->bindParam(':salida_id', $salida_id, PDO::PARAM_INT);
            } else {
                $consultaMovimiento->bindValue(':salida_id', null, PDO::PARAM_NULL);
            }

            $consultaMovimiento->execute();


            // Insertar la factura en la tabla factura
            $nro_factura = 'FCT' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT); // Ejemplo: FCT000001

            $sqlFactura = "INSERT INTO factura (nro_factura, fecha_emision, total_facturado, usuario_id_usuario, inventario_id_inventario)
                       VALUES (:nro_factura, :fecha_emision, :total_facturado, :usuario_id_usuario, :inventario_id_inventario)";

            $consultaFactura = $conexionBD->prepare($sqlFactura);
            $consultaFactura->bindParam(':nro_factura', $nro_factura);
            $consultaFactura->bindParam(':fecha_emision', $fecha);
            $consultaFactura->bindParam(':total_facturado', $valor_total_con_iva);
            $consultaFactura->bindParam(':usuario_id_usuario', $usuario_id, PDO::PARAM_INT);
            $consultaFactura->bindParam(':inventario_id_inventario', $inventario_id, PDO::PARAM_INT);

            $consultaFactura->execute();

            //obtiene el id de la factura reciente para poder llenar el detalle de la factura
            $factura_id = $conexionBD->lastInsertId();
            if (!$factura_id) {
                throw new Exception("No se pudo obtener el ID de la factura.");
            }

            //inserta en la tabla detalle factura
            $sqlDetalleFactura = "INSERT INTO detalle_factura (cantidad,factura_id_factura)
            VALUES(:cantidad, :factura_id_factura)";

            $consultaDetalleFactura = $conexionBD->prepare($sqlDetalleFactura);
            $consultaDetalleFactura->bindParam(':cantidad', $existencia_inicial, PDO::PARAM_INT);
            $consultaDetalleFactura->bindParam(':factura_id_factura', $factura_id, PDO::PARAM_INT);

            $consultaDetalleFactura->execute();

            return true;
        } catch (PDOException $e) {
            echo "Error al agregar entrada al inventario: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }



    //metodo para cancelar una entrada
    public static function borrarEntrada($id)
    {
        try {
            $conexionBD = self::crearInstancia();

            //verifica si la entrada esta asociada a una salida
            $consulta = $conexionBD->prepare("SELECT COUNT(*) as total
            FROM salida 
            WHERE inventario_id_inventario = :id");
            $consulta->bindParam(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total'] > 0) {
                //si existen salidas asociadas no se podra eliminar la entrada
                return 'Asociada';
            }
            //si no existen salidas asociadas se puede proceder con la eliminacion
            $consultaEliminar = $conexionBD->prepare("DELETE FROM inventario WHERE id_inventario = :id");
            $consultaEliminar->bindParam(':id', $id, PDO::PARAM_INT);
            $consultaEliminar->execute();

            return true;
        } catch (Exception $e) {
            //menesaje de excepcion
            echo "Error al borrar la entrada: " . $e->getMessage();
            return false;
        }
    }



    // metodo que obtiene el listado de productos
    public static function listarProductos()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_producto, cod_producto, nombre_producto, cantidad, valor_unitario FROM producto");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // metodo que obtiene la lista de usuarios
    public static function listarUsuarios()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_usuario, nombre FROM usuario");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // metodo que obtiene la lista de ubicaciones
    public static function listarUbicaciones()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_ubicacion, nombre_zona FROM ubicacion");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // este metodo cuenta el total de filas en la tabla inventario para realizar paginacion
    public static function contarInventarios()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM inventario");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }



    // metodo que cuenta el total de existencias de un producto mediante una funcion en la bd
    public static function contarExistencias()
    {
        try {
            $conexionBD = self::crearInstancia();

            // Prepara la consulta para llamar a la función
            $consulta = $conexionBD->query("SELECT calcular_total_existencia() AS total");

            // Obtiene el resultado
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            $total_existencia = $resultado['total'];

            return $total_existencia; // Retorna el total de la valorización
        } catch (PDOException $e) {
            echo "Error al obtener el total de existencias: " . $e->getMessage();
            return false;
        }
    }


    //metodo que calcula el valor total de inventario mediante una funcion en la bd
    public static function calcularValorizacion()
    {
        try {
            $conexionBD = self::crearInstancia();

            // Prepara la consulta para llamar a la función
            $consulta = $conexionBD->query("SELECT calcular_valorizacion_total() AS total_valorizacion");

            // Obtiene el resultado
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            $total_valorizacion = $resultado['total_valorizacion'];

            return $total_valorizacion; // Retorna el total de la valorización
        } catch (PDOException $e) {
            echo "Error al obtener el total de valorización: " . $e->getMessage();
            return false;
        }
    }


    // metodo para obtener los niveles de inventario y mostrarlos en un grafico
    public static function obtenerNivelesInventario()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("
            SELECT producto.nombre_producto, existencia_actual
            FROM inventario
            LEFT JOIN producto ON inventario.producto_id_producto = producto.id_producto
            WHERE existencia_actual < 20
            ORDER BY existencia_actual ASC
        ");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // Método para verificar productos con existencia baja en el inventario mediante una vista en la bd
    public static function verificarExistenciasBajas()
    {
        try {
            $conexionBD = self::crearInstancia();

            // Cambiar la consulta para seleccionar desde la vista
            $consulta = $conexionBD->prepare("SELECT * FROM vista_notificaciones_baja_existencia");

            $consulta->execute();

            // Verifica si hay productos con existencias bajas
            $productosConExistenciasBajas = $consulta->fetchAll(PDO::FETCH_ASSOC);

            if ($productosConExistenciasBajas) {
                return $productosConExistenciasBajas;
            } else {
                // No hay productos con existencias bajas
                return [];
            }
        } catch (PDOException $e) {
            // Manejo de errores en caso de que ocurra un problema con la consulta
            var_dump("Error al verificar existencias bajas: " . $e->getMessage());
            return [];
        }
    }

    //aumenta la existencia de un producto en el inventario
    public static function aumentarExistencia($inventario_id, $cantidad_aumentar)
    {
        try {
            $conexionBD = self::crearInstancia();

            // Obtener el registro actual de inventario para el ID proporcionado
            $consultaInventario = $conexionBD->prepare("SELECT existencia_actual, producto_id_producto FROM inventario WHERE id_inventario = :inventario_id");
            $consultaInventario->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaInventario->execute();
            $inventario = $consultaInventario->fetch(PDO::FETCH_ASSOC);

            if (!$inventario) {
                throw new Exception("Registro de inventario no encontrado para el ID proporcionado.");
            }

            // Obtener el valor unitario del producto asociado al inventario
            $id_producto = $inventario['producto_id_producto'];
            $consultaProducto = $conexionBD->prepare("SELECT valor_unitario FROM producto WHERE id_producto = :id_producto");
            $consultaProducto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
            $consultaProducto->execute();
            $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto no encontrado para el ID proporcionado.");
            }

            // Obtener el valor unitario del producto
            $valor_unitario = $producto['valor_unitario'];

            $iva = 0.19;
            $iva_unitario = $valor_unitario * $iva;

            // Calcula la nueva existencia actual
            $nueva_existencia_actual = $inventario['existencia_actual'] + $cantidad_aumentar;

            //calculo el nuevo valor total del inventario sin iva 
            $nuevo_valor_total_sin_iva = $nueva_existencia_actual * $valor_unitario;

            //calculo iva total
            $nuevo_iva_total = $nueva_existencia_actual * $iva_unitario;

            //nuevo valor total 
            $nuevo_valor_total = $nuevo_valor_total_sin_iva + $nuevo_iva_total;
            

            // Actualiza la existencia actual y el valor total en la base de datos
            $sqlUpdate = "UPDATE inventario SET existencia_actual = :nueva_existencia_actual, valor_total = :nuevo_valor_total WHERE id_inventario = :inventario_id";
            $consultaUpdate = $conexionBD->prepare($sqlUpdate);
            $consultaUpdate->bindParam(':nueva_existencia_actual', $nueva_existencia_actual, PDO::PARAM_INT);
            $consultaUpdate->bindParam(':nuevo_valor_total', $nuevo_valor_total, PDO::PARAM_STR);
            $consultaUpdate->bindParam(':inventario_id', $inventario_id, PDO::PARAM_INT);
            $consultaUpdate->execute();

            return true;
        } catch (Exception $e) {
            echo "Error al aumentar existencia: " . $e->getMessage();
            return false;
        }
    }



    //cambiar estado del inventario 
    public static function cambiarEstadoInventario($id, $nuevoEstado)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE inventario SET estado_inve = :estado_inve WHERE id_inventario = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':estado_inve', $nuevoEstado);
            $consulta->bindParam(':id', $id);



            if ($consulta->execute()) {
                return true; // Estado cambiado
            } else {
                // Imprimir información de error
                var_dump($consulta->errorInfo());
                return false; // No se pudo cambiar el estado
            }
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return false;
        }
    }

    //listar nro factura
    public static function listarNroFactura()
    {
        $conexionBD = self::crearInstancia();

        $sql = "SELECT nro_factura FROM factura";
        $consulta = $conexionBD->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
