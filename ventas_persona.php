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

    tr:hover {
      background-color: #de853136;
    }

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
      /*font-family: "Glory", sans-serif;*/
    }

    #tiendas {
      padding: 5px;
      font-size: 14px;
      /*font-family: "Glory", sans-serif;*/
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
      border: 1px ;
      border-radius: 4px;
      padding: 4px 5px;
      /*font-family: "Glory", sans-serif;*/
      margin: 0px 15px 0px 7px;
    }

    .btnmodal{
      border-radius: 4px;
    }
    
  </style>
  <title>Tiendas Africam Safari</title>
</head>

<body>
  <header class="navbarvxp">
    <a href="login.php">
      <img src="./assets/img/logo.png" width="130px" />
    </a>
      <a class="text-black m-0" id="nombrepagvxp" href="index.php">Ventas por Persona</a>
    <div class="cerrarsesion">
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main>
    
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