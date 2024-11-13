<?php
// Inicia la sesión
session_start();

include_once '../models/usuario.php';

if (isset($_SESSION['usuario'])) {
    header('Location: ../views/inicio.php'); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = filter_var(strtolower($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'];

    $resultadoAutenticacion = Usuario::autenticarUsuario($correo, $contrasena);

    if ($resultadoAutenticacion === true) {
        header('Location: ../views/inicio.php');
        exit();
    } else {
        
        // Almacena el mensaje de error en una variable para ser utilizado en la vista
        $mensajeError = $resultadoAutenticacion;
    }
}

require '../views/login.php'; 

?>
