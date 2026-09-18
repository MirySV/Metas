<?php

include "conexion.php";

date_default_timezone_set('America/Mexico_City');

$fecha = "2026-09-11";
$hora = "00:00:00";

$insertadosDescanso = 0;
$insertadosFalta = 0;

// 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo
$dia = date("N", strtotime($fecha));

/*GENERAR DESCANSOS*/

$empleadosDescanso = mysqli_query($conec, "SELECT id_empleado, id_tienda FROM empleados WHERE descanso = '$dia' AND tipo_jornada = 1 AND status = 1");

while ($emp = mysqli_fetch_assoc($empleadosDescanso)) {

    $idEmpleado = $emp['id_empleado'];
    $idTienda   = $emp['id_tienda'];

    if (empty($idTienda)) {
        continue;
    }

    // Revisa si hay registro para el dia de hoy
    $existe = mysqli_query($conec, "SELECT 1 FROM registros WHERE id_empleado = '$idEmpleado' AND fecha = '$fecha' LIMIT 1");

    if (mysqli_num_rows($existe) > 0) {
        continue;
    }

    mysqli_query($conec, "INSERT INTO registros (id_tienda_actual, id_empleado, fecha, hora_entrada, tipo_registro) VALUES ('$idTienda','$idEmpleado','$fecha','$hora','DESCANSO')");

    $insertadosDescanso++;
}


/* GENERAR FALTAS*/

$empleadosTrabajo = mysqli_query($conec, "SELECT id_empleado, id_tienda FROM empleados WHERE status = 1 AND tipo_jornada = 1 AND descanso <> '$dia'");

while ($emp = mysqli_fetch_assoc($empleadosTrabajo)) {

    $idEmpleado = $emp['id_empleado'];
    $idTienda   = $emp['id_tienda'];

    if (empty($idTienda)) {
        continue;
    }

    // Si NO existe ningún registro del día (ni reloj general ni reloj tienda)
    $existe = mysqli_query($conec, "SELECT 1 FROM registros WHERE id_empleado = '$idEmpleado'AND fecha = '$fecha'LIMIT 1");

    if (mysqli_num_rows($existe) == 0) {

        mysqli_query($conec, "INSERT INTO registros(id_tienda_actual, id_empleado, fecha, hora_entrada, tipo_registro)VALUES('$idTienda','$idEmpleado','$fecha','$hora','FALTA')
        ");

        $insertadosFalta++;
    }
}

echo "Fecha: $fecha<br>";
echo "Descansos generados: $insertadosDescanso<br>";
echo "Faltas generadas: $insertadosFalta";