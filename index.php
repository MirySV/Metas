<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();
$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
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
        .navbarindex {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0px 0px 10px 0px;
            padding: 55px 0px 40px 0px;
        }

        .navbarindex a {
            position: absolute;
            left: 20px;
        }

        #custom-cards {
            margin: 30px 15px 55px 75px;
        }

        #tarjetas{
            text-decoration:none;
        }

        .mi-card {
            background-color: #330a04;
        }

        font {
            font-family: "Macondo", cursive;
        }
    </style>
    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="navbarindex">
        <a href="login.php">
            <img src="./assets/img/logo.png" width="130px" alt="130px" />
        </a>
    </header>

    <main>
        <div class="container px-2 py-3" id="custom-cards">
            <div class="row g-4">

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="colaboradores.php" style="text-decoration: none; color: white;">Colaboradores</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>


                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="tiendas_exp.php" style="text-decoration: none; color: white;">Tiendas Explanada</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="ventas_persona.php" style="text-decoration: none; color: white;">Ventas por persona</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="comisiones.php" style="text-decoration: none; color: white;">Comision</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

            </div>
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