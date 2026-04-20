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
    .navbarcolaboradores {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      padding: 18px;
    }

    #nombrepagcolab {
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
    #tabla_colaboradores {
      /*width: 100%;*/
      margin: 0 auto;
      margin-top: 50px;
      margin-left: 100px;
      border-collapse: collapse;
      font-size: 12px;
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

    /*tr:nth-child(even) {
      background-color: #73241127;
    }*/

    /*tr:hover {
      background-color: #de853136;
    }*/

    .encabezado {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: left;
      background-color: #77240f;
      color: white;
    }

    .filtro_acciones {
      margin: 20px 480px 20px 480px;
    }

    .filtro_tiendas {
      display: flex;
      justify-content: center;
    }

    .info_colaboradores {
      overflow-x: auto;
      margin: 50px 120px 50px 120px;
      border-collapse: collapse;
      font-family: "Glory", sans-serif;
    }

    #tiendas {
      padding: 5px;
      font-size: 14px;
      font-family: "Glory", sans-serif;
      border-radius: 4px;
    }

    #tienda {
      border-radius: 4px;
      border: 1px;
    }

    .date {
      width: 100px;
    }

    button {
      background-color: #de85315c;
      border: none;
      border-radius: 4px;
      padding: 2px 5px;
      font-family: "Glory", sans-serif;
      margin: 0px 15px 0px 7px;
    }
  </style>
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbarcolaboradores">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" />
    </a>
      <h2 class="text-black m-0" id="nombrepagcolab">Colaboradores</h2>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>
    <div class="filtro_tiendas">
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
    </div>

    <div class="info_colaboradores" style="overflow-x:auto;  ">
      <table class="tabla_colaboradores">
        <tr class="encabezado">
          <th width="2000" style="font-size: 18px;">
            <b>Colaborador</b>
          </th>
          <th width="1300" style="font-size: 18px;">
            <b>Tienda</b>
          </th>
          <th width="2800" style="font-size: 18px;">
            <b>Acciones</b>
          </th>
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
          <form action="actualizar_tienda.php" method="post">
            <!-- Muestra el nombre del colaborador, la posicion 1 es el nombre, por ser el unico valor solicitado en la consulta -->
            <td width="220">
              <?php echo $i[1]; ?>
            </td>
            <!-- Muestra el nombre de la tienda a la que pertenece el colaborador, la posicion 2 es el nombre de la tienda, por ser el segundo valor solicitado en la consulta -->
            <td width="220">
              <!-- Muestra un formulario para actualizar la tienda a la que pertenece el colaborador, se envia el id del colaborador para actualizarlo en la base de datos, se muestra un select con las tiendas disponibles para seleccionar a cual tienda se desea cambiar al colaborador -->
              <input type="hidden" name="id_empleado" value="<?php echo $i[0]; ?>">

              <input id="tienda" list="tienda_<?php echo $i[0]; ?>" name="tienda" value="<?php echo $i[2]; ?>" class="form-control" style="font-size: 14px;" <?php if ($rol != 'admin') echo "readonly"; ?>>

              <datalist id="tienda_<?php echo $i[0]; ?>">
                <option value="KARIBU">
                <option value="EXPLANADA">
                <option value="CHAMCHAWI">
                <option value="NIEVES ESPECTACULOS">
                <option value="AVENTURA AMAZONICA">
                <option value="MATUNDA ESPECTACULOS">
                <option value="ZAWADI DUKAZURI">
                <option value="ZAWADI ASIATICOS">
                <option value="MOROCCO SOURVENIRS">
                <option value="NIEVES MOROCCO">
                <option value="MICHELADAS">
                <option value="CARRITO DE LEONES">
                <option value="AFRITATTOS">
                <option value="MOROCCO DULCERIA">
                <option value="PALOMITAS MOROCCO">
                <option value="KARLUI">
                <option value="PENDA">
                <option value="MAHALI">
                <option value="NIEVES MOMBASA">
                <option value="KU-HU-ZU">
                <option value="FOTO SAFARI">
                <option value="ZAWADI HUELLAS">
                <option value="OCEANIA">
                <option value="AVIARIO AUSTRALIANO">
              </datalist>
            </td>
            <!-- Muestra un formulario para actualizar la tienda a la que pertenece el colaborador, se envia el id del colaborador para actualizarlo en la base de datos, se muestra un select con las tiendas disponibles para seleccionar a cual tienda se desea cambiar al colaborador -->
            <td width="220">
              <!-- Aqui va el boton de actualizar -->
              <?php if ($rol == 'admin') { ?>
                <button style="margin-right: 90px;" type="submit">Actualizar</button>
              <?php } ?>
              <input type="date" id="inicio_<?php echo $i[0]; ?>" name="inicio" required class="date">
              <input type="date" id="fin_<?php echo $i[0]; ?>" name="fin" required class="date">
              <button type="button" onclick="buscar(<?php echo $i[0]; ?> , '<?php echo $i[1]; ?>')">Buscar</button>
            </td>
          </form>
        </tr>
      <?php
          } //Acaba el ciclo while 
      ?>
      </tr>
      </table>

      <!--Modal para mostrar los resultados de la busqueda de los registros del colaborador, se muestra el nombre del colaborador y las fechas seleccionadas para mostrar los registros de ese colaborador en ese rango de fechas-->
      <div id="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:white; margin:5% auto; padding:20px; width:80%; max-height:80%; overflow:auto;">
          <h3 id="tituloModal"></h3>
          <div id="tablaResultados"></div>
          <button onclick="cerrarModal()">Cerrar</button>
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

<script>
  function buscar(id_empleado, nombre) {
    let inicio = document.getElementById("inicio_" + id_empleado).value;
    let fin = document.getElementById("fin_" + id_empleado).value;

    if (inicio === "" || fin === "") {
      alert("Selecciona ambas fechas");
      return;
    }

    document.getElementById("tituloModal").innerText = "Registros de " + nombre;

    // abrir modal
    document.getElementById("modal").style.display = "block";

    // enviar datos a PHP
    fetch("reporte.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id_empleado=" + id_empleado + "&inicio=" + inicio + "&fin=" + fin
      })
      .then(res => res.text())
      .then(data => {
        console.log(data);
        document.getElementById("tablaResultados").innerHTML = data;
      });
  }

  function cerrarModal() {
    document.getElementById("modal").style.display = "none";
  }
</script>

</html>