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

    .navbarvxp {
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

    .inputcomisiones {
      /*border-radius: 10px;*/
      /*padding: 40px;*/
      width: 90%;
      margin: 40px auto;
      margin-bottom: 70px;
    }

    .tabla_comisiones {
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
  </style>
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbarvxp">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" />
    </a>
    <a class="text-black m-0" id="nombrepagvxp" href="index.php">Comisiones</a>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>
    <!-- Comienza l atbla que muestra los toda la informacion de ventas por persona -->
    <div class="inputcomisiones">
      <div class="info_com" style="overflow-x:auto;  ">
        <table class="tabla_comisiones">
          <tr class="encabezado">
            <th width="800" style="font-size: 16px;">
              <b>Tienda</b>
            </th>
            <th width="2000" style="font-size: 16px;">
              <b>......</b>
            </th>
            <th width="2500" style="font-size: 16px;">
              <b>.....</b>
            </th>
            <th width="1700" style="font-size: 16px;">
              <b>.....</b>
            </th>
            <th width="1000" style="font-size: 16px;">
              <b>......</b>
            </th>
          </tr>
          <tr>
            <?php
            if (isset($_POST['inicio']) && isset($_POST['fin'])) {
              $fechainicio = $_POST['inicio'];
              $fechafin = $_POST['fin'];
              //Consulta para mostrar la informacion de tiendas explanada, si se selecciona una fecha, se muestra la informacion de esa fecha en especifico, se muestra la fecha, los grupos, el pax y los visitantes por experiencia, si no se selecciona una fecha, se muestra toda la informacion de la tabla tiendas_explanada, 
              $tiendas_explanada = mysqli_query($conec, "SELECT * FROM tiendas_explanada WHERE fecha BETWEEN '$fechainicio' AND '$fechafin' ORDER BY fecha DESC ");
            } else {
              $tiendas_explanada = mysqli_query($conec, "SELECT * FROM tiendas_explanada ORDER BY fecha DESC");
            }
            //Ciclo para mostrar los colaboradores, se muestra el nombre del colaborador en la tabla
            while ($i = mysqli_fetch_array($tiendas_explanada)) {
            ?>
          <tr>
            <form action="" method="POST">
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