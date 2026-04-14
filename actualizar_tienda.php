<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();
$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
//Recibe los datos del formulario de actualizar tienda, el nombre de la tienda y el id del colaborador para actualizar la tienda a la que pertenece el colaborador en la base de datos
$tienda = $_POST['tienda'];
$id_empleado = $_POST['id_empleado'];

//Busca el id de la tienda seleccionada en el formulario para actualizar la tienda a la que pertenece el colaborador, se busca por el nombre de la tienda, ya que es el valor que se envia desde el formulario, se obtiene el id de la tienda para actualizarlo en la base de datos
$buscar_tienda = mysqli_query($conec, "SELECT id_tienda FROM tiendas WHERE nombre='$tienda'");
$fila = mysqli_fetch_array($buscar_tienda);
if ($fila) {
    $id_tienda = $fila['id_tienda'];
    $actualizar_tienda=mysqli_query($conec, "UPDATE empleados SET id_tienda='$id_tienda' WHERE id_empleado='$id_empleado'");
    header('Location: colaboradores.php');
} else {
    echo "La tienda seleccionada no existe";
}
?>