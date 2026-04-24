<?php

include 'conexion.php'; //Conexion a la base de datos
//var_dump($_GET);
session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para actualizar";
    exit();
}

$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
//Recibe los datos del formulario de tiendas_exp , la fecha, los grupos y los visitantes por experiencia para guardar la informacion en la base de datos

$id_tiexp = $_GET['id_tiexp']; //Recibe el id del registro seleccionado en el formulario para editar
//echo "ID del registro a editar: " . $id_tiexp; //Confirmo el id del registro que se va a editar

//Busca el id del registro seleccionado en el formulario para editar
$buscar_tiexp = mysqli_query($conec, "SELECT * FROM tiendas_explanada WHERE id_tiexp='$id_tiexp'");
$fila = mysqli_fetch_array($buscar_tiexp);
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
            padding: 40px;
            width: 50%;
            margin: 40px auto;
        }

        button {
            background-color: #de85315c;
            border: none;
            border-radius: 4px;
            padding: 3px 6px;
            /*font-family: "Glory", sans-serif;*/
            display: flex;
            text-align: center;
        }

        button:hover {
            background-color: #de8531;
            color: white;
        }

        .tabla_texp {
            /*width: 100%;*/
            margin: 0 auto;
            margin-top: 30px;
            border-collapse: collapse;
            /*font-size: 12px;*/
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #77240f81;
            padding: 10px;
            font-size: 14px;
        }

        .encabezado {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #77240f;
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
         <!-- Formulario para consultar los grupos, pax y numero de visitantes segun la fecha -->
        <div class="inputTExplanada  card shadow-2-strong" style="border-radius: 1rem;">
            <form class="form-inline" action="tiendas_exp.php" method="POST">
                <section>
                    <!-- Comienza l atbla que muestra los grupos, el pax y los visitantes -->
                    <div class="info_texp" style="overflow-x:auto;  ">
                        <table class="tabla_texp">
                            <tr class="encabezado">
                                <th width="1800" style="font-size: 16px;">
                                    <b>Fecha</b>
                                </th>
                                <th width="1800" style="font-size: 16px;">
                                    <b>Grupos</b>
                                </th>
                                <th width="1800" style="font-size: 16px;">
                                    <b>PAX</b>
                                </th>
                                <th width="1700" style="font-size: 16px;">
                                    <b>Visitantes</b>
                                </th>
                                <th width="1700" style="font-size: 16px;">
                                    <b>Actualizar</b>
                                </th>
                            </tr>
                            <tr>
                            <tr>
                                <form action="actualizarTExp.php" method="POST">
                                    <input type="hidden" name="id_tiexp" value="<?php echo $fila['id_tiexp']; ?>">
                                    <!-- Muestra la fecha-->
                                    <td width="200">
                                        <input type="date" name="fecha" value="<?php echo $fila['fecha']; ?>">
                                    </td>
                                    <!-- Muestra los grupos -->
                                    <td width="200">
                                        <input type="number" name="grupos" value="<?php echo $fila['grupos']; ?>">
                                    </td>
                                    <!-- Muestra el pax -->
                                    <td width="200">
                                       <input type="number" name="pax" value="<?php echo $fila['pax']; ?>">
                                    </td>
                                    <!-- Muestra los visitantes por experiencia -->
                                    <td width="200">
                                        <input type="number" name="visitantes" value="<?php echo $fila['visitantes']; ?>">
                                    </td>
                                    <td width="200">
                                         <button type="submit">Guardar</button>
                                    </td>
                                </form>
                            </tr>
                        </tr>
                        </table>
                    </div>
                </section>
            </form>
        </div>
    </main>
    <footer>
        <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
    </footer>

    <!-- CDN Boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
