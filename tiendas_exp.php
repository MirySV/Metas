<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();
$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
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

        .navbartiendasexp {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            padding: 18px;
        }

        #nombrepagtiendasexp {
            font-family: "Macondo", cursive;
            font-size: 45px;
            color: #330a04;
            text-align: center;
            margin: 0;
            padding-right: 150px;
        }

        .cerrarsesion {
            font-family: "Macondo", cursive;
            font-size: 22px;
            position: absolute;
            right: 20px;
            top: 40px;
        }

        .cerrarsesion a {
            text-decoration: none;
            color: #330a04;
        }

        .cerrarsesion a:hover {
            color: #ffffff;
        }

        .inputTExplanada {
            border-radius: 10px;
            padding: 50px;
            width: 50%;
            margin: 40px auto;
        }

        button {
            background-color: #de85315c;
            border: none;
            border-radius: 4px;
            padding: 3px 6px;
            font-family: "Glory", sans-serif;
            display: flex;
            text-align: center;
        }

        button:hover {
            background-color: #de8531;
            color: white;
        }
    </style>
    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="navbartiendasexp">
        <a href="login.php">
            <img src="./assets/img/logo.png" width="130px" />
        </a>
        <h2 class="text-black m-0" id="nombrepagtiendasexp">Tiendas Explanada</h2>
        <div class="cerrarsesion">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main>
        <div class="inputTExplanada  card shadow-2-strong" style="border-radius: 1rem;">
            <form class="form-inline" action="validar.php" method="post">
                <section>
                    <div class="row mb-3">
                        <label for="inputfecha" class="col-sm-2 col-form-label">Fecha:</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="inputfecha">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputGrupos" class="col-sm-2 col-form-label">Grupos:</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="inputGrupos">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputVxE" class="col-sm-4 col-form-label">Visitantes por experiencia:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="inputVxE">
                        </div>
                    </div>
                    <br>
                    <button type="submit">Guardar</button>
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