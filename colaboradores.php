<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();

if (!isset($_SESSION['username'])) {
  header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
  exit();
}

$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
$rol = $_SESSION['rol'];

if ($rol != 'admin' && $rol != 'user' && $rol != 'supervisora') {
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
  <link rel="stylesheet" href="./css/style_colaboradores.css">
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbarcolaboradores">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" />
    </a>
    <a class="text-black m-0" id="nombrepagcolab" href="index.php">Colaboradores</a>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>
    <div class="filtro_tiendas">
      <form action="colaboradores.php" method="POST">
        <div class="filtro_acciones">
          <select id="tiendas" name="tiendas" class="filtro-input">
            <option value="todas" selected>Todas las tiendas</option>
            <option value="AFRITATTOS">AFRITATTOS</option>
            <option value="AVENTURA AMAZONICA">AVENTURA AMAZONICA</option>
            <option value="AVIARIO AUSTRALIANO">AVIARIO AUSTRALIANO</option>
            <option value="CARRITO DE LEONES">CARRITO DE LEONES</option>
            <option value="CHAMCHAWI">CHAMCHAWI</option>
            <option value="EXPLANADA">EXPLANADA</option>
            <option value="FOTO EXPERIENCIAS">FOTO EXPERIENCIAS</option>
            <option value="FOTO SAFARI">FOTO SAFARI</option>
            <option value="KARIBU">KARIBU</option>
            <option value="KARLUI">KARLUI</option>
            <option value="KIBOKO">KIBOKO</option>
            <option value="KU-HU-ZU">KU-HU-ZU</option>
            <option value="MAHALI">MAHALI</option>
            <option value="MATUNDA ESPECTACULOS">MATUNDA ESPECTACULOS</option>
            <option value="MICHELADAS">MICHELADAS</option>
            <option value="MOROCCO DULCERIA">MOROCCO DULCERIA</option>
            <option value="MOROCCO SOUVENIRS">MOROCCO SOUVENIRS</option>
            <option value="NIEVES ESPECTACULOS">NIEVES ESPECTACULOS</option>
            <option value="NIEVES MOMBASA">NIEVES MOMBASA</option>
            <option value="NIEVES MOROCCO">NIEVES MOROCCO</option>
            <option value="OCEANIA">OCEANIA</option>
            <option value="PALOMITAS MOROCCO">PALOMITAS MOROCCO</option>
            <option value="PENDA">PENDA</option>
            <option value="ZAWADI ASIATICOS">ZAWADI ASIATICOS</option>
            <option value="ZAWADI DUKAZURI">ZAWADI DUKAZURI</option>         
            <option value="ZAWADI HUELLAS">ZAWADI HUELLAS</option>
          </select>

          <input type="text" id="empleado" name="empleado" list="lista_empleados" placeholder="Nombre del colaborador" class="form-control filtro-input" style=" width: 250px; font-size: 14px;">
          <datalist id="lista_empleados">
            <?php
            //Consulta para obtener los nombres de los colaboradores y mostrarlos en el datalist del input de nombre del colaborador
            $colaboradores = mysqli_query($conec, "SELECT id_empleado, nombre FROM empleados WHERE status = 1 ORDER BY nombre");
            while ($empleadoBusqueda = mysqli_fetch_assoc($colaboradores)) {
            ?>
              <option value="<?php echo htmlspecialchars($empleadoBusqueda['nombre']); ?>">
              <?php
            }
              ?>
          </datalist>
          <button type="submit" style="font-size: 14px;">Filtrar</button>
        </div>
      </form>
    </div>

    <div class="info_colaboradores" style="overflow-x:auto;  ">
      <div class="info_colaboradores">

        <div class="tabla-scroll">

          <table class="tabla_colaboradores">

            <thead>
              <tr class="encabezado">
                <th>Colaborador</th>
                <th>Tienda</th>
                <th>Descanso</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>

              <?php
              $tienda = $_POST['tiendas'] ?? 'todas';
              $empleado = $_POST['empleado'] ?? '';

              if ($empleado != '') {

                $colaboradores = mysqli_query($conec,"SELECT e.id_empleado, e.nombre, t.nombre, e.descanso FROM empleados AS e INNER JOIN tiendas AS t ON e.id_tienda_actual = t.id_tienda WHERE e.nombre = '$empleado' ORDER BY e.nombre");
              } elseif ($tienda != 'todas') {

                $colaboradores = mysqli_query($conec,"SELECT e.id_empleado, e.nombre, t.nombre, e.descanso FROM empleados AS e INNER JOIN tiendas AS t ON e.id_tienda_actual = t.id_tienda WHERE t.nombre = '$tienda' ORDER BY e.nombre");
              } else {
                $colaboradores = mysqli_query($conec,"SELECT e.id_empleado, e.nombre, t.nombre, e.descanso FROM empleados AS e INNER JOIN tiendas AS t ON e.id_tienda_actual = t.id_tienda ORDER BY e.nombre");
              }

              while ($i = mysqli_fetch_array($colaboradores)) {
              ?>

                <tr>

                  <form action="actualizar_tiendaColab.php" method="POST">
                    <!-- ID DEL EMPLEADO -->
                    <input type="hidden" name="id_empleado" value="<?php echo $i[0]; ?>">
                    <!-- COLABORADOR -->
                    <td>
                      <div class="colaborador-info">
                        <div>
                          <span class="colaborador-nombre">
                            <?php echo $i[1]; ?>
                          </span> 
                        </div>
                      </div>
                    </td>


                    <!-- TIENDA -->
                    <td>
                      <input
                        id="tienda" list="tienda_<?php echo $i[0]; ?>" name="tienda" value="<?php echo $i[2]; ?>" class="form-control form-control-sm campo-tabla"
                        <?php
                        if ($rol != 'admin' && $rol != 'supervisora') echo "readonly";
                        ?>>

                      <datalist id="tienda_<?php echo $i[0]; ?>">

                        <option value="AFRITATTOS">
                        <option value="AVENTURA AMAZONICA">
                        <option value="AVIARIO AUSTRALIANO">
                        <option value="CARRITO DE LEONES">
                        <option value="CHAMCHAWI">
                        <option value="EXPLANADA">
                        <option value="FOTO EXPERIENCIAS">
                        <option value="FOTO SAFARI">
                        <option value="KARIBU">
                        <option value="KARLUI">
                        <option value="KIBOKO">
                        <option value="KU-HU-ZU">
                        <option value="MAHALI">
                        <option value="MATUNDA ESPECTACULOS">
                        <option value="MICHELADAS">
                        <option value="MOROCCO DULCERIA">
                        <option value="MOROCCO SOUVENIRS">
                        <option value="NIEVES ESPECTACULOS">
                        <option value="NIEVES MOMBASA">
                        <option value="NIEVES MOROCCO">
                        <option value="OCEANIA">
                        <option value="PALOMITAS MOROCCO">
                        <option value="PENDA">
                        <option value="ZAWADI ASIATICOS">
                        <option value="ZAWADI DUKAZURI">
                        <option value="ZAWADI HUELLAS">

                      </datalist>
                    </td>

                    <!-- DESCANSO -->
                    <td>

                      <select id="descanso" name="descanso" class="form-select form-select-sm campo-tabla"
                        <?php if ($rol != 'admin' && $rol != 'supervisora') echo "disabled";?>>
                        <option value="0" <?php if ($i[3] == 0) echo "selected"; ?>>TRABAJA FINES</option>
                        <option value="1" <?php if ($i[3] == 1) echo "selected"; ?>>LUNES</option>
                        <option value="2" <?php if ($i[3] == 2) echo "selected"; ?>>MARTES</option>
                        <option value="3" <?php if ($i[3] == 3) echo "selected"; ?>>MIÉRCOLES</option>
                        <option value="4" <?php if ($i[3] == 4) echo "selected"; ?>>JUEVES</option>
                        <option value="5" <?php if ($i[3] == 5) echo "selected"; ?>>VIERNES</option>
                        <option value="6" <?php if ($i[3] == 6) echo "selected"; ?>>SÁBADO</option>
                        <option value="7" <?php if ($i[3] == 7) echo "selected"; ?>>DOMINGO</option>
                      </select>

                    </td>

                    <!-- ACCIONES -->
                    <td>

                      <div class="acciones">
                        <?php if ($rol == 'admin' || $rol == 'supervisora') { ?>
                          <button type="submit" class="btn btn-sm btn-outline-primary btn-accion" formnovalidate onclick="return confirm('¿Está seguro que desea actualizar esta información?')" title="Actualizar información"><i class="bi bi-check2"></i>Actualizar</button>

                        <?php } ?>
                        <div class="rango-fechas">
                          <input type="date" id="inicio_<?php echo $i[0]; ?>" name="inicio" required class="form-control form-control-sm date" title="Fecha inicial">
                          <span class="fecha-separador">—</span>
                          <input type="date" id="fin_<?php echo $i[0]; ?>" name="fin" required class="form-control form-control-sm date" title="Fecha final">
                        </div>


                        <button type="button" class="btn btn-sm btn-outline-secondary btn-accion" onclick="buscar(<?php echo $i[0]; ?>, '<?php echo $i[1]; ?>')" title="Buscar historial"> <i class="bi bi-search"></i> Buscar </button>
                      </div>
                    </td>
                  </form>
                </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <!--Modal para mostrar los resultados de la busqueda de los registros del colaborador, se muestra el nombre del colaborador y las fechas seleccionadas para mostrar los registros de ese colaborador en ese rango de fechas-->

      <div class="modal fade" id="modal" tabindex="-1">
        <div class="modal-dialog modal-xl"> <!-- más grande -->
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title" id="tituloModal"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div id="tablaResultados"></div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

<script>
  let modal;

  document.addEventListener("DOMContentLoaded", function() {
    modal = new bootstrap.Modal(document.getElementById('modal'));
  });

  function buscar(id_empleado, nombre) {
    let inicio = document.getElementById("inicio_" + id_empleado).value;
    let fin = document.getElementById("fin_" + id_empleado).value;

    if (inicio === "" || fin === "") {
      alert("Selecciona ambas fechas");
      return;
    }

    document.getElementById("tituloModal").innerText =
      "Registros de " + nombre + " del " + inicio + " al " + fin;

    modal.show();

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
</script>

</html>