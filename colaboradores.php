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


    <div id="info_colaboradores" style="overflow:auto; width:900px;height:200px;  ">
      <form action="insertarTicketNuevo.php" method="post">
        <table class="tabla_colaboradores">
          <!-- Registra el nombre de usuario automaticamente -->
          <tr>
            <td width="1000">
              Colaborador
            </td>
          </tr>
          <tr>
              <?php
              //Consulta para mostrar los colaboradores que tienen asignada una tienda, se hace un inner join entre empleados y tiendas para mostrar el nombre del colaborador de acuerdo a la tienda a la que pertenece
              $colaboradores=mysqli_query($conec,"SELECT e.nombre FROM empleados AS e INNER JOIN tiendas AS t WHERE e.id_tienda = t.id_tienda;"); 
              while($i=mysqli_fetch_array($colaboradores))
              { // ciclo para mostrar los datos de los tickets por medio de una posción

              ?>
          <tr>
            <!-- muestra el id del ticket -->
            <td width="220"><?php echo $i[0]; ?></td>
            <td>
              <!-- bóton por cada consulta dentro el ciclo while -->
              <!-- el bóton pasa parametros por medio de la url, de la consulta dentro del while, manda los parametros a la página asignarTipoBoletoAd.php -->

              <a href="actualizarTicket.php?id_ticket=<?php echo $i[0]; ?>&usuario=<?php echo $id_usuario[0]; ?>">
                <font color="#000000"><i class="fa fa-pencil" aria-hidden="true"></i></font>
              </a>
            </td>
          </tr>
          <?php } //acaba el ciclo while ?>
          </th>
          </tr>
        </table>
      </form>
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