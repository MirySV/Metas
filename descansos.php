<?php
include "conexion.php";

date_default_timezone_set('America/Mexico_City');

$fecha = date("Y-m-d");
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

$dia = $dias[date("l", strtotime($fecha))];

// Buscar empleados que descansan hoy
$empleados = mysqli_query($conec,"SELECT id_empleado, id_tienda FROM empleados WHERE descanso='$dia' AND tipo_jornada=1 AND status=1");

while ($emp = mysqli_fetch_assoc($empleados)) {

    $idEmpleado = $emp['id_empleado'];
    $idTienda = $emp['id_tienda'];

    // Verificar que tenga tienda asignada
    if (empty($idTienda)) {
        continue;
    }

    // Verificar si ya existe un registro para este empleado hoy
    $existe = mysqli_query( $conec,"SELECT 1 FROM registros WHERE id_empleado='$idEmpleado' AND fecha='$fecha' LIMIT 1");

    if (mysqli_num_rows($existe) > 0) {
        continue;
    }

    // Insertar descanso usando la tienda ASIGNADA del empleado
    mysqli_query($conec,"INSERT INTO registros (id_tienda_actual, id_empleado, fecha, hora_entrada, tipo_registro) VALUES ('$idTienda', '$idEmpleado', '$fecha', '$hora', 'DESCANSO')");

    $insertados++;
}

if ($insertados > 0) {
    echo "Se insertaron $insertados registros de descanso para el día $dia ($fecha).";
} else {
    echo "No hubo registros de descanso por generar para el día $dia ($fecha).";
}