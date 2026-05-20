<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
  echo "No tienes permisos para acceder a esta pagina, favor de iniciar sesion";
  exit();
}

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

    .navbarcom {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      padding: 18px;
    }

    #nombrepagvxp {
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

    .inputVxP {
      /*border-radius: 10px;*/
      /*padding: 40px;*/
      width: 90%;
      margin: 40px auto;
    }

    .tabla_VxP {
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

    button {
      background-color: #de85315c;
      border: 1px;
      border-radius: 4px;
      padding: 4px 5px;
      /*font-family: "Glory", sans-serif;*/
      margin: 0px 15px 0px 7px;
    }

    .cerrar_temporada {
      background-color: #de85315c;
      border: 1px;
      border-radius: 4px;
      padding: 10px 20px; 
      display: block;
      margin: 0 auto;
      margin-bottom: 70px;
    }
  </style>
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbarcom">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" />
    </a>
    <a class="text-black m-0" id="nombrepagvxp" href="index.php">Ventas por Persona</a>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>
    <!-- Comienza l atbla que muestra los toda la informacion de ventas por persona -->
    <div class="inputVxP">
      <div class="info_texp" style="overflow-x:auto;  ">
        <table class="tabla_VxP">
          <tr class="encabezado">
            <th width="800" style="font-size: 16px;">
              <b>Tienda</b>
            </th>
            <th width="2000" style="font-size: 16px;">
              <b>Meta Año Anterior</b>
            </th>
            <th width="2500" style="font-size: 16px;">
              <b>VXP Real Año Anterior</b>
            </th>
            <th width="1700" style="font-size: 16px;">
              <b>VXP Real Temporada Anterior</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>VXP Real Puente Anterior</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>Meta Actual</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>Venta Real</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>Resultados</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>Comision</b>
            </th>
          </tr>
          <tr>
            <?php
            if (isset($_POST['inicio']) && isset($_POST['fin'])) {
              $fechainicio = $_POST['inicio'];
              $fechafin = $_POST['fin'];
              //Consulta para mostrar la informacion de tiendas explanada, si se selecciona una fecha, se muestra la informacion de esa fecha en especifico, se muestra la fecha, los grupos, el pax y los visitantes por experiencia, si no se selecciona una fecha, se muestra toda la informacion de la tabla tiendas_explanada, 
              $temporadas = mysqli_query($conec, "SELECT t.nombre, tc.cantTempActYear, tc.cantTempAnterior, tc.puenteAnterior, tc.meta, tc.venta_real, tc.resultados, tc.comision FROM temp_comparativos AS tc INNER JOIN tiendas AS t INNER JOIN temporadas AS temp ON tc.id_tienda = t.id_tienda WHERE tc.id_temporada = temp.id_temporada AND fecha_inicio AND fecha_fin BETWEEN '$fechainicio' AND '$fechafin'");
            } else {
              $temporadas = mysqli_query($conec, "SELECT t.nombre, tc.cantTempActYear, tc.cantTempAnterior, tc.puenteAnterior, tc.meta, tc.venta_real, tc.resultados, tc.comision FROM temp_comparativos AS tc INNER JOIN tiendas AS t ON tc.id_tienda = t.id_tienda");
            }
            //Ciclo para mostrar los colaboradores, se muestra el nombre del colaborador en la tabla
            while ($i = mysqli_fetch_array($temporadas)) {
            ?>
          <tr>
            <form action="actualizarTExp.php" method="POST">
              <!-- Muestra el nombre de la tienda -->
              <td width="220">
                <input type="hidden" name="id_comparativos" value="<?php echo $i['id_comparativos']; ?>">
                <input type="text" name="fecha" value="<?php echo $i['nombre']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
              </td>
              <!-- Muestra la cantidad de temporadas actuales -->
              <td width="220">
                <input type="number" name="grupos" value="<?php echo $i['cantTempActYear']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
              </td>
              <!-- Muestra el pax -->
              <td width="220">
                <input type="number" name="pax" value="<?php echo $i['cantTempAnterior']; ?>" class="form-control" style="font-size: 14px;" readonly>
              </td>
              <!-- Muestra los visitantes por experiencia -->
              <td width="220">
                <input type="text" name="visitantes" value="<?php echo $i['puenteAnterior']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>
              </td>
              <?php if ($rol == 'admin') { ?>
                <td width="220">
                  <input type="number" name="pax" value="<?php echo $i['meta']; ?>" class="form-control" style="font-size: 14px;" readonly>
                </td>
                <td width="220">
                  <input type="number" name="pax" value="<?php echo $i['venta_real']; ?>" class="form-control" style="font-size: 14px;" readonly>
                </td>
                <td width="220">
                  <input type="number" name="pax" value="<?php echo $i['resultados']; ?>" class="form-control" style="font-size: 14px;" readonly>
                </td>
                <td width="220">
                  <input type="number" name="pax" value="<?php echo $i['comision']; ?>" class="form-control" style="font-size: 14px;" readonly>
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
    </div>

    <div>
      <button class="cerrar_temporada">Cerrar temporada</button>
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