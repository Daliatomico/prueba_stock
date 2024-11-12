<?php
// inicia sesion para acceder a las variables de sesion
session_start();

// verifica si hay una sesion activa
if (isset($_SESSION['usuario'])) {
    // destruir la sesion
    session_unset();  //elimina todas las variables de sesion para limpiar los datos almacenados
    session_destroy(); // destruye la sesion completamente

    // me redirige al login
    header('Location: ../views/login.php'); 
    exit(); 
} else {
    //si no hay sesion activa, envia a la inicio de sesion
    header('Location: ../views/login.php'); 
    exit(); 
}
?>
