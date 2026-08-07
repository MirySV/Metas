<?php
include "conexion.php";
//date_default_timezone_set('America/Mazatlan');
date_default_timezone_set('America/Mexico_City');
$fecha = date("Y-m-d");
//$fecha = "2026-08-08";
$hora = "00:00:00";

$insertados = 0;
// Obtener el día de la semana
$dias = [
    "Monday"    => "LUNES",
    "Tuesday"   => "MARTES",
    "Wednesday" => "MIERCOLES",
    "Thursday"  => "JUEVES",
    "Friday"    => "VIERNES",
    "Saturday"  => "SABADO",
    "Sunday"    => "DOMINGO"
];

//$dia = $dias[date("l")];
$dia = $dias[date("l", strtotime($fecha))];

$empleados = mysqli_query($conec, "SELECT * FROM empleados WHERE descanso='$dia' AND tipo_jornada=1 AND status=1");

while ($emp = mysqli_fetch_assoc($empleados)) {

    $idEmpleado = $emp['id_empleado'];

$existe = mysqli_query($conec, "SELECT 1 FROM registros WHERE id_empleado='$idEmpleado' AND fecha='$fecha'");

if (mysqli_num_rows($existe) > 0) {
    continue;
}

$ultima = mysqli_query($conec, "SELECT id_tienda FROM registros WHERE id_empleado='$idEmpleado' AND tipo_registro = 'NORMAL' ORDER BY fecha DESC, hora_entrada DESC LIMIT 1");

if (mysqli_num_rows($ultima) == 0) {
    continue;
}

$t = mysqli_fetch_assoc($ultima);

$idTienda = $t['id_tienda'];

mysqli_query($conec, "INSERT INTO registros(id_tienda,id_empleado,fecha,hora_entrada,tipo_registro)VALUES('$idTienda','$idEmpleado','$fecha','$hora','DESCANSO')");

    $insertados++;

}

if ($insertados > 0) {
    echo "Se insertaron $insertados registros de descanso para el día $dia ($fecha).";
} else {
    echo "No hubo registros de descanso por generar para el día $dia ($fecha).";
}