<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockControl</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!--grafico--> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- CSS - BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/app_stock/public/css/estilos.css">
</head>

<body>


    <!--Sidebar -->
    <div class="sidebar position-fixed top-0 bottom-0 border-end">
        <div class="d-flex align-items-center p-3">
            <a href="inicio.php" class="sidebar-logo text-uppercase fw-bold  fs-4 d-flex ">
                <img src="/app_stock/public/img/logo_trs.png" alt="Logo" class="logo-img me-2" />

            </a>
            <i class="sidebar-toggle ri-arrow-left-circle-line ms-auto fs-5 d-none d-md-block"></i>
        </div>

        <ul class="sidebar-menu p-3 m-0 mb-0">
            <li class="sidebar-menu-item active">
                <a href="inicio.php">
                    <i class="ri-dashboard-line sidebar-menu-item-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="sidebar-menu-divider mt-3 mb-1 text-uppercase">Menu principal</li>
            <li class="sidebar-menu-item has-dropdown">
                <a href="#">
                    <i class="ri-box-3-line sidebar-menu-item-icon"></i>
                    Gestión de productos
                    <i class="ri-arrow-down-s-line sidebar-menu-item-accordion ms-auto"></i>
                </a>
                <ul class="sidebar-dropdown-menu">
                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_categorias.php">
                            Categorías
                        </a>
                    </li>

                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_proveedores.php">
                            Proveedores
                        </a>
                    </li>

                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_productos.php">
                            Productos
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-menu-item has-dropdown">
                <a href="#">
                    <i class='bx bx-box sidebar-menu-item-icon'></i>
                    Gestión de inventario
                    <i class="ri-arrow-down-s-line sidebar-menu-item-accordion ms-auto"></i>
                </a>
                <ul class="sidebar-dropdown-menu">
                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_inventario.php">
                            Inventario
                        </a>
                    </li>
                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_entradas.php">
                            Entradas
                        </a>
                    </li>
                    <li class="sidebar-dropdown-menu-item">
                        <a href="vista_salidas.php">
                            Salidas
                        </a>
                    </li>

                    <li class="sidebar-dropdown-menu-item">
                        <a href="historial_movimientos.php">
                            Historial de movimientos
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-menu-item">
                <a href="vista_reportes.php">
                    <i class='bx bxs-report sidebar-menu-item-icon'></i>
                    Reportes
                </a>
            </li>


            <!-- Mostrar el menú de Usuarios solo si el rol es 'administrador' -->
            <?php if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'Administrador'): ?>
            <li class="sidebar-menu-item">
                <a href="vista_usuarios.php">
                    <i class="ri-user-fill sidebar-menu-item-icon"></i>
                    Usuarios
                </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-menu-item">
                <a href="vista_clientes.php">
                    <i class="ri-group-3-fill sidebar-menu-item-icon"></i>
                    Clientes
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-overlay"></div>
    <!-- Fin Sidebar -->