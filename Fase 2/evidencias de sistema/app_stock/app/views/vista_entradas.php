<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: ../views/login.php'); // Redirigir a la página de inicio de sesión
    exit();
}

include_once('../models/inventario.php');
include('../../templates/header.php');

// parametros para la paginacion
$limit = 5; // ajusta el numero de inventarios por pagina
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // pagina actual
$offset = ($page - 1) * $limit; // indice desde donde se empieza a listar



$listaInventarios = Inventario::listarInventarios($limit, $offset);
$totalInventarios = Inventario::contarInventarios(); 
$totalPaginas = ceil($totalInventarios / $limit); 

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
        <!--Contenido-->
        <h3 class="title fw-bold mt-3 mb-0 me-auto">Entradas</h3>

        <!-- Botón agregar entrada -->
        <div class="row mt-4">
            <div class="col-auto d-grip gap-2 d-sm-block">
                <a href="entrada.php" class="btn btn-success mb-1" role="button">Registrar entrada</a>
            </div>
        </div>
        <div class="col-auto d-flex gap-2 align-items-center">
            <form action="../libs/factura.php" method="GET" class="d-flex">

                <select class="form-select  me-2 mb-2" name="nro_factura" id="nro_factura" style="width: 200px;">
                    <option value="" disabled selected> Nro factura</option>
                    <?php
                    $num_factura = Inventario::listarNroFactura();
                    foreach ($num_factura as $factura) {
                        echo "<option value='" . $factura['nro_factura'] . "'>" . $factura['nro_factura'] . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success mb-2 btn-sm">Generar factura</button>
            </form>
        </div>



        <!-- Filtros -->
        <form class="row g-3 align-items-end mt-3">
            <div class="col-md-3">
                <input type="text" id="filtroCodigo" class="form-control" placeholder="Filtrar por código producto">
            </div>
            <div class="col-md-3">
                <input type="text" id="filtroFactura" class="form-control" placeholder="Filtrar por nro factura">
            </div>
        </form>


        <!-- Tabla de entradas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <?php if (!empty($listaInventarios)): ?>
                        <table id="tabla-inventario" class="table-container table table-bordered">
                            <thead class="table table-stripped">
                                <tr class="highlight">
                                    <th>Nro. factura</th>
                                    <th>Código prod</th>
                                    <th>Proveedor</th>
                                    <th>Cantidad</th>
                                    <th>Valor unitario</th>
                                    <th>Fecha entrada</th>
                                    <th>Responsable</th>
                                    <th>Acción</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaInventarios as $inventario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inventario['nro_factura']); ?></td>
                                        <td><?php echo htmlspecialchars($inventario['cod_producto']); ?></td>
                                        <td><?php echo htmlspecialchars($inventario['nombre_prove']); ?></td>
                                        <td>
                                            <?php
                                            // Concatenar existencia inicial con unidad de medida
                                            $existencia_inicial_concatenada = htmlspecialchars($inventario['existencia_inicial']);
                                            echo $existencia_inicial_concatenada;
                                            ?>
                                        </td>
                                        <td> $<?= number_format($inventario['valor_unitario'], 0, ',', '.'); ?></td>

                                        <td><?php echo htmlspecialchars($inventario['fecha']); ?></td>


                                        <td><?php echo htmlspecialchars($inventario['registrado_por']); ?></td>


                                        <td>
                                            <form action="../controllers/inventarioController.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="id_inventario" value="<?php echo $inventario['id_inventario']; ?>">
                                                <button type="submit" name="accion" value="borrar" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Estás seguro de que deseas cancelar esta entrada?');">
                                                    <i class="ri-close-circle-line"></i>
                                                </button>
                                            </form>
                                        </td>


                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- paginacion inventario -->
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
                    <?php else : ?>
                        <p>No hay inventarios para mostrar.</p>
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