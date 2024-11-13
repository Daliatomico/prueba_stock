<?php
session_start();

// Verificar si hay una sesión activa y si el rol es "Administrador"
if (!isset($_SESSION['usuario']['rol']) || $_SESSION['usuario']['rol'] !== 'Administrador') {
    header('Location: ../views/login.php'); // Redirigir a la página de inicio de sesión
    exit();
}


include('../../templates/header.php');
include_once '../models/producto.php'; // Ajusta la ruta según sea necesario
include_once '../models/ubicacion.php';
include('../models/inventario.php');


// Obtener productos con existencias bajas
$notificacionesExistencias = Inventario::verificarExistenciasBajas();



?>

<!-- MAIN CONTENIDO -->
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

        <!-- Contenido -->
        <h3 class="title fw-bold mt-3 mb-3 me-auto">Registro de productos</h3>
        <!-- Formulario de registro de producto -->
        <div class="container-fluid">
            <div class="mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-4">
                        <form class="row form-registro mx-auto" action="../controllers/productoController.php" method="POST" enctype="multipart/form-data">



                            <!-- Categoría -->
                            <div class="mb-3">
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="" disabled selected>Seleccionar Categoría</option>
                                    <?php
                                    // Llama a la función que contiene lista de categorías desde el modelo
                                    $categorias = Producto::listarCategorias();
                                    foreach ($categorias as $categoria) {
                                        // Asegurarse de que no se impriman etiquetas HTML
                                        $nombreCategoriaLimpio = strip_tags($categoria['nombre_categoria']);
                                        echo '<option value="' . htmlspecialchars($categoria['id_categoria']) . '">' . htmlspecialchars($nombreCategoriaLimpio) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Proveedor -->
                            <div class="mb-3">
                                <!-- <label for="proveedor_id" class="form-label">Proveedor</label> -->
                                <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                                    <option value="" disabled selected>Seleccionar Proveedor</option>
                                    <?php
                                    // Llama a la función que contiene lista proveedores desde el modelo
                                    $proveedores = Producto::listarProveedores();
                                    foreach ($proveedores as $proveedor) {
                                        // Asegurarse de que no se impriman etiquetas HTML
                                        $nombreProveedorLimpio = strip_tags($proveedor['nombre_prove']);
                                        echo '<option value="' . htmlspecialchars($proveedor['id_proveedor']) . '">' . htmlspecialchars($nombreProveedorLimpio) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- ubicacion -->
                            <div class="mb-3">
                                <!-- <label for="ubicacion_id" class="form-label">Ubicación</label> -->
                                <select class="form-select" id="ubicacion_id" name="ubicacion_id" required>
                                    <option value="" disabled selected>Seleccionar Ubicación</option>
                                    <?php
                                    // Llama a la función que contiene lista los ubicaciones desde el modelo
                                    $ubicaciones = Ubicacion::listarUbicaciones();
                                    foreach ($ubicaciones as $ubicacion) {
                                        // Asegurarse de que no se impriman etiquetas HTML
                                        $nombreUbicacionLimpio = strip_tags($ubicacion['nombre_zona']);
                                        echo '<option value="' . htmlspecialchars($ubicacion['id_ubicacion']) . '">' . htmlspecialchars($nombreUbicacionLimpio) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <!--unidad de medida del producto-->
                            <div class="mb-3">
                                <select class="form-select" name="unidad_medida" id="unidad_medida" required>
                                    <option value="" disabled selected>Seleccionar unidad de medida</option>
                                    <option value="Caj.">Caj.</option>
                                    <option value="Und.">Und.</option>
                                </select>
                            </div>

            
                            <!-- Nombre del Producto -->
                            <div class="mb-3">
                                <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                            </div>
                            

                            <!--valor del producto-->
                            <div class="mb-3">
                                    <label for="valor_unitario" class="form-label">Valor unitario</label>
                                    <input type="text" class="form-control" id="valor_unitario" name="valor_unitario" required>
                            </div>


                        
                            <!-- Botones de acción -->
                            <input type="hidden" name="accion" value="agregar">

                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-md btn-success">Ingresar</button>&nbsp;
                                <a href="vista_productos.php" class="btn btn-md btn-success">Cerrar</a>
                            </div>



                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- FIN MAIN CONTENIDO -->


<?php include('../../templates/footer.php'); ?>