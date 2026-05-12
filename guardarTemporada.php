<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para acceder a esta pagina";
    exit();
}

$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
  header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

if(isset($_POST['temporada']) &&
   isset($_POST['inicio']) &&
   isset($_POST['fin'])){

    $temporada = $_POST['temporada'];
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];

$inserta_temporada = mysqli_query($conec, "INSERT INTO temporadas (temporada, fecha_inicio, fecha_fin) VALUES ('$temporada', '$inicio', '$fin')");
if ($inserta_temporada) { 
    echo '<script>
            alert("La temporada se ha guardado correctamente");
            window.location.href="ventas_persona.php";
          </script>';
} else {
    echo "No se pudo guardar la temporada, por favor intente de nuevo";
}
}

?>