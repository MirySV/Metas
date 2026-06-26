<?php

session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (!isset($_SESSION['rol'])) {
  echo "No tienes permisos para acceder a esta pagina, favor de iniciar sesion";
  exit();
}

$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
$id_temporada = $_GET['id_temporada'] ?? 0;
$id_puente = $_GET['id_puente'] ?? 0;
$fecha = $_GET['fecha'] ?? '';
//echo $fecha;
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
  header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

include 'conexion.php'; //Conexion a la base de datos


if ($id_temporada != 0) {
  mysqli_query($conec, "UPDATE comparativos SET meta = ROUND((cantTempActYear + cantTempAnterior + puenteAnterior) / 3,2) WHERE id_temporada = '$id_temporada'");
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
    <a class="text-black m-0" id="nombrepagvxp" href="index.php">Ventas por Pax</a>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>

    <form method="GET" action="ventas_pax.php">
      <input type="hidden" name="id_temporada" value="<?php echo $id_temporada; ?>">
      <input type="date" name="fecha">
      <button type="submit">Ver día</button>
      <a href="ventas_pax.php?id_temporada=<?php echo $id_temporada; ?>" class="btn btn-secondary">Vista general</a>
    </form>
    <!-- Comienza la tabla que muestra los toda la informacion de ventas por pax -->
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
            <th width="1700" style="font-size: 16px;">
              <b>VXP Real Puente Anterior</b>
            </th>
            <?php if ($fecha != '') { ?>

              <th width="1700" style="font-size: 16px;">Meta Día</th>
              <th width="1700" style="font-size: 16px;">Venta Real</th>
              <th width="1700" style="font-size: 16px;">Resultados</th>
              <th width="1700" style="font-size: 16px;">Comisión</th>
              <?php if ($rol == 'admin') { ?>
                <th width="1000" style="font-size: 16px;">
                  <b>Cargar Venta Real</b>
                </th>
                <th width="1000" style="font-size: 16px;">
                  <b>Actualizar</b>
                </th>
              <?php } ?>

            <?php } else { ?>

              <th width="1700" style="font-size: 16px;">Meta Actual</th>
              <th width="1700" style="font-size: 16px;">Venta Total</th>
              <th width="1700" style="font-size: 16px;">Crecimiento</th>
              <th width="1700" style="font-size: 16px;">Comisión</th>
              <?php if ($rol == 'admin') { ?>
                <th width="1000" style="font-size: 16px;">
                  <b>Actualizar</b>
                </th>
              <?php } ?>

            <?php } ?>

          </tr>
          <?php

          //Comienza mi prueba de if para las consultas de la tabla por temporada y puente segun las fechas y las temporadas abiertas o cerradas
          $datos = null;
          if ($id_temporada != 0) {

            if ($fecha != '') {
              // temporada por día
              $fechafb = date('d.m.Y', strtotime($fecha));
              //echo "Fecha Firebird: $fechafb";

              include 'conTaquilla.php'; //Conexion a la base de datos de ibase de taquilla

              //Consulta para saber el numero de visitantes por dia consultando a la base de datos de taquilla
              $visitantesxdia = ibase_query($conn1, "SELECT SUM(ADULTOS_PV) + SUM(ADULTOS_VE) + SUM(NINOS_PV) + SUM(NINOS_VE) + SUM(BEBES_PV) + SUM(BEBES_VE) AS TOTAL_VISITANTES FROM mg_rep3('N','$fechafb', '$fechafb',0)");

              $fila = ibase_fetch_assoc($visitantesxdia);

              $visitantes = $fila['TOTAL_VISITANTES'];
              //echo $visitantes;

              mysqli_query($conec, "UPDATE comparativos_dia SET meta_dia = metaYearAnterior * $visitantes WHERE id_temporada = '$id_temporada'AND fecha = '$fecha'");

              $datos = mysqli_query($conec, "SELECT cd.id_comparativosDia,t.nombre,cd.metaYearAnterior,cd.cantTempActYear,cd.cantTempAnterior,cd.puenteAnterior,cd.meta_dia,cd.venta_real,cd.resultados,cd.comision, temp.estatus FROM comparativos_dia AS cd INNER JOIN tiendas AS t ON cd.id_tienda = t.id_tienda INNER JOIN temporadas AS temp ON cd.id_temporada = temp.id_temporada WHERE temp.id_temporada = '$id_temporada' AND cd.fecha = '$fecha'");
            } else {
              // temporada general
              $datos = mysqli_query($conec, "SELECT c.id_comparativos,t.nombre,c.metaYearAnterior,c.cantTempActYear,c.cantTempAnterior,c.puenteAnterior,c.meta,c.venta_total,c.crecimiento,c.comision, temp.estatus FROM comparativos AS c INNER JOIN tiendas AS t ON c.id_tienda = t.id_tienda INNER JOIN temporadas AS temp ON c.id_temporada = temp.id_temporada WHERE temp.id_temporada = '$id_temporada' /*AND '$fecha' BETWEEN temp.fecha_inicio AND temp.fecha_fin*/");
            }
          } elseif ($id_puente != 0) {

            if ($fecha != '') {
              // puente por día
              $datos = mysqli_query($conec, "SELECT cd.id_comparativosDia,t.nombre,cd.metaYearAnterior,cd.cantTempActYear,cd.cantTempAnterior,cd.puenteAnterior,cd.meta_dia,cd.venta_real,cd.resultados,cd.comision, puentes.estatus FROM comparativos_dia AS cd INNER JOIN tiendas AS t ON cd.id_tienda = t.id_tienda INNER JOIN puentes ON cd.id_temporada = puentes.id_puente WHERE puentes.id_puente = '$id_puente' AND cd.fecha = '$fecha'");
            } else {
              // puente general
              $datos = mysqli_query($conec, "SELECT c.id_comparativos,t.nombre,c.metaYearAnterior,c.cantTempActYear,c.cantTempAnterior,c.puenteAnterior,c.meta,c.venta_total,c.crecimiento,c.comision, puentes.estatus FROM comparativos AS c INNER JOIN tiendas AS t ON c.id_tienda = t.id_tienda INNER JOIN puentes ON c.id_temporada = puentes.id_puente WHERE puentes.id_puente = '$id_puente' /*AND '$fecha' BETWEEN temp.fecha_inicio AND temp.fecha_fin*/");
            }
          }

          //Siento que esta parte esta de mas, considero que no es necesario, la dejare comentada por si las dudas
          //Si el id_temporada es diferente de 0, se muestra la informacion de la temporada seleccionada, en caso contrario se muestra toda la informacion de todas las temporadas
          /*if ($id_temporada != 0) {

              //Si la fecha es diferente de vacio, se muestra la informacion de la temporada seleccionada en esa fecha, en caso contrario se muestra toda la informacion de la temporada seleccionada
              if ($fecha != '') {

                $temporadas = mysqli_query($conec, "SELECT tc.id_comparativos,t.nombre,tc.metaYearAnterior,tc.cantTempActYear,tc.cantTempAnterior,tc.puenteAnterior,tc.meta,tc.venta_total,tc.crecimiento,tc.comision, temp.estatus FROM temp_comparativos AS tc INNER JOIN tiendas AS t ON tc.id_tienda = t.id_tienda INNER JOIN temporadas AS temp ON tc.id_temporada = temp.id_temporada WHERE temp.id_temporada = '$id_temporada' AND '$fecha' BETWEEN temp.fecha_inicio AND temp.fecha_fin");
              }

              //Si la fecha es igual a vacio, se muestra toda la informacion de la temporada seleccionada
              else {
                $temporadas = mysqli_query($conec, "SELECT tc.id_comparativos,t.nombre,tc.metaYearAnterior,tc.cantTempActYear,tc.cantTempAnterior,tc.puenteAnterior,tc.meta,tc.venta_total,tc.crecimiento,tc.comision, temp.estatus FROM temp_comparativos AS tc INNER JOIN tiendas AS t ON tc.id_tienda = t.id_tienda INNER JOIN temporadas AS temp ON tc.id_temporada = temp.id_temporada WHERE temp.id_temporada = '$id_temporada'");
              }
              //Si el id_temporada es igual a 0, se muestra toda la informacion de todas las temporadas
            } else {
              $temporadas = mysqli_query($conec, "SELECT tc.id_comparativos,t.nombre,tc.metaYearAnterior,tc.cantTempActYear,tc.cantTempAnterior,tc.puenteAnterior,tc.meta,tc.venta_total,tc.crecimiento,tc.comision, temp.estatus FROM temp_comparativos AS tc INNER JOIN tiendas AS t ON tc.id_tienda = t.id_tienda INNER JOIN temporadas AS temp ON tc.id_temporada = temp.id_temporada");
            }*/

          if ($datos) {
            while ($i = mysqli_fetch_array($datos)) {
          ?>
              <tr>
                <form action="actualizarVPax.php" method="POST">
                  <!-- Muestra el nombre de la tienda -->
                  <td width="220">
                    <?php if ($fecha != '') { ?>

                      <!-- CAMPOS DE LA VISTA POR DÍA -->
                      <input type="hidden" name="id_comparativosDia" value="<?php echo $i['id_comparativosDia']; ?>">
                      <input type="hidden" name="id_temporada" value="<?php echo $id_temporada; ?>">
                      <input type="hidden" name="id_puente" value="<?php echo $id_puente; ?>">
                      <input type="text" name="nombre" value="<?php echo $i['nombre']; ?>" class="form-control" style="font-size: 14px;" readonly>
                      <input type="file" name="archivo" id="archivo<?php echo $i['id_comparativosDia']; ?>" accept=".xlsx,.xls" style="display:none;">

                    <?php } else { ?>

                      <!-- CAMPOS DE LA VISTA GENERAL -->
                      <input type="hidden" name="id_comparativos" value="<?php echo $i['id_comparativos']; ?>">
                      <input type="hidden" name="id_temporada" value="<?php echo $id_temporada; ?>">
                      <input type="hidden" name="id_puente" value="<?php echo $id_puente; ?>">
                      <input type="text" name="nombre" value="<?php echo $i['nombre']; ?>" class="form-control" style="font-size: 14px;" readonly>

                    <?php } ?>
                  </td>
                  <!-- Muestra la cantidad de temporadas actuales -->
                  <td width="220">
                    <input type="number" name="metaYearAnterior" value="<?php echo $i['metaYearAnterior']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin' || (isset($i['estatus']) && $i['estatus'] == 0)) {
                                                                                                                                                                echo "readonly";
                                                                                                                                                              } ?>>
                  </td>
                  <!-- Muestra el pax -->
                  <td width="220">
                    <input type="number" name="cantTempActYear" value="<?php echo $i['cantTempActYear']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin' || (isset($i['estatus']) && $i['estatus'] == 0)) {
                                                                                                                                                              echo "readonly";
                                                                                                                                                            } ?>>
                  </td>
                  <!-- Muestra los visitantes por experiencia -->
                  <td width="220">
                    <input type="number" name="cantTempAnterior" value="<?php echo $i['cantTempAnterior']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin' || (isset($i['estatus']) && $i['estatus'] == 0)) {
                                                                                                                                                                echo "readonly";
                                                                                                                                                              } ?>>
                  </td>
                  <td width="220">
                    <input type="number" name="puenteAnterior" value="<?php echo $i['puenteAnterior']; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin' || (isset($i['estatus']) && $i['estatus'] == 0)) {
                                                                                                                                                            echo "readonly";
                                                                                                                                                          } ?>>
                  </td>

                  <?php if ($fecha != '') { ?>
                    <!-- CAMPOS DE LA VISTA POR DÍA -->
                    <td width="220">
                      <input type="number" name="meta_dia"
                        value="<?php echo $i['meta_dia']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="venta_real"
                        value="<?php echo $i['venta_real']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="resultados"
                        value="<?php echo $i['resultados']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="comision"
                        value="<?php echo $i['comision']; ?>"
                        class="form-control">
                    </td>

                  <?php } else { ?>

                    <!-- CAMPOS DE LA VISTA GENERAL -->

                    <td width="220">
                      <input type="number" name="meta"
                        value="<?php echo $i['meta']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="venta_total"
                        value="<?php echo $i['venta_total']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="crecimiento"
                        value="<?php echo $i['crecimiento']; ?>"
                        class="form-control">
                    </td>

                    <td width="220">
                      <input type="number" name="comision"
                        value="<?php echo $i['comision']; ?>"
                        class="form-control">
                    </td>

                  <?php } ?>

                  <?php if ($rol == 'admin') { ?>
                    <?php if ($fecha != '') { ?>
                      <td width="220">
                        <center>

                          <button
                            type="button"
                            class="btneditar"
                            onclick="document.getElementById('archivo<?php echo $i['id_comparativosDia']; ?>').click();">

                            <i class="bi bi-upload"></i>

                          </button>

                        </center>
                      </td>
                    <?php } ?>
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
          } //Acaba el ciclo if que verifica si hay datos para mostrar 
          ?>
        </table>
      </div>
    </div>

    <?php if ($id_temporada != 0) { ?>
      <div>
        <form action="cerrarTemporada.php" method="POST">
          <input type="hidden" name="id_temporada" value="<?php echo $id_temporada; ?>">
          <button type="submit" class="cerrar_temporada">Cerrar temporada</button>
        </form>
      </div>
    <?php } ?>
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