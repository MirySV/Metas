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
  <style>
#tabla_colaboradores{
    /*width: 100%;*/
    margin: 0 auto;
    margin-top: 50px;
    margin-left: 100px;
    border-collapse: collapse;
}
th, td {
  border:3px solid black;
  padding: 10px;
}
.filtro_acciones{
    margin: 20px 500px 20px 600px;
}
.info_colaboradores{
    margin: 50px 300px 50px 300px;
    border-collapse: collapse;
}
  </style>
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbar">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" alt="130px" />
    </a>
    <center><h2 class="text-black" id="nombrepag">Colaboradores</h2></center>
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

    <div class="info_colaboradores" style="overflow:auto; width:900px;height:400px;  ">
      <table class="tabla_colaboradores">
        <tr>
          <td width="1500">
            Colaborador
          </td>
          <td width="700">
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
          <td width="220">
            <form action="actualizar_tienda.php" method="post">

              <input type="hidden" name="id_empleado" value="<?php echo $i[0]; ?>">

              <datalist id="tienda">
                <option value="KARIBU" <?php if ($i[2] == "KARIBU") echo "selected"; ?>>KARIBU</option>
                <option value="EXPLANADA">EXPLANADA</option>
                <option value="CHAMCHAWI" <?php if ($i[2] == "CHAMCHAWI") echo "selected"; ?>>CHAMCHAWI</option>
                <option value="NIEVES ESPECTACULOS" <?php if ($i[2] == "NIEVES ESPECTACULOS") echo "selected"; ?>>NIEVES ESPECTACULOS</option>
                <option value="AVENTURA AMAZONICA" <?php if ($i[2] == "AVENTURA AMAZONICA") echo "selected"; ?>>AVENTURA AMAZONICA</option>
                <option value="MATUNDA ESPECTACULOS" <?php if ($i[2] == "MATUNDA ESPECTACULOS") echo "selected"; ?>>MATUNDA ESPECTACULOS</option>
                <option value="ZAWADI DUKAZURI" <?php if ($i[2] == "ZAWADI DUKAZURI") echo "selected"; ?>>ZAWADI DUKAZURI</option>
                <option value="ZAWADI ASIATICOS" <?php if ($i[2] == "ZAWADI ASIATICOS") echo "selected"; ?>>ZAWADI ASIATICOS</option>
                <option value="MOROCCO SOURVENIRS" <?php if ($i[2] == "MOROCCO SOURVENIRS") echo "selected"; ?>>MOROCCO SOURVENIRS</option>
                <option value="NIEVES MOROCCO" <?php if ($i[2] == "NIEVES MOROCCO") echo "selected"; ?>>NIEVES MOROCCO</option>
                <option value="MICHELADAS" <?php if ($i[2] == "MICHELADAS") echo "selected"; ?>>MICHELADAS</option>
                <option value="CARRITO DE LEONES" <?php if ($i[2] == "CARRITO DE LEONES") echo "selected"; ?>>CARRITO DE LEONES</option>
                <option value="AFRITATTOS" <?php if ($i[2] == "AFRITATTOS") echo "selected"; ?>>AFRITATTOS</option>
                <option value="MOROCCO DULCERIA" <?php if ($i[2] == "MOROCCO DULCERIA") echo "selected"; ?>>MOROCCO DULCERIA</option>
                <option value="PALOMITAS MOROCCO" <?php if ($i[2] == "PALOMITAS MOROCCO") echo "selected"; ?>>PALOMITAS MOROCCO</option>
                <option value="KARLUI" <?php if ($i[2] == "KARLUI") echo "selected"; ?>>KARLUI</option>
                <option value="PENDA" <?php if ($i[2] == "PENDA") echo "selected"; ?>>PENDA</option>
                <option value="MAHALI" <?php if ($i[2] == "MAHALI") echo "selected"; ?>>MAHALI</option>
                <option value="NIEVES MOMBASA" <?php if ($i[2] == "NIEVES MOMBASA") echo "selected"; ?>>NIEVES MOMBASA</option>
                <option value="KU-HU-ZU" <?php if ($i[2] == "KU-HU-ZU") echo "selected"; ?>>KU-HU-ZU</option>
                <option value="FOTO SAFARI" <?php if ($i[2] == "FOTO SAFARI") echo "selected"; ?>>FOTO SAFARI</option>
                <option value="ZAWADI HUELLAS" <?php if ($i[2] == "ZAWADI HUELLAS") echo "selected"; ?>>ZAWADI HUELLAS</option>
                <option value="OCEANIA" <?php if ($i[2] == "OCEANIA") echo "selected"; ?>>OCEANIA</option>
                <option value="AVIARIO AUSTRALIANO" <?php if ($i[2] == "AVIARIO AUSTRALIANO") echo "selected"; ?>>AVIARIO AUSTRALIANO</option>
              </datalist>
              <input list="tienda" name="tienda" id="tienda" value="<?php echo $i[2]; ?>">
            </form>
          </td>
          <!-- Muestra un formulario para actualizar la tienda a la que pertenece el colaborador, se envia el id del colaborador para actualizarlo en la base de datos, se muestra un select con las tiendas disponibles para seleccionar a cual tienda se desea cambiar al colaborador -->
          <td width="220">
            <!-- Aqui va el campo de actualizar -->
            <button type="submit">Actualizar</button>
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
  <footer>
    <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
  </footer>

  <!-- CDN Boostrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
  </script>

</body>

</html>