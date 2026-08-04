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

$id_temporada = $_POST['id_temporada'] ?? 0;
$id_puente    = $_POST['id_puente'] ?? 0;
$fecha = $_POST['fecha'] ?? null;
$id_comparativos = $_POST['id_comparativos'] ?? null;
$id_comparativosDia = $_POST['id_comparativosDia'] ?? null;
$id_tienda = $_POST['id_tienda'] ?? null;

$meta = $_POST['meta'] ?? null;
$meta_dia = $_POST['meta_dia'] ?? null;
$meta_dia_original = $_POST['meta_dia_original'];

$venta_real = $_POST['venta_real'] ?? null;
$venta_real_original = $_POST['venta_real_original'];
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

//Aqui empieza el comentario general
    mysqli_query($conec, "UPDATE tiendas SET porcentaje = '$porcentaje' WHERE id_tienda = '$id_tienda'");

    if ($porcentaje != $porcentaje_original) {
    //Si usuario cambió el porcentaje entonces se recalcula comisión
            $comision = $venta_real * ($porcentaje / 100);
        }

        if ($venta_real != $venta_real_original || $meta_dia != $meta_dia_original) {

        if ($venta_real == 0) {
            $resultados = 0;
        } else {
            $resultados = round((($venta_real / $meta_dia) - 1) * 100, 2);
        }
    }


        //Aqui empiezan los comentarios particulares
    //$comision = $venta_real * ($porcentaje / 100);

    /*$actualizar = mysqli_query($conec, "UPDATE comparativos_dia SET meta_dia = '$meta_dia', venta_real = '$venta_real', porcentaje_comision = '$porcentaje' WHERE id_comparativosDia = '$id_comparativosDia'");

    mysqli_query($conec, "UPDATE comparativos_dia SET comision = ROUND(venta_real * (porcentaje_comision / 100),2) WHERE id_tienda='$id_tienda' AND fecha='$fecha'");

    if ($actualizar) {
        mysqli_query($conec, "UPDATE comparativos_dia SET resultados = CASE WHEN venta_real = 0 THEN 0 ELSE ROUND(((venta_real / meta_dia) - 1) * 100, 2) END WHERE id_temporada = '$id_temporada' AND fecha = '$fecha' AND meta_dia > 0");
}Aqui termina el comentario particular*/

//Aqui empieza el comentario general 
$actualizar = mysqli_query($conec, "UPDATE comparativos_dia SET meta_dia = '$meta_dia', venta_real = '$venta_real', resultados = '$resultados', porcentaje_comision = '$porcentaje', comision = '$comision' WHERE id_comparativosDia = '$id_comparativosDia'");

/* Comentario particular mysqli_query($conec, "UPDATE comparativos_dia SET resultados = CASE WHEN venta_real = 0 THEN 0 ELSE ROUND(((venta_real / meta_dia) - 1) * 100, 2) END WHERE id_temporada = '$id_temporada' AND fecha = '$fecha' AND meta_dia > 0");*/

} else {

    die("No se recibió ningún identificador.");

}


if ($actualizar) {

    if ($id_puente != 0) {
        $pagina = "ventas_puentes.php?id_puente=$id_puente&fecha=$fecha";
    } else {
        $pagina = "ventas_pax.php?id_temporada=$id_temporada&fecha=$fecha";
    }

    echo '<script>
            alert("La información se actualizó correctamente");
            window.location.href="' . $pagina . '";
          </script>';
}
else {

    echo "Error al actualizar: " . mysqli_error($conec);

}
