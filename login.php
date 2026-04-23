<?php
//recordar la variable de sesion
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CDN Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icoons de Boostrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Favicon (Icono de la pagina web)-->
    <link rel="shortcut icon" href="./assets/img/shop.svg" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!-- Tipografia Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Averia+Libre:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Glory:ital,wght@0,100..800;1,100..800&family=Macondo&family=Marcellus&display=swap" rel="stylesheet">
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">

    <style>
        main {
            flex: 1;
        }

        #login {
            margin: 0px 300px 0px 300px;
        }

        #usuario,
        #contraseña {
            /*font-family: "Glory", sans-serif;*/
            font-size: 20px;
        }

        .boton-isesion {
            background-color: #efac41;
            border: none;
            /*font-family: "Macondo", cursive;*/
            color: white;
        }

        .boton-isesion:hover {
            background-color: #de8531;
        }

        .boton-isesion:focus,
        .boton-isesion:active {
            outline: none;
            box-shadow: none;
        }
    </style>
    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="navbar">
        <a href="login.php">
            <img src="./assets/img/logo.png" width="130px" alt="130px" />
        </a>
        <center>
            <h2 class="text-black" id="nombrepag">Tiendas Africam Safari</h2>
        </center>
    </header>

    <main>
        <div class="container px-1 px-sm-5 mx-auto" id="login">

            <form class="form-inline" action="validar.php" method="post">

                <section class="">
                    <div class="container py-5 h-100 fondo">
                        <div class="row d-flex justify-content-center align-items-center h-100">
                            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                                <div class="card shadow-2-strong" style="border-radius: 1rem;">
                                    <div class="card-body p-5 text-center">
                                        <h3 class="text-center">
                                            <img src="assets/img/person-circle.svg" width="35%" alt="" />
                                        </h3>

                                        <br><br>

                                        <div class="mb-3">
                                            <b><label for="" class="form-label" id="usuario">Usuario</label></b>
                                            <input type="text" class="form-control" name="usuario" required>
                                        </div>


                                        <div class="mb-3">
                                            <b><label for="" class="form-label" id="contraseña">Contraseña</label></b>
                                            <input type="password" class="form-control" name="contraseña" required>
                                        </div>


                                        <br>
                                        <button type="submit" name="iniciarsesion" class="btn boton-isesion">Iniciar sesión</button>
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                </section>

            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
    </footer>

    <!-- CDN Boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

</body>

</html>