<<?php
//Archivo encargado de actualizar los datos de la tienda de la explanada, recibe los datos del formulario de editarTExp.php y actualiza la informacion en la base de datos


include 'conexion.php'; //Conexion a la base de datos
//var_dump($_POST);
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
//Recibe los datos del formulario de actualizar tienda, el nombre de la tienda y el id del colaborador para actualizar la tienda a la que pertenece el colaborador en la base de datos
$tienda = $_POST['tienda'];
$id_empleado = $_POST['id_empleado'];


//Busca el id de la tienda seleccionada en el formulario para actualizar la tienda a la que pertenece el colaborador, se busca por el nombre de la tienda, ya que es el valor que se envia desde el formulario, se obtiene el id de la tienda para actualizarlo en la base de datos
$buscar_tienda = mysqli_query($conec, "SELECT id_tienda FROM tiendas WHERE nombre='$tienda'");
$fila = mysqli_fetch_array($buscar_tienda);

if ($fila) {

    $id_tienda = $fila['id_tienda'];
    $descanso = $_POST['descanso'];

    $actualizar_tienda = mysqli_query($conec, "UPDATE empleados SET id_tienda_actual='$id_tienda', descanso='$descanso' WHERE id_empleado='$id_empleado'");

    if ($actualizar_tienda) {
        echo '<script>
                alert("La tienda del colaborador se ha actualizado correctamente");
                window.location.href="colaboradores.php";
              </script>';
    } else {
        echo "Error al actualizar: " . mysqli_error($conec);
    }

} else {
    echo "La tienda seleccionada no existe";
}
?>