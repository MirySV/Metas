<?php

include 'conexion.php'; //Conexion a la base de datos
var_dump($_POST);
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
//Recibe los datos del formulario de editarTExp , la fecha, los grupos y los visitantes por experiencia para guardar la informacion en la base de datos

$id_tiexp = $_GET['id_tiexp']; //Recibe el id del registro seleccionado en el formulario para editar
$fecha = $_POST['fecha'];
$grupoS = $_POST['grupoS'];
$pax= $_POST['pax'];
$visitantes= $_POST['visitantes'];

//Actualiza los datos del registro seleccionado en el formulario para editar

$actualizar_tiexp = mysqli_query($conec, "UPDATE tiendas_explanada SET fecha='$fecha', grupoS='$grupoS', pax='$pax', visitantes='$visitantes' WHERE id_tiexp='$id_tiexp'");

if ($actualizar_tiexp) {
    header('Location: actualizar_tienda.php'); //Redirecciona a la pagina de tiendas_exp.php
} else {
    echo "Error al actualizar el registro: " . mysqli_error($conec); //Muestra un mensaje de error en caso de que no se haya podido actualizar el registro
}
