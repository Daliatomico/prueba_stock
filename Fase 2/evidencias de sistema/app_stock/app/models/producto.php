<?php
include_once '../config/bd.php';

class Producto
{
    public $id;
    public $codigo;
    public $nombre_producto;
    public $unidad_medida;
    public $valor;
    public $estado;
    public $fecha_registro_prod;
    public $proveedor_id;
    public $categoria_id;
    public $ubicacion_id;

    public static function crearInstancia()
    {
        return BD::crearInstancia();
    }

    // listar los productos
    public static function listarProductos($limit, $offset)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT producto.*, 
                        proveedor.nombre_prove, 
                        categoria.nombre_categoria, 
                        ubicacion.nombre_zona 
                        FROM producto
                        LEFT JOIN proveedor ON producto.proveedor_id_proveedor = proveedor.id_proveedor
                        LEFT JOIN categoria ON producto.categoria_id_categoria = categoria.id_categoria
                        LEFT JOIN ubicacion ON producto.ubicacion_id_ubicacion = ubicacion.id_ubicacion
                        LIMIT :limit OFFSET :offset ";

            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':limit', $limit, PDO::PARAM_INT);
            $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
            $consulta->execute();
            
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error al listar productos: " . $e->getMessage();
            return [];
        }
    }


    //listrar productos para entrada
    public static function listarProdEntrada()
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT producto.*, 
                        proveedor.nombre_prove, 
                        categoria.nombre_categoria, 
                        ubicacion.nombre_zona 
                        FROM producto
                        LEFT JOIN proveedor ON producto.proveedor_id_proveedor = proveedor.id_proveedor
                        LEFT JOIN categoria ON producto.categoria_id_categoria = categoria.id_categoria
                        LEFT JOIN ubicacion ON producto.ubicacion_id_ubicacion = ubicacion.id_ubicacion
                        ";

            $consulta = $conexionBD->prepare($sql);
            $consulta->execute();
            
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error al listar productos: " . $e->getMessage();
            return [];
        }
    }

    //listar productos con inventario para generar salida
    public static function listarProdConInventario()
    {
        $conexionBD = self::crearInstancia();

        $consulta = $conexionBD->prepare("
        SELECT DISTINCT inventario.id_inventario, 
                        producto.id_producto, 
                        producto.cod_producto, 
                        producto.nombre_producto, 
                        producto.valor_unitario, 
                        inventario.existencia_actual  -- Cambiado a existencia_actual
        FROM inventario
        INNER JOIN producto ON inventario.producto_id_producto = producto.id_producto
    ");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    //obtiene el listado de proveedores
    public static function listarProveedores()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_proveedor, nombre_prove FROM proveedor");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // obtiene el listado de categorias
    public static function listarCategorias()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_categoria, nombre_categoria FROM categoria");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // metodo para listar productos por su categoria
    public static function listarProductosPorCategoria($categoria_id)
    {
        $conexionBD = self::crearInstancia();

        $consulta = $conexionBD->prepare("SELECT * FROM producto WHERE categoria_id = :categoria_id AND estado ='Estado' ");
        $consulta->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchALL(PDO::FETCH_ASSOC);
    }

    // obtiene el listado de las ubicaciones
    public static function listarUbicaciones()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT id_ubicacion, nombre_zona FROM ubicacion");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }



    // metodo que cuenta el total de filas de la tabla producto para la paginacion
    public static function ProductosPaginacion()
    {
        $conexionBD = self::crearInstancia();
        $consulta = $conexionBD->prepare("SELECT COUNT(*) as total FROM producto");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }


    #metodo para agregar productos
    public static function agregarProducto($nombre_producto, $unidad_medida, $valor, $fecha_registro_prod, $proveedor_id, $categoria_id, $ubicacion_id)
    {
        try {
            $conexionBD = self::crearInstancia();

            
            //verifica el estado del proveedor Activo o Inactivo
            $consultaProveedor = $conexionBD->prepare("SELECT estado FROM proveedor WHERE id_proveedor = :proveedor_id");
            $consultaProveedor->bindParam(':proveedor_id', $proveedor_id,PDO::PARAM_INT);
            $consultaProveedor->execute();
            $proveedor = $consultaProveedor->fetch(PDO::FETCH_ASSOC);

            //si el proveedor no se encuenta en estado activo se lanza una excepcion
            if (!$proveedor || $proveedor['estado']!== 'Activo') {
                throw new Exception("El proveedor no esta activo, no se puede registrar el producto.");
            }


        

            //genera el codigo del producto
            $codigo = 'PD-' . substr(md5(uniqid()), 0, 5);
            //estado del producto por defecto
            $estado = 'Activo';

            $sql = "INSERT INTO producto (cod_producto,nombre_producto,unidad_medida, valor_unitario,estado, fecha_registro_prod,proveedor_id_proveedor, categoria_id_categoria, ubicacion_id_ubicacion)
                    VALUES (:cod_producto,:nombre_producto, :unidad_medida, :valor_unitario, :estado, :fecha_registro_prod, :proveedor_id, :categoria_id, :ubicacion_id)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':cod_producto', $codigo);
            $consulta->bindParam(':nombre_producto', $nombre_producto);
            $consulta->bindParam(':unidad_medida', $unidad_medida);
            $consulta->bindParam(':valor_unitario', $valor);
            $consulta->bindParam(':estado', $estado);
            $consulta->bindParam(':fecha_registro_prod', $fecha_registro_prod);
            $consulta->bindParam(':proveedor_id', $proveedor_id);
            $consulta->bindParam(':categoria_id', $categoria_id);
            $consulta->bindParam(':ubicacion_id', $ubicacion_id);
            $consulta->execute(); // Ejecutar la consulta
            return true; // Retornar true si se ejecuta exitosamente
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage(); // Mostrar error si ocurre
            return false; // Retornar false si hay un error
        }
    }

    #metodo para editar productos
    public static function editarProducto($id, $nombre_producto, $unidad_medida, $valor, $fecha_registro_prod,  $proveedor_id, $categoria_id, $ubicacion_id)
    {
        $conexionBD = self::crearInstancia();
        $sql = "UPDATE producto SET 
                -- cod_producto = :cod_producto,
                nombre_producto = :nombre_producto, 
                unidad_medida = :unidad_medida,
                valor_unitario = :valor_unitario,
                fecha_registro_prod = :fecha_registro_prod, 
                -- estado = :estado,
                proveedor_id_proveedor = :proveedor_id, 
                categoria_id_categoria = :categoria_id,  
                ubicacion_id_ubicacion = :ubicacion_id  
            WHERE id_producto = :id";
        $consulta = $conexionBD->prepare($sql);
        // $consulta->bindParam(':cod_producto', $codigo);
        $consulta->bindParam(':nombre_producto', $nombre_producto);
        $consulta->bindParam(':unidad_medida', $unidad_medida);
        $consulta->bindParam(':valor_unitario', $valor);
        $consulta->bindParam(':fecha_registro_prod', $fecha_registro_prod);
        // $consulta->bindParam(':estado', $estado);
        $consulta->bindParam(':proveedor_id', $proveedor_id);
        $consulta->bindParam(':categoria_id', $categoria_id);
        $consulta->bindParam(':ubicacion_id', $ubicacion_id);
        $consulta->bindParam(':id', $id);

        try {
            $consulta->execute(); // Ejecutar la consulta
            return true; // Retornar true si se ejecuta exitosamente
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage(); // Mostrar error si ocurre
            return false; // Retornar false si hay un error
        }
    }


    //metodo que obtiene los datos de los producto por su id para editar
    public static function obtenerProductoPorId($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "SELECT * FROM producto WHERE id_producto = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC); // Devuelve un array asociativo con los datos del producto
        } catch (PDOException $e) {
            echo "Error al obtener el producto: " . $e->getMessage();
            return false;
        }
    }



    #metodo para eliminar productos
    public static function borrarProducto($id)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "DELETE FROM producto WHERE id_producto = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':id', $id);
            $consulta->execute();

            return true;
        } catch (Exception $e) {
            echo "Error al borrar el producto: " . $e->getMessage();
            return false;
        }
    }


    //metodo  que cuenta el total de productos mediante una funcion en la bd
    public static function contarProductos()
    {
        try {
            $conexionBD = self::crearInstancia();

            //llama a la funcion 
            $consulta = $conexionBD->prepare("SELECT contar_total_productos() AS total");
            $consulta->execute();

            //obtiene el valor devuelto por la funcion
            $total = $consulta->fetch(PDO::FETCH_ASSOC)['total'];

            return $total; //retornar el total de productos
        } catch (PDOException $e) {
            echo "Error al contrar productos: " . $e->getMessage();
            return false;
        }
    }




    //metodo para cambiar el estado de un producto
    public static function cambiarEstadoProducto($id, $nuevoEstado)
    {
        try {
            $conexionBD = self::crearInstancia();
            $sql = "UPDATE producto SET estado = :estado WHERE id_producto = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(':estado', $nuevoEstado);
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
}
