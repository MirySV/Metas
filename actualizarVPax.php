<?php

include 'conexion.php';

session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para actualizar";
    exit();
}

$usuario = $_SESSION['username'];

if (!isset($usuario)) {
    header('Location: index.php');
    exit();
}

$id_comparativos = $_POST['id_comparativos'];
$venta_real = $_POST['venta_real'];
$resultados = $_POST['resultados'];
$comision = $_POST['comision'];

$consulta = mysqli_query($conec,"SELECT cantTempActYear, cantTempAnterior, puenteAnterior FROM temp_comparativos WHERE id_comparativos = '$id_comparativos'");

$datos = mysqli_fetch_assoc($consulta);

$meta = round(($datos['cantTempActYear'] + $datos['cantTempAnterior'] + $datos['puenteAnterior']) / 3, 2);

$actualizar = mysqli_query($conec,"UPDATE temp_comparativos SET meta = '$meta', venta_real = '$venta_real', resultados = '$resultados',comision = '$comision' WHERE id_comparativos = '$id_comparativos'");

if ($actualizar) {

    echo '<script>
            alert("La información se actualizó correctamente");
            window.history.back();
          </script>';

} else {

    echo "Error al actualizar: " . mysqli_error($conec);
}

?>