<!--para que las notifiaciones se marquen como leidas-->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Recuperar las notificaciones leidas del local storage
        const leidas = JSON.parse(localStorage.getItem('notificacionesLeidas')) || [];

        // marcar como leidas las notificaciones
        leidas.forEach(producto => {
            const notificationItem = document.querySelector(`.notification-item:contains('${producto}')`);
            if (notificationItem) {
                notificationItem.remove(); // Remover la notificación
            }
        });

        // actualizar el contador de notificaciones
        actualizarContadorNotificaciones();
    });

    function marcarComoLeida(elemento, producto) {
        // Remover el elemento de la lista de notificaciones
        elemento.remove();

        // Guardar la notificación como leída en localStorage
        const leidas = JSON.parse(localStorage.getItem('notificacionesLeidas')) || [];
        leidas.push(producto);
        localStorage.setItem('notificacionesLeidas', JSON.stringify(leidas));

        // Actualizar el contador de notificaciones no leídas
        actualizarContadorNotificaciones();

        // Comprobar si hay más notificaciones
        let notificationList = document.getElementById('notification-list');
        if (notificationList.children.length === 0) {
            // Si no hay notificaciones, mostrar el mensaje "No hay notificaciones"
            let noNotificationsMessage = document.createElement('p');
            noNotificationsMessage.classList.add('dropdown-menu-notification-item-text', 'mb-1');
            noNotificationsMessage.textContent = 'No hay notificaciones';
            notificationList.appendChild(noNotificationsMessage);
        }
    }

    function actualizarContadorNotificaciones() {
        let notificationCountElement = document.getElementById('notification-count');
        let currentCount = document.querySelectorAll('.notification-item').length;

        notificationCountElement.textContent = currentCount;

        // Si no hay notificaciones, ocultar el contador
        if (currentCount === 0) {
            notificationCountElement.style.display = 'none';
        }
    }
</script>


<!--grafico en el dashboard con niveles de inventario-->
<script>
    // obtener los datos del inventario
    async function obtenerNivelesInventario() {
        const response = await fetch('/app_stock/app/api/nivel_inventario.php');
        if (!response.ok) {
            throw new Error('Error al obtener los niveles de inventario');
        }
        return await response.json();
    }

    // crear el grafico
    async function crearGrafico() {
        const datosInventario = await obtenerNivelesInventario();

        // Si no hay datos, se muestran vacios
        const productos = datosInventario.length > 0 ? datosInventario.map(item => item.nombre_producto) : [];
        const existencias = datosInventario.length > 0 ? datosInventario.map(item => item.existencia_actual) : [];



        const ctx = document.getElementById('nivelesInventarioChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productos.length > 0 ? productos : ['No hay productos con inventario reducido.'],
                datasets: [{
                    label: 'Existencia Actual',
                    data: existencias.length > 0 ? existencias : [0],
                    backgroundColor: 'rgb(113, 198, 100)',
                    borderColor: 'rgb(113, 198, 100)',
                    borderWidth: 1,
                    barThickness: 30
                }]
            },

            options: {
                responsive: true,

                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 1, //para que el eje Y comienze desde el 1
                        ticks: {
                            stepSize: 1, //para que solo se muestren  numeros enteros
                            callback: function (value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }


                    }
                }
            }
        });
    }

    // Llama a la funcion para crear el gráfico cuando la página se haya cargado
    document.addEventListener('DOMContentLoaded', crearGrafico);
</script>



<!--filtros para las tablas-->
<script>
    //filtro por nombre producto
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroProducto").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtrar por factura
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroFactura").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtrar por guia traslado
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroGuia").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtrar por codigo producto
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroCodigo").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtro por categoria
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroCategoria").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtro por proveedor
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroProveedor").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtro por cliente
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroCliente").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });

    //filtro por nombre usuario
    $(document).ready(function () {
        // Filtro de búsqueda
        $("#filtroNombre").on("keyup", function () {
            var valorBusqueda = $(this).val().toLowerCase();
            $("#tabla-inventario tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(valorBusqueda) > -1);
            });
        });
    });
</script>


<!--rellnar formulario entradas-->
<script>
    document.getElementById('producto_id').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        // Rellenar los campos con los valores de los atributos 'data-' del producto seleccionado
        document.getElementById('nombre_prove').value = selectedOption.getAttribute('data-proveedor');
        document.getElementById('cod_producto').value = selectedOption.getAttribute('data-codigo');
        document.getElementById('nombre_producto').value = selectedOption.getAttribute('data-nombre');
        document.getElementById('valor_unitario').value = selectedOption.getAttribute('data-valor');
        document.getElementById('cantidad').value = selectedOption.getAttribute('data-cantidad');
        document.getElementById('ubicacion').value = selectedOption.getAttribute('data-ubicacion');
    });
</script>

<!--rellenar formulario salida-->
<script>
    document.getElementById('salida_id').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        document.getElementById('cod_producto').value = selectedOption.getAttribute('data-codigo');
        document.getElementById('nombre_producto').value = selectedOption.getAttribute('data-nombre');
        document.getElementById('valor_unitario').value = selectedOption.getAttribute('data-valor');
        document.getElementById('existencia_actual').value = selectedOption.getAttribute('data-existencia');

    })
</script>

<!--rellenar cliente en formulario salida-->
<script>
    document.getElementById('cliente_id').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        document.getElementById('direccion').value = selectedOption.getAttribute('data-direccion');
    })
</script>




<!--refrescar lista de movimientos recientes-->
<script>
    // Función para cargar los movimientos recientes dinámicamente
    function cargarMovimientosRecientes() {
        // Realizamos la petición AJAX al servidor
        fetch('/app_stock/app/api/movimientos_recientes.php')
            .then(response => response.json())
            .then(data => {
                const lista = document.getElementById('activity-list');
                lista.innerHTML = ''; // Limpiar la lista antes de agregar los nuevos movimientos

                data.forEach(movimiento => {
                    // Crear un nuevo <li> por cada movimiento
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span class="activity-description">
                            Se realizó una ${movimiento.movimiento} de ${movimiento.nombre_producto}
                        </span>
                        <span class="activity-time">${new Date(movimiento.fecha_movimiento).toLocaleString()}</span>
                    `;
                    lista.appendChild(li);
                });
            })
            .catch(error => console.error('Error al cargar los movimientos:', error));
    }

    // Configurar intervalo de actualización (por ejemplo, cada 10 segundos)
    setInterval(cargarMovimientosRecientes, 10000); // 10000 ms = 10 segundos

    // Cargar movimientos cuando el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', cargarMovimientosRecientes);
</script>



<!--ojo mostrar contraseña-->
<script>
    // Función para alternar la visibilidad de la contraseña
    function togglePasswordVisibility(inputId, toggleButtonId) {
        const passwordField = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleButtonId);

        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleButton.innerHTML = '<i class="ri-eye-line"></i>'; // Cambiar el icono a 'ojo abierto'
        } else {
            passwordField.type = "password";
            toggleButton.innerHTML = '<i class="ri-eye-line"></i>'; // Cambiar el icono a 'ojo cerrado'
        }
    }

    // Agregar eventos a los botones de mostrar/ocultar
    document.getElementById("togglePassword1").addEventListener("click", function () {
        togglePasswordVisibility("contrasena", "togglePassword1");
    });
    document.getElementById("togglePassword2").addEventListener("click", function () {
        togglePasswordVisibility("contrasena_nueva", "togglePassword2");
    });
    document.getElementById("togglePassword3").addEventListener("click", function () {
        togglePasswordVisibility("confirmar_contrasena", "togglePassword3");
    });
</script>


<!--funcion para calcular iva y mostrar en el campo de la entrada-->
<script>
    document.getElementById('producto_id').addEventListener('change',function(){
        //obtengo el valor unitario del producto
        var producto = this.options[this.selectedIndex];
        var valorUnitario = parseFloat(producto.getAttribute('data-valor').replace(/[^\d.-]/g, ''));

        //calculo del iva
        var iva = valorUnitario * 0.19 ;

        //formato iva
        var ivaChileno = "$" + iva.toFixed(3).replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        //me muestra el valor del iva en el campo que esta en la entrada
        document.getElementById('iva').value = ivaChileno;
       

    })
</script>






<script src="/app_stock/public/js/jquery.min.js"></script>
<script src="/app_stock/public/js/bootstrap.bundle.min.js"></script>
<script src="/app_stock/public/js/script.js"></script>

</body>

</html>