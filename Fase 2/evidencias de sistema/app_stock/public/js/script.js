$(document).ready(function () {

    // Sidebar
    $('.sidebar-dropdown-menu').slideUp('fast')

    $('.sidebar-menu-item.has-dropdown > a, .sidebar-dropdown-menu-item.has-dropdown > a').click(function (e) {
        e.preventDefault()

        if (!($(this).parent().hasClass('focused'))) {
            $(this).parent().parent().find('.sidebar-dropdown-menu').slideUp('fast')
            $(this).parent().parent().find('.has-dropdown').removeClass('focused')
        }

        $(this).next().slideToggle('fast')
        $(this).parent().toggleClass('focused')
    })

    $('.sidebar-toggle').click(function () {
        $('.sidebar').toggleClass('collapsed')

        $('.sidebar.collapsed').mouseleave(function () {
            $('.sidebar-dropdown-menu').slideUp('fast')
            $('.sidebar-menu-item.has-dropdown, .sidebar-dropdown-menu-item.has-dropdown').removeClass('focused')
        })
    })

    $('.sidebar-overlay').click(function () {
        $('.sidebar').addClass('collapsed')

        $('.sidebar-dropdown-menu').slideUp('fast')
        $('.sidebar-menu-item.has-dropdown, .sidebar-dropdown-menu-item.has-dropdown').removeClass('focused')
    })

    if (window.innerWidth < 768) {
        $('.sidebar').addClass('collapsed')
    }
    // fin Sidebar


    // control logo sidebar
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarToggle = document.querySelector(".sidebar-toggle");
        const sidebar = document.getElementById("sidebar");

        sidebarToggle.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed"); // Cambia a 'collapsed' cuando el sidebar está colapsado
        });

        // Mostrar el logo al pasar el mouse sobre el sidebar
        sidebar.addEventListener("mouseenter", function () {
            sidebar.classList.remove("collapsed"); // Elimina la clase 'collapsed' al pasar el mouse
        });

        // Ocultar el logo al salir el mouse del sidebar
        sidebar.addEventListener("mouseleave", function () {
            if (!sidebar.classList.contains("collapsed")) {
                sidebar.classList.add("collapsed"); // Agrega la clase 'collapsed' si el sidebar no está colapsado
            }
        });
    });
    //fin control sidebar

})

