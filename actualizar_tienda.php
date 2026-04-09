<!-- Esta en vemos si se usa este archivo o no -->

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
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
        <a href="login.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
            <img src="./assets/img/logo.png" width="10%" alt="50%" />
            <h2 class="text-black" id="nombrepag">Colaboradores</h2>
        </a>
    </header>

    <main>
        <form action="colaboradores.php" method="POST">
            <div class="filtro_acciones">
                <select id="tiendas" name="tiendas">
                    <option value="todas" selected>Todas las tiendas</option>
                    <option value="KARIBU">KARIBU</option>
                    <option value="EXPLANADA">EXPLANADA</option>
                    <option value="CHAMCHAWI">CHAMCHAWI</option>
                    <option value="NIEVES ESPECTACULOS">NIEVES ESPECTACULOS</option>
                    <option value="AVENTURA AMAZONICA">AVENTURA AMAZONICA</option>
                    <option value="MATUNDA ESPECTACULOS">MATUNDA ESPECTACULOS</option>
                    <option value="ZAWADI DUKAZURI">ZAWADI DUKAZURI</option>
                    <option value="ZAWADI ASIATICOS">ZAWADI ASIATICOS</option>
                    <option value="MOROCCO SOURVENIRS">MOROCCO SOURVENIRS</option>
                    <option value="NIEVES MOROCCO">NIEVES MOROCCO</option>
                    <option value="MICHELADAS">MICHELADAS</option>
                    <option value="CARRITO DE LEONES">CARRITO DE LEONES</option>
                    <option value="AFRITATTOS">AFRITATTOS</option>
                    <option value="MOROCCO DULCERIA">MOROCCO DULCERIA</option>
                    <option value="PALOMITAS MOROCCO">PALOMITAS MOROCCO</option>
                    <option value="KARLUI">KARLUI</option>
                    <option value="PENDA">PENDA</option>
                    <option value="MAHALI">MAHALI</option>
                    <option value="NIEVES MOMBASA">NIEVES MOMBASA</option>
                    <option value="KU-HU-ZU">KU-HU-ZU</option>
                    <option value="FOTO SAFARI">FOTO SAFARI</option>
                    <option value="ZAWADI HUELLAS">ZAWADI HUELLAS</option>
                    <option value="OCEANIA">OCEANIA</option>
                    <option value="AVIARIO AUSTRALIANO">AVIARIO AUSTRALIANO</option>
                </select>
                <button type="submit">Filtrar</button>
            </div>
        </form>

        <div id="info_colaboradores" style="overflow:auto; width:900px;height:200px;  ">
            <table class="tabla_colaboradores">
                <tr>
                    <td width="1000">
                        Colaborador
                    </td>
                    <td width="1000">
                        Tienda
                    </td>
                    <td width="1000">
                        Acciones
                    </td>
                </tr>
                <tr>
                    <?php
                    //Condicional para mostrar los colaboradores de acuerdo a la tienda seleccionada, si se selecciona "todas las tiendas" se muestran todos los colaboradores, de lo contrario se muestra el colaborador de la tienda seleccionada
                    if (isset($_POST['tiendas']) && $_POST['tiendas'] != "") {
                        $tienda = $_POST['tiendas'];
                        if ($tienda == "todas") {
                            //Consulta para mostrar todos los colaboradores, se hace un inner join entre empleados y tiendas para mostrar el nombre del colaborador de acuerdo a la tienda a la que pertenece
                            $colaboradores = mysqli_query($conec, "SELECT e.id_empleado, e.nombre, t.nombre FROM empleados AS e INNER JOIN tiendas AS t ON e.id_tienda = t.id_tienda");
                        } else {
                            //Consulta para mostrar los colaboradores de la tienda seleccionada
                            $colaboradores = mysqli_query($conec, "SELECT e.id_empleado,e.nombre, t.nombre FROM empleados AS e INNER JOIN tiendas AS t WHERE t.nombre = '$tienda' AND e.id_tienda = t.id_tienda");
                        }
                    } else {
                        //Consulta para mostrar todos los colaboradores, se hace un inner join entre empleados y tiendas para mostrar el nombre del colaborador de acuerdo a la tienda a la que pertenece
                        $colaboradores = mysqli_query($conec, "SELECT e.id_empleado, e.nombre, t.nombre FROM empleados AS e INNER JOIN tiendas AS t ON e.id_tienda = t.id_tienda");
                    }
                    //Ciclo para mostrar los colaboradores, se muestra el nombre del colaborador en la tabla
                    while ($i = mysqli_fetch_array($colaboradores)) {
                    ?>
                <tr>
                    <!-- Muestra el nombre del colaborador, la posicion 0 es el nombre, por ser el unico valor solicitado en la consulta -->
                    <td width="220"><?php echo $i[1]; ?></td>
                    <!-- Muestra el nombre de la tienda a la que pertenece el colaborador, la posicion 1 es el nombre de la tienda, por ser el segundo valor solicitado en la consulta -->
                    <td width="220"><?php echo $i[2]; ?></td>
                    <!-- Muestra un formulario para actualizar la tienda a la que pertenece el colaborador, se envia el id del colaborador para actualizarlo en la base de datos, se muestra un select con las tiendas disponibles para seleccionar a cual tienda se desea cambiar al colaborador -->
                    <td width="220">
                        <!-- Aqui va el campo de actualizar -->
                        <a href="actualizar_tienda.php" target="_self">Actualizar</a>
                    </td>
                </tr>
            <?php
                    } //Acaba el ciclo while 
            ?>
            </th>
            </tr>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 my-4">
        <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
    </footer>

    <!-- CDN Boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

</body>

</html>