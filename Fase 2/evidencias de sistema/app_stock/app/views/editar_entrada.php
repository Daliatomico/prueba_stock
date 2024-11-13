<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: ../views/login.php'); // Redirigir a la página de inicio de sesión
    exit();
}

include('../models/inventario.php');
include_once '../models/producto.php';
include_once '../models/usuario.php';
include_once '../models/ubicacion.php';




// // Verificar si se ha pasado el id_inventario en la URL
// if (isset($_GET['id_inventario'])) {
//     // Filtrar el id de la entrada seleccionada
//     $id_inventario = filter_input(INPUT_GET, 'id_inventario', FILTER_SANITIZE_NUMBER_INT);

//     // Recuperar los detalles de la entrada de inventario, incluyendo los datos del producto
//     $inventario = Inventario::obtenerInventarioPorId($id_inventario);

//     // Verificar si se encontraron datos del inventario
//     if (!$inventario) {
//         echo "Error: No se encontraron los datos del inventario.";
//         exit();
//     }

//     // Si el inventario existe, asignar los datos a variables para mostrar en el formulario
//     $producto_id = $inventario['producto_id_producto'];
//     $nombre_producto = $inventario['nombre_producto'];
//     $valor_unitario = $inventario['valor_unitario'];
//     $codigo_producto = $inventario['cod_producto'];
//     $nombre_proveedor = $inventario['nombre_prove'];
//     $existencia_inicial = $inventario['existencia_inicial'];
// } else {
//     echo "Error: ID del inventario no especificado.";
//     exit();
// }



include('../../templates/header.php');


// Obtener productos con existencias bajas
$notificacionesExistencias = Inventario::verificarExistenciasBajas();


?>



<!--MAIN CONTENIDO-->
<main>
    <div class="p-2">

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


        <!--Contenido-->
        <h3 class="title fw-bold mt-3 mb-3 me-auto">Editar entrada</h3>
        <!-- formulario editar inventario-->
        <div class="container-fluid">
            <div class="mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-4">


                        <form class="row form-registro mx-auto" action="../controllers/inventarioController.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_inventario" value="<?php echo htmlspecialchars($id_inventario); ?>">

                            <div class="mb-3">
                                <label for="nombre_prove" class="form-label">Proveedor</label>
                                <input type="text" class="form-control" id="nombre_prove" name="nombre_prove" value="<?php echo htmlspecialchars($inventario['nombre_prove']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="cod_producto" class="form-label">Código</label>
                                <input type="text" class="form-control" id="cod_producto" name="cod_producto" value="<?php echo htmlspecialchars($inventario['cod_producto']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="nombre_producto" class="form-label">Producto</label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" value="<?php echo htmlspecialchars($inventario['nombre_producto']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="valor_unitario" class="form-label">Valor unitario</label>
                                <input type="text" class="form-control" id="valor_unitario" name="valor_unitario" value="$ <?= number_format($inventario['valor_unitario'], 0, ',', '.'); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="existencia_inicial" class="form-label">Editar cantidad</label>
                                <input type="number" class="form-control" id="existencia_inicial" name="existencia_inicial" value="<?php echo htmlspecialchars($existencia_inicial); ?>" required>
                            </div>


                            <input type="hidden" name="accion" value="editarEntrada">

                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-md btn-success">Guardar cambios</button>&nbsp;
                                <a href="vista_entradas.php" class="btn btn-md btn-secondary">Cerrar</a>
                            </div>
                        </form>



                    </div>
                </div>
            </div>
        </div>

        <!--fin del contenido-->
    </div>
</main>
<!--FIN MAIN CONTENIDO-->






<?php include('../../templates/footer.php'); ?>