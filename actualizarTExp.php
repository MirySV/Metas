<?php
//Archivo encargado de actualizar los datos de la tienda de la explanada, recibe los datos del formulario de editarTExp.php y actualiza la informacion en la base de datos


include 'conexion.php'; //Conexion a la base de datos
//var_dump($_POST);
session_start();
var_dump($_POST);
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

$id_tiexp = $_POST['id_tiexp']; //Recibe el id del registro seleccionado en el formulario para editar
$fecha = $_POST['fecha'];
$grupos = $_POST['grupos'];
$pax = $_POST['pax'];
$visitantes= $_POST['visitantes'];

    $actualizar_tienda = mysqli_query($conec, "UPDATE tiendas_explanada SET fecha='$fecha', grupos='$grupos', pax='$visitantes' * 0.65, visitantes='$visitantes' WHERE id_tiexp='$id_tiexp'");

    if ($actualizar_tienda) {
        echo '<script>
                alert("La informcion se ha actualizado correctamente");
                window.location.href="tiendas_exp.php";
              </script>';
    } else {
        echo "Error al actualizar el registro: " . mysqli_error($conec);
    }
?>