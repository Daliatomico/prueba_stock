<?php
session_start();




// Verifica si el usuario esta logeado y la informacion en la sesion
if (!isset($_SESSION['usuario'])) {
    //redirige al login si el usuario no esta logeado
    header('Location: ../views/login.php'); // Redirigir a la página de inicio de sesión
    exit();
}



include('../models/usuario.php');
include('../models/inventario.php');
include('../../templates/header.php');

// Obtiene los datos del usuario logeado
$usuarioLogeado = Usuario::obtenerUsuarioLogeado();


// notificar existencia baja de un producto en el inventario
$notificacionesExistencias = Inventario::verificarExistenciasBajas();

?>








<!-- Main Contenido -->
<main>
    <div class="p-2">
        <!-- Navbar -->
        <nav class="px-3 py-2 bg-white rounded shadow d-flex justify-content-between align-items-center">
            <!-- Botón menú para pantallas pequeñas -->
            <i class="ri-menu-line sidebar-toggle me-3 d-block d-md-none text-dark"></i>

            <!-- matener vacio -->
            <div class="d-none d-md-flex flex-grow-1 me-3">

            </div>

            <!-- Contenedor de notificaciones y usuario -->
            <div class="d-flex align-items-center">
                <!-- Notificaciones -->
                <div class="dropdown me-2">
                    <div class="cursor-pointer dropdown-toggle navbar-link" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-notification-line"></i>
                        <!-- Mostrar el número de notificaciones -->
                        <span id="notification-count" class="topbar-button-total topbar-button-total-notification">
                            <?php echo count($notificacionesExistencias); ?>
                        </span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end fx-dropdown-menu">
                        <div id="notification-list" class="list-group list-group-flush">
                            <?php if (count($notificacionesExistencias) > 0): ?>
                                <?php
                                // Agrupar notificaciones por producto
                                $notificacionesAgrupadas = [];
                                foreach ($notificacionesExistencias as $notificacion) {
                                    $nombre = $notificacion['nombre_producto'];
                                    $existencia = $notificacion['existencia_actual'];

                                    if (!isset($notificacionesAgrupadas[$nombre])) {
                                        $notificacionesAgrupadas[$nombre] = 0;
                                    }
                                    $notificacionesAgrupadas[$nombre] += $existencia;
                                }
                                ?>
                                <?php foreach ($notificacionesAgrupadas as $producto => $cantidad): ?>
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center notification-item" onclick="marcarComoLeida(this, '<?php echo $producto; ?>')">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center">
                                                <span class="dropdown-menu-notification-item-icon danger me-2">
                                                    <i class="ri-alert-line"></i>
                                                </span>
                                                <p class="dropdown-menu-notification-item-time mb-0">
                                                    ¡Crítico! <?php echo $producto; ?> tiene solo <?php echo $cantidad; ?> unidades.
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="dropdown-menu-notification-item-text mb-1">No hay notificaciones</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Fin notificaciones -->

                <!-- Usuario -->
                <div class="dropdown">
                    <div class="d-flex align-items-center cursor-pointer dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <span class="me-2 d-none d-sm-block"> <?php echo $_SESSION['usuario']['nombre']; ?> </span>
                        <img class="navbar-profile-image"
                            src="../../public/img/usuario.png"
                            alt="Image">
                    </div>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li class="nom-user dropdown-item"> <?php echo $_SESSION['usuario']['nombre']; ?> </li>
                        <li><a class="dropdown-item" href="vista_perfil.php">Perfil</a></li>
                        <li>
                            <form action="../controllers/logoutController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="accion" value="logout">
                                <button type="submit" class="dropdown-item" style="border: none; background: none; cursor: pointer;">
                                    Cerrar sesión
                                </button>
                            </form>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>

        <!-- Fin Navbar -->
        <!--Contenido-->
        <h3 class="title fw-bold mt-3 mb-0 me-auto">Perfil de usuario</h3>
        <!-- formulario cambio de contraseña -->
        <div class="container-fluid">
            <div class="mb-3">
                <div class="row justify-content-center mt-3">

                    <!--informacion del usuario-->
                    <div class="col-md-4">
                        <h5 class="fw-bold text-center  my-3">Información del usuario</h5>
                        <br>

                        <?php if ($usuarioLogeado): ?>
                            <form class="row form-registro mx-auto" action="">
                                <div class="mb-3">
                                    <label for="nombreCompleto" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" value="<?php echo $usuarioLogeado['nombre'] . ' ' . $usuarioLogeado['apellido']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="correo" class="form-label">Correo</label>
                                    <input type="text" class="form-control" value="<?php echo $usuarioLogeado['correo']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="rol" class="form-label">Rol</label>
                                    <input type="text" class="form-control" value="<?php echo $usuarioLogeado['rol']; ?>" readonly>
                                </div>

                            </form>
                        <?php else:  ?>
                            <p>No se pudo cargar la información del usuario.</p>
                        <?php endif; ?>
                    </div>


                    <!--formulario cambio de contraseña-->
                    <div class=" col-md-4">
                        <h5 class="fw-bold text-center  my-3">Cambio de Contraseña</h5>
                        <br>

                        <!-- Cambiar contraseña - formulario -->
                        <form class="row form-registro mx-auto" action="../controllers/usuarioController.php" method="POST">
                            <input type="hidden" name="accion" value="cambiar_contrasena">

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña actual</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                    <span class="input-group-text" id="togglePassword1" style="cursor: pointer;">
                                        <i class="ri-eye-line"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena_nueva" class="form-label">Nueva contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena_nueva" name="contrasena_nueva" required>
                                    <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                                        <i class="ri-eye-line"></i>
                                    </span>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar nueva contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                    <span class="input-group-text" id="togglePassword3" style="cursor: pointer;">
                                        <i class="ri-eye-line"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-md btn-success">Guardar</button>
                                <a href="inicio.php" class="btn btn-md btn-success">Cerrar</a>
                            </div>
                        </form>



                    </div>

                </div>
            </div>
            <!--fin del contenido-->

        </div>

</main>
<!-- Fin Main Contenido-->






<?php include('../../templates/footer.php'); ?>