<?php


class BD {

    //variable que almacena la instancia de conexion a la bd
    private static $instancia = null;

    // metodo para crear y devolver una instancia de conexion a la bd
    public static function crearInstancia() {
        //verifica si se ha creado una instancia de conexion
        if (!isset(self::$instancia)) {
            try {
                //configuracion de opciones para la conexion PDO
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // modo de error: lanza excepciones en caso de error
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
                    PDO::ATTR_EMULATE_PREPARES => false, 
                ];
                
                //crea nueva instancia de PDO para conectarse a la bd
                self::$instancia = new PDO('mysql:host=localhost;dbname=stock_control', 'root', '', $opciones);
            } catch (PDOException $e) {
                //si ocurre un error al conectar, se muestra un msje de error y se termina la ejecucion
                echo "Error de conexión: " . $e->getMessage();
                exit; 
            }
        }
        //devuelve la instancia de conexion a la base de datos
        return self::$instancia;
    }
}

?>
