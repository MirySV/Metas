<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$rol = $_SESSION['rol'];

if ($rol != 'admin' && $rol != 'user') {
    header('Location: index.php');
    exit();
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
            text-decoration: none;
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

        .btneditar {
            background-color: transparent;
            border: none;
            color: #330a04;
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
        <a class="text-black m-0" id="nombrepagtiendasexp" href="index.php">Tiendas Explanada</a>
        <div class="cerrarsesion">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main>
        <!-- Formulario para ingresar datos de tiendas explanada, se enviar a guardarTExp.php para insertar en la base de datos -->
        <div class="inputTExplanada  card shadow-2-strong" style="border-radius: 1rem;">
            <form class="form-inline" action="guardarTExp.php" method="POST">
                <section>
                    <div class="row mb-3">
                        <label for="inputfecha" class="col-sm-2 col-form-label">Fecha:</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputGrupos" class="col-sm-2 col-form-label">Grupos:</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="grupos" name="grupos" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputVxE" class="col-sm-4 col-form-label">Visitantes por experiencia:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="vxe" name="vxe" required>
                        </div>
                    </div>
                    <br>
                    <button type="submit" onclick="return confirm('¿Esta seguro que desea guardar esta informacion?')">Guardar</button>
                </section>
            </form>
        </div>
        <!-- Formulario para consultar los grupos, pax y numero de visitantes segun la fecha -->
        <div class="inputTExplanada  card shadow-2-strong" style="border-radius: 1rem;">
            <form class="form-inline" action="tiendas_exp.php" method="GET">
                <div class="row mb-3 align-items-center">
                    <label for="inputfecha" class="col-sm-1 col-form-label">Del:</label>
                    <div class="col-sm-4">
                        <input type="date" name="inicio" class="form-control" value="<?php echo isset($_GET['inicio']) ? $_GET['inicio'] : ''; ?>" required>
                    </div>

                    <label for="inputfecha" class="col-sm-1 col-form-label">al:</label>
                    <div class="col-sm-4">
                        <input type="date" name="fin" class="form-control" value="<?php echo isset($_GET['fin']) ? $_GET['fin'] : ''; ?>" required>
                    </div>

                    <div class="col-sm-2">
                        <button type="submit">Buscar</button>
                    </div>
                </div>
            </form>


            <!-- Comienza l atbla que muestra los grupos, el pax y los visitantes -->
            <div class="info_texp" style="overflow-x:auto;  ">
                <table class="tabla_texp">
                    <tr class="encabezado">
                        <th width="800" style="font-size: 16px;">
                            <b>Fecha</b>
                        </th>
                        <th width="2000" style="font-size: 16px;">
                            <b>Grupos</b>
                        </th>
                        <th width="2500" style="font-size: 16px;">
                            <b>PAX</b>
                        </th>
                        <th width="1700" style="font-size: 16px;">
                            <b>Visitantes</b>
                        </th>
                        <?php if ($rol == 'admin') { ?>
                        <th width="1000" style="font-size: 16px;">
                            <b>Actualizar</b>
                        </th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php
                        if (isset($_GET['inicio']) && isset($_GET['fin'])) {
                            $fechainicio = $_GET['inicio'];
                            $fechafin = $_GET['fin'];
                            //Consulta para mostrar la informacion de tiendas explanada, si se selecciona una fecha, se muestra la informacion de esa fecha en especifico, se muestra la fecha, los grupos, el pax y los visitantes por experiencia, si no se selecciona una fecha, se muestra toda la informacion de la tabla tiendas_explanada, 
                            $tiendas_explanada = mysqli_query($conec, "SELECT * FROM tiendas_explanada WHERE fecha BETWEEN '$fechainicio' AND '$fechafin' ORDER BY fecha DESC ");
                        } else {
                            $tiendas_explanada = mysqli_query($conec, "SELECT * FROM tiendas_explanada ORDER BY fecha DESC");
                        }
                        //Ciclo para mostrar los colaboradores, se muestra el nombre del colaborador en la tabla
                        while ($i = mysqli_fetch_array($tiendas_explanada)) {
                        ?>
                    <tr>
                        <form action="actualizarTExp.php" method="POST">
                            <!-- Muestra la fecha-->
                            <td width="220">
                                <input type="hidden" name="id_tiexp" value="<?php echo $i['id_tiexp']; ?>">
                                <input type="date" name="fecha" value="<?php echo $i['fecha']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
                            </td>
                            <!-- Muestra los grupos -->
                            <td width="220">
                                <input type="text" name="grupos" value="<?php echo $i['grupos']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
                            </td>
                            <!-- Muestra el pax -->
                            <td width="220">
                                <input type="number" name="pax" value="<?php echo $i['pax']; ?>" class="form-control" style="font-size: 14px;" readonly>
                            </td>
                            <!-- Muestra los visitantes por experiencia -->
                            <td width="220">
                                <input type="text" name="visitantes" value="<?php echo $i['visitantes']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
                            </td>
                            <?php if ($rol == 'admin') { ?>
                            <td width="220">
                                <center>
                                    <button type="submit" class="btneditar" formnovalidate onclick="return confirm('¿Está seguro que desea actualizar esta informacion?')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>   
                                </center>
                            </td>
                            <?php } ?>
                        </form>
                    </tr>
                <?php
                        } //Acaba el ciclo while 
                ?>
                </tr>
                </table>
            </div>
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