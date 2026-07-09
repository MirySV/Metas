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

$id_temporada = $_POST['id_temporada'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$id_comparativos = $_POST['id_comparativos'] ?? null;
$id_comparativosDia = $_POST['id_comparativosDia'] ?? null;
$id_tienda = $_POST['id_tienda'] ?? null;

$meta = $_POST['meta'] ?? null;
$meta_dia = $_POST['meta_dia'] ?? null;

$venta_real = $_POST['venta_real'] ?? null;
$venta_total = $_POST['venta_total'] ?? null;

$resultados = $_POST['resultados'] ?? null;
$porcentaje = str_replace('%', '', $_POST['porcentaje']);
$porcentaje_original = $_POST['porcentaje_original'];
$comision = str_replace('$', '', $_POST['comision']);
$crecimiento = $_POST['crecimiento'] ?? null;



if ($id_comparativos != null) {

    $actualizar = mysqli_query($conec, "UPDATE comparativos SET meta = '$meta', venta_total = '$venta_total', crecimiento = '$crecimiento',comision = '$comision' WHERE id_comparativos = '$id_comparativos'");
}

//VISTA POR DÍA 

elseif ($id_comparativosDia != null) {

    mysqli_query($conec, "UPDATE tiendas SET porcentaje = '$porcentaje' WHERE id_tienda = '$id_tienda'");

    if ($porcentaje != $porcentaje_original) {
    // Si usuario cambió el porcentaje entonces se recalcula comisión
            $comision = $venta_real * ($porcentaje / 100);
        }

    //$comision = $venta_real * ($porcentaje / 100);

    $actualizar = mysqli_query($conec, "UPDATE comparativos_dia SET meta_dia = '$meta_dia', venta_real = '$venta_real', resultados = '$resultados', porcentaje_comision = '$porcentaje', comision = '$comision' WHERE id_comparativosDia = '$id_comparativosDia'");

} else {

    die("No se recibió ningún identificador.");

}


if ($actualizar) {

    echo '<script>
            alert("La información se actualizó correctamente");
            window.location.href="ventas_pax.php?id_temporada=' . $id_temporada . '&fecha=' . $fecha . '";
        </script>';
} else {

    echo "Error al actualizar: " . mysqli_error($conec);
}
