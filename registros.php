<?php
include "conexion.php";
//date_default_timezone_set('America/Mazatlan');
date_default_timezone_set('America/Mexico_City');
$fecha = date("Y-m-d");
$hora = date("H:i:s");


$ip = $_SERVER['REMOTE_ADDR'];
//echo $ip;
?>
<!--<h3><?php echo $ip; ?></h3>-->
<?php
// Buscar tienda por IP
$consultaTienda = mysqli_query($conec, "SELECT * FROM tiendas WHERE ip='$ip' AND estatus=1");
$tienda = mysqli_fetch_array($consultaTienda);
if ($tienda) {
  $id_tienda_actual = $tienda['id_tienda'];
  $nombre_tienda = $tienda['nombre'];
} else {
  $id_tienda_actual = 0;
  $nombre_tienda = "Tienda desconocida";
}

//echo $id_tienda_actual;
//Consulta original
//$scar=mysqli_query($conec,"SELECT * FROM registros as r,empleados as e WHERE r.idEmpleado=e.idEmpleado AND codigo=".$_POST['tarjeta']." AND r.fecha='".$fecha."'");

// Consulta cruzada entre 'bd_metas' y 'bd_reloj'
if (!empty($_POST['tarjeta'])) {

  // Buscar empleado en reloj
  $empleado = mysqli_query($conec, "SELECT idEmpleado FROM reloj.empleados WHERE codigo = " . $_POST['tarjeta'] . "");

  if (mysqli_num_rows($empleado) > 0) {

    $idEmpl = mysqli_fetch_assoc($empleado);
    $idEmpleado = $idEmpl['idEmpleado'];

    // Buscar el empleado en Metas para conocer su tienda
    $empleadoMetas = mysqli_query($conec, "SELECT id_tienda_actual FROM metas.empleados WHERE id_empleado='$idEmpleado'
        ");

    if (mysqli_num_rows($empleadoMetas) == 0) {

      $mensaje = "<div class='alerta'>⚠️ <strong>El empleado aún no ha sido dado de alta en Tiendas.</strong></div>";
    } else {

      $datosEmpleado = mysqli_fetch_assoc($empleadoMetas);

      // Verificar que pertenece a esta tienda
      if ($datosEmpleado['id_tienda_actual'] != $id_tienda_actual) {

        $mensaje = "<div class='alerta'>⚠️ <strong>El empleado no pertenece a esta tienda.</strong></div>";
      } else {

        // Verificar que ya checó en el reloj general
        $checador = mysqli_query($conec, "SELECT * FROM reloj.registros WHERE idEmpleado='$idEmpleado' AND fecha='$fecha'");

        if (mysqli_num_rows($checador) == 0) {

          $mensaje = "<div class='alerta'>⚠️ <strong>Primero debe registrar su entrada en el reloj general.</strong></div>";
        } else {

          // Verificar que no esté registrado en Metas
          $registrado = mysqli_query($conec, "SELECT * FROM metas.registros WHERE id_empleado='$idEmpleado' AND fecha='$fecha'"); /*AND id_tienda_actual='$id_tienda_actual'");*/

          if (mysqli_num_rows($registrado) > 0) {

            $mensaje = "<div class='alerta1'>🔵 <strong>El empleado ya se encuentra registrado el día de hoy.</strong></div>";
          } else {

            // Insertar registro
            mysqli_query($conec, "INSERT INTO metas.registros (id_registro,id_tienda_actual,id_empleado,fecha,hora_entrada,tipo_registro) VALUES (NULL,'$id_tienda_actual','$idEmpleado','$fecha','$hora','NORMAL')");
          }
        }
      }
    }
  } else {

    $mensaje = "<div class='alerta'>⚠️ <strong>El empleado no existe en el reloj.</strong></div>";
  }
}

//ALMACEN, VIGILANCIA
?>
<!doctype html>

<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="./assets/css/estilos.css">
  <link rel="icon" href="./assets/img/icono.ico" type="image/x-icon">
  <link rel="stylesheet" href="./assets/css/estilos.css">

  <style>
    .btn a {
      text-decoration: none;
      color: inherit;
    }
  </style>

  <style>
    .alerta {
      background: #ff3860;
      color: white;
      padding: 15px 20px;
      border-radius: 6px;
      margin: auto;
      margin-top: 20px;
      width: 550px;
      justify-content: center;
      font-family: Arial, sans-serif;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      animation: parpadeo 1.2s infinite;
      max-width: 400px;
    }

    .alerta strong {
      font-size: 20px;
    }

    @keyframes parpadeo {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.03);
      }

      100% {
        transform: scale(1);
      }
    }
  </style>

  <style>
    .alerta1 {
      background: #007bff;
      /* Azul intenso */
      color: white;
      padding: 15px 20px;
      border-radius: 6px;
      margin: auto;
      margin-top: 20px;
      width: 550px;
      justify-content: center;
      font-family: Arial, sans-serif;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      animation: parpadeo 1.2s infinite;
      max-width: 400px;
    }

    .alerta1 strong {
      font-size: 20px;
    }

    .fondo {
      margin-top: 15px;
      margin-bottom: 10px;
    }

    .card {
      border: none;
      border-radius: 20px !important;
      box-shadow: 0 15px 40px rgba(0, 0, 0, .35);
      background: rgba(255, 255, 255, .96);
    }

    #reloj {
      font-size: clamp(36px, 5vw, 52px);
      font-weight: 700;
      color: #111;
      margin-bottom: 20px;
      letter-spacing: 2px;
      text-shadow: 0 3px 10px rgba(0, 0, 0, .25);
    }

    .formulario-checador {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      width: 100%;
    }

    .nombre-tienda {
      text-align: center;
      color: white;
      font-size: 42px;
      font-weight: bold;
      margin-top: 25px;
      text-shadow: 2px 2px 8px black;
    }

    #tarjeta {
      width: 58%;
      height: 48px;
      font-size: 24px;
      text-align: center;
      border-radius: 10px;
      border: 2px solid #198754;
      transition: .2s;
    }

    #tarjeta:focus {
      outline: none;
      border-color: #157347;
      box-shadow: 0 0 10px rgba(25, 135, 84, .3);
    }

    input[type=submit] {
      width: 28%;
      height: 48px;
      background: #198754;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      font-weight: bold;
      transition: .25s;
    }

    input[type=submit]:hover {
      background: #157347;
      transform: translateY(-2px);
    }

    .tabla-container {
      width: 80%;
      margin-top: 15px;
      margin-left: auto;
      margin-right: auto;
      justify-content: center;
      display: flex;
    }

    .table {
      width: 80%;
      margin: 0;
      border-radius: 15px;
      overflow: hidden;
      background: #222831;
    }

    .table thead {
      background: #198754;
    }

    .table td,
    .table th {
      padding: 12px;
    }

    .table tbody tr:hover {
      background: #303841;
    }

    @keyframes parpadeo {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.03);
      }

      100% {
        transform: scale(1);
      }
    }
  </style>


  <title>Sistemas Africam Safari</title>

  <style>
    #reloj {
      font-size: 52px;
      font-weight: 700;
      color: #111;
      margin-bottom: 20px;
      letter-spacing: 2px;
      text-shadow: 0 3px 10px rgba(0, 0, 0, .25);
      /*font-size: 3em;
      /* Aqui se ajusta el tamaño del texto
      font-weight: bold;*/
    }
  </style>
</head>
<!--<body onLoad="document.forms[0].tarjeta.focus()">-->

<!--<body style="background-image: url('assets/<?php echo $id_tienda_actual; ?>.png'); background-size: cover;">-->

<body style="background:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
              url('assets/<?php echo $id_tienda_actual; ?>.png');
              background-size:cover;
              background-position:center;
              background-attachment:fixed;">
  <?php

  //include "navbar.php";


  ?>
  <!--------------------------------------- FORMULARIO  ------------------------------------------>
  <!--<h1 class="nombre-tienda"><?php echo $nombre_tienda; ?></h1>-->


  <div class="container-fluid px-3 px-md-4 mx-auto">

    <form class="form-inline" action="" method="post">

      <section>
        <div class="container py-3 fondo">
          <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">

              <div class="card shadow-2-strong">

                <div class="card-body p-4 p-md-5 text-center">

                  <?php
                  function obtenerHoraActual()
                  {
                    return date("H:i:s");
                  }
                  ?>

                  <div id="reloj">
                    <?php echo obtenerHoraActual(); ?>
                  </div>

                  <div class="formulario-checador">

                    <input
                      type="text"
                      id="tarjeta"
                      name="tarjeta"
                      required
                      autocomplete="off">

                    <input
                      type="submit"
                      name="insertar"
                      value="Aceptar">

                  </div>

                </div>

              </div>

            </div>
          </div>
        </div>
      </section>

    </form>


    <center>
      <h5><?php echo @$mensaje; ?></h5>
    </center>


    <div class="tabla-container">

      <table class="table table-dark">

        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Hora</th>
          </tr>
        </thead>

        <tbody>

          <?php

          $mostrarR = mysqli_query($conec, "SELECT j.nombre, m.hora_entrada FROM metas.registros AS m INNER JOIN reloj.empleados AS j ON m.id_empleado = j.idEmpleado WHERE m.fecha = '$fecha' AND m.id_tienda_actual = '$id_tienda_actual' ORDER BY m.hora_entrada DESC");

          $c = 1;

          while ($i = mysqli_fetch_array($mostrarR)) {

            $cont = $c++;

            if ($cont == 1) {
              $color = "<font color='#7CFC00'>";
              $ccierre = "</font>";
            } else {
              $color = "<font color='#FDFEFE'>";
              $ccierre = "</font>";
            }

          ?>

            <tr>

              <th scope="row">
                <?php echo $cont; ?>
              </th>

              <td>
                <h6>
                  <?php echo $color; ?>
                  <?php echo $i[0]; ?>
                  <?php echo $ccierre; ?>
                </h6>
              </td>

              <td>
                <?php echo $color; ?>
                <?php echo $i[1]; ?>
                <?php echo $ccierre; ?>
              </td>

            </tr>

          <?php
          }
          ?>

        </tbody>

      </table>

    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <!-- SCRIPT PARA OBTENER LA HORA -->
  <script>
    // Función para inicializar y luego actualizar el reloj cada segundo
    function inicializarReloj() {
      actualizarReloj(); // Inicializar el reloj con la hora actual
      setInterval(actualizarReloj, 1000); // Actualizar el reloj cada segundo
    }

    // Función para actualizar el reloj
    function actualizarReloj() {
      var reloj = document.getElementById('reloj');
      var fecha = new Date();
      var hora = fecha.getHours();
      var minutos = fecha.getMinutes();
      var segundos = fecha.getSeconds();
      var ampm = (hora >= 12) ? 'PM' : 'AM';

      // Convertir la hora a formato de 12 horas
      hora = (hora % 12) || 12;

      // Añade un cero delante de los minutos y segundos si son menores que 10
      minutos = minutos < 10 ? '0' + minutos : minutos;
      segundos = segundos < 10 ? '0' + segundos : segundos;

      // Actualiza el contenido del reloj
      reloj.innerHTML = hora + ':' + minutos + ':' + segundos + ' ' + ampm;
    }

    // Llama a la función de inicialización al cargar la página
    inicializarReloj();
  </script>
</body>


</html>