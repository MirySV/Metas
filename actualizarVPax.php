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

$id_comparativos = $_POST['id_comparativos'] ?? null;
$id_comparativosDia = $_POST['id_comparativosDia'] ?? null;

$meta = $_POST['meta'] ?? null;
$meta_dia = $_POST['meta_dia'] ?? null;

$venta_real = $_POST['venta_real'] ?? null;
$venta_total = $_POST['venta_total'] ?? null;

$resultados = $_POST['resultados'] ?? null;
$crecimiento = $_POST['crecimiento'] ?? null;

$comision = $_POST['comision'] ?? null;


if ($id_comparativos != null) {

    $actualizar = mysqli_query($conec,"UPDATE comparativos SET meta = '$meta', venta_total = '$venta_total', crecimiento = '$crecimiento',comision = '$comision' WHERE id_comparativos = '$id_comparativos'");

}

//VISTA POR DÍA 

elseif ($id_comparativosDia != null) {

    $actualizar = mysqli_query($conec,"UPDATE comparativos_dia SET meta_dia = '$meta_dia', venta_real = '$venta_real', resultados = '$resultados', comision = '$comision' WHERE id_comparativosDia = '$id_comparativosDia'");

}

else{

    die("No se recibió ningún identificador.");
}


if ($actualizar) {

    echo '<script>
            alert("La información se actualizó correctamente");
            window.history.back();
          </script>';

} else {

    echo "Error al actualizar: " . mysqli_error($conec);

}

?>