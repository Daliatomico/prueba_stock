<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: ../views/login.php'); // Redirigir a la página de inicio de sesión
    exit();
}

include('../../templates/header.php');
include('../models/inventario.php');
include('../models/movimiento.php');


// parametros para la paginacion
$limit = 5; // ajusta el numero de categorias por pagina
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // pagina actual
$offset = ($page - 1) * $limit; // indice desde donde se empieza a listar

// Obtener la lista de categorías y el total de categorías
$listarMovimientos = Movimiento::listarMovimientos($limit, $offset);
$totalMovimientos = Movimiento::contarMovimientos();
$totalPaginas = ceil($totalMovimientos / $limit); // calular el numero de paginas



// Obtener productos con existencias bajas
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

        <!--contenid-->
        <h3 class="title fw-bold mt-3 mb-0 me-auto">Histórico de movimientos</h3>
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <?php if (!empty($listarMovimientos)): ?>
                        <table class="table-container table table-bordered">
                            <thead class="table table-stripped">
                                <tr class="highlight">
                                    <th>Tipo movimiento</th>
                                    <th>Código producto</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Fecha movimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listarMovimientos as $movimiento): ?>
                                    <tr>
                                        <td><?php echo $movimiento['movimiento']; ?></td>
                                        <td><?php echo $movimiento['cod_producto']; ?></td>
                                        <td><?php echo $movimiento['nombre_producto']; ?></td>
                                        <td>
                                            <?php
                                            if ($movimiento['movimiento'] === 'Entrada') {
                                                echo $movimiento['existencia_inicial'];
                                            } elseif ($movimiento['movimiento'] === 'Salida') {
                                                echo $movimiento['cantidad_salida'];
                                            } 
                                            ?>
                                        </td>

                                        <td><?php echo $movimiento['fecha_movimiento']; ?></td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- poginacion productos -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm">
                                <!-- boton retroceder -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link text-success" href="?page=<?php echo $page - 1; ?>" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- boton de las paginas -->
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?php if ($i == $page)  ?>">
                                        <a class="page-link <?php echo ($i == $page) ? 'bg-success text-white' : 'text-success'; ?>" href="?page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- boton pagina siguiente -->
                                <?php if ($page < $totalPaginas): ?>
                                    <li class="page-item">
                                        <a class="page-link text-success" href="?page=<?php echo $page + 1; ?>" aria-label="Siguiente">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>

                    <?php else: ?>
                        <p>No hay historial de movimientos para mostrar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!--fin del contenido-->
    </div>
</main>
<!-- Fin Main Contenido-->




<?php include('../../templates/footer.php');
?>