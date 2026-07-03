<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para actualizar";
    exit();
}

$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
//Recibe los datos del formulario de tiendas exp , la fecha, los grupos y los visitantes por experiencia para guardar la informacion en la base de datos

$fecha = $_POST['fecha'];
$grupos = $_POST['grupos'];
$vxe = $_POST['vxe'];
$pax=$vxe*0.65;
$id_tienda=7;

$verificar = mysqli_query($conec, "SELECT * FROM tiendas_explanada WHERE fecha = '$fecha'");

if (mysqli_num_rows($verificar) > 0) {
    echo '<script>
            alert("Ya existe un registro con esa fecha, por favor elige otra fecha");
            window.location.href="tiendas_exp.php";
          </script>';
}
//Inserta la informacion recibida del formulario de tiendas exp en la base de datos, se inserta la fecha, los grupos y los visitantes por experiencia en la tabla tiendas_explanada
$insertar_info = mysqli_query($conec, "INSERT INTO tiendas_explanada (fecha, grupos, visitantes, pax, id_tienda) VALUES ('$fecha', '$grupos', '$vxe', '$pax', '$id_tienda')");
if ($insertar_info) {
     echo '<script>
            alert("Los datos se han guardado correctamente");
            window.location.href="tiendas_exp.php";
          </script>';
} else {
    echo "No se pudo guardar la informacion, por favor intente de nuevo";
}
?>