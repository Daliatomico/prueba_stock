<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockControl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>

    <!--no mover estos estilos de aqui-->
    <style>
        * {
            margin: 0;
            padding: 0;
            border: none;
            outline: none;
            box-sizing: border-box;
            background-color: #0e2238;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }

        body {
            display: contents;

        }


        .comienzo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }

        /* Estilos para el logo */
        .comienzo .logo-img {
            max-width: 100%;
            /* Asegura que la imagen no exceda el ancho del contenedor */
            height: auto;
            /* Mantiene la proporción de la imagen */
            margin-bottom: 20px;
            /* Añade espacio debajo del logo */
        }

        /* Estilos para el botón de acceso */
        .comienzo .btn-iniciar {
            display: inline-block;
            padding: 15px 30px;
            /* Añade padding interno al botón */
            font-size: 16px;
            /* Ajusta el tamaño de la fuente */
            color: white;
            /* Color del texto del botón */
            background-color: #28a745;
            /* Color de fondo del botón (verde) */
            border: none;
            /* Elimina el borde del botón */
            border-radius: 5px;
            /* Añade bordes redondeados */
            text-decoration: none;
            /* Elimina el subrayado del enlace */
            transition: background-color 0.3s ease;
            /* Añade una transición suave para el cambio de color */
        }

        .comienzo .btn-iniciar:hover {
            background-color: #218838;
            /* Color de fondo del botón al pasar el ratón (verde oscuro) */
        }

        /* Ajustes para pantallas pequeñas */
        @media (max-width: 768px) {
            .comienzo {
                padding: 10px;
                /* Reduce el padding en pantallas pequeñas */
            }

            .comienzo .btn-iniciar {
                padding: 12px 25px;
                /* Ajusta el padding interno del botón en pantallas pequeñas */
                font-size: 14px;
                /* Reduce el tamaño de la fuente en pantallas pequeñas */
            }
        }
    </style>



    <div class="container-fluid">
        <div class="row justify-content-center align-items-center text-center">
            <div class="col-12 col-md-6 comienzo">
                <img src="public/img/logo_trs.png" alt="logo" class="img-fluid">
                <br>
                <a href="app/views/login.php" class="btn btn-iniciar mt-3">Acceder</a>
            </div>
        </div>
   


</body>

</html>