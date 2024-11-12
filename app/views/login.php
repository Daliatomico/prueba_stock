<?php if (isset($mensajeError)): ?>
    <script>
        alert("<?php echo $mensajeError; ?>");
    </script>
<?php endif; ?>

<!doctype html>
<html lang="es">

<head>
    <title>StockControl</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>


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



        .btn-iniciar {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-iniciar:hover {
            background-color: #218838;
        }


        /* Estilos generales para el contenedor del formulario */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;


        }

        /* Estilos para el formulario */
        .form-login {
            color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }


        .form-login a {
            color: #28a745;

        }


        .form-login .btn-success {
            color: white;
            background-color: #28a745;
            border-color: #28a745;
            margin-top: 10px;

        }

        .form-login .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;

        }

        .form-login h3 {
            text-align: center;
            margin-bottom: 20px;

        }
    </style>


    <!--login usuarios-->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="login-container p-4 ">
                    <form method="POST" action="../controllers/loginController.php" class="form-login">
                        <h3 class="text-center mb-4">StockControl</h3>

                        <div class="form-group">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" required>
                        </div>
                        <!-- <input type="hidden" name="accion" value="login"> -->
                        <div class="col-auto d-grid gap-2 d-sm-block mt-3">
                            <button type="submit" class="btn btn-success btn-lg w-100">Iniciar sesión</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


</body>



</html>