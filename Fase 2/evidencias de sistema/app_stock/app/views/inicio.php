<?php
session_start();
// // Verificar si hay una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: ../views/login.php');
    exit();
}

include('../../templates/header.php');
include('../models/producto.php');
include('../models/inventario.php');
include('../models/movimiento.php');

$totalContar = Producto::contarProductos();
$total_valorizacion = Inventario::calcularValorizacion();
$totalInventarios = Inventario::contarExistencias();
$movimientos = Movimiento::listarMovimientosCard();


$notificacionesExistencias = inventario::verificarExistenciasBajas();

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
                                <?php foreach ($notificacionesAgrupadas as $producto => $existencia_actual): ?>
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center notification-item" onclick="marcarComoLeida(this, '<?php echo $producto; ?>')">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center">
                                                <span class="dropdown-menu-notification-item-icon danger me-2">
                                                    <i class="ri-alert-line"></i>
                                                </span>
                                                <p class="dropdown-menu-notification-item-time mb-0">
                                                    ¡Crítico! <?php echo $producto; ?> tiene solo <?php echo $existencia_actual; ?> unidades.
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
        <h3 class="title fw-bold mt-3 mb-0 me-auto">Dashboard</h3>
        <div class="info-data">

            <div class="card">
                <div class="head">

                    <div>
                        <h2>
                            <?php echo htmlspecialchars($totalContar); ?>
                        </h2>
                        <p>Productos</p>
                    </div>
                    <i class='bx bxs-package bx-sm'></i>
                </div>

            </div>
            <div class="card">
                <div class="head">
                    <div>
                        <h2>
                            <?php echo htmlspecialchars($totalInventarios); ?>
                        </h2>
                        <p>Existencia total</p>
                    </div>
                    <i class='bx bx-box bx-sm '></i>

                </div>

            </div>
            <div class="card">
                <div class="head">
                    <div>
                        <h2>
                            $ <?php echo number_format(htmlspecialchars($total_valorizacion), 0, ',', '.'); ?>
                        </h2>
                        <p>Valorización total</p>
                    </div>
                    <i class='bx bx-dollar bx-sm'></i>
                </div>
            </div>

        </div>

        <div class="data">
            <div class="content-data" style="width:100%">
                <div class=" head">
                    <h5>Nivel de inventario bajo</h5>
                </div>

                <canvas id="nivelesInventarioChart"></canvas>

            </div>



            <div class="content-data">
                <div class="activity-container">
                    <div class="head">
                        <h5>Movimientos recientes</h5>
                    </div>

                    <ul class="activity-list" id="activity-list">
                        <!-- Los movimientos recientes se cargarán aquí -->
                        <?php

                        foreach ($movimientos as $movimiento): ?>
                            <li>
                                <span class="activity-description">
                                    Se realizó una
                                    <?php echo htmlspecialchars($movimiento['movimiento']) . ' de ' . htmlspecialchars($movimiento['nombre_producto']); ?>
                                </span>
                                <span class="activity-time">
                                    <?php echo date("d-m-Y", strtotime($movimiento['fecha_movimiento'])); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Botón Ver Más -->
                    <div class="d-flex justify-content-end">
                        <a href="historial_movimientos.php" class="btn btn-success btn-sm">
                            Ver más
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!--fin del contenido-->
    </div>
</main>
<!-- Fin Main Contenido-->


<?php include('../../templates/footer.php'); ?>