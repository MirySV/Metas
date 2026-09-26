<?php

include "conexion.php";

date_default_timezone_set('America/Mexico_City');

$fecha = date("Y-m-d");
//$fecha = "2026-09-26";
$hora = "00:00:00";

$insertadosDescanso = 0;
$insertadosFalta = 0;

// 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo
$dia = date("N", strtotime($fecha));


/* OBTENER EMPLEADOS*/

$empleados = mysqli_query($conec,"SELECT id_empleado, id_tienda, descanso, tipo_jornada FROM empleados WHERE status = 1 AND tipo_jornada IN (0, 1)");

while ($emp = mysqli_fetch_assoc($empleados)) {

    $idEmpleado = $emp['id_empleado'];
    $idTienda = $emp['id_tienda'];
    $descanso = (int)$emp['descanso'];
    $tipoJornada = (int)$emp['tipo_jornada'];

    // Si no tiene tienda, no se procesa
    if (empty($idTienda)) {
        continue;
    }

    /*DETERMINAR SI HOY DESCANSA*/

    $esDescanso = false;
    $debeTrabajar = false;


    /* TIPO DE JORNADA 0 SOLO TRABAJA SÁBADO Y DOMINGO*/

    if ($tipoJornada == 0) {

        // Lunes a viernes → no trabaja
        if ($dia >= 1 && $dia <= 5) {
            continue;
        }

        // Sábado y domingo → trabaja
        if ($dia == 6 || $dia == 7) {
            $debeTrabajar = true;
        }
    }


    /* TIPO DE JORNADA 1 JORNADA NORMAL*/

    elseif ($tipoJornada == 1) {

        // Lunes a viernes
        if ($dia >= 1 && $dia <= 5) {

            // Descanso normal del día
            if ($descanso == $dia) {
                $esDescanso = true;
            } else {
                $debeTrabajar = true;
            }
        }

        // Sábado
        elseif ($dia == 6) {

            // 6 = descanso sábado
            // 8 = descanso sábado y domingo
            if ($descanso == 6 || $descanso == 8) {
                $esDescanso = true;
            } else {
                $debeTrabajar = true;
            }
        }

        // Domingo
        elseif ($dia == 7) {

            // 7 = descanso domingo
            // 8 = descanso sábado y domingo
            if ($descanso == 7 || $descanso == 8) {
                $esDescanso = true;
            } else {
                $debeTrabajar = true;
            }
        }
    }


    /* REVISAR SI YA EXISTE REGISTRO HOY*/

    $existe = mysqli_query($conec,"SELECT 1 FROM registros WHERE id_empleado = '$idEmpleado' AND fecha = '$fecha' LIMIT 1");


    /* GENERAR DESCANSO*/

    if ($esDescanso) {

        // Si ya tiene cualquier registro, no hacer nada
        if (mysqli_num_rows($existe) > 0) {
            continue;
        }

        mysqli_query($conec,"INSERT INTO registros (id_tienda_actual,id_empleado,fecha, hora_entrada,tipo_registro) VALUES
            ('$idTienda','$idEmpleado','$fecha','$hora','DESCANSO' )");

        $insertadosDescanso++;
    }


    /* GENERAR FALTA*/

    elseif ($debeTrabajar) {

        // Si ya tiene cualquier registro, no generar falta
        if (mysqli_num_rows($existe) > 0) {
            continue;
        }

        mysqli_query( $conec,"INSERT INTO registros(id_tienda_actual, id_empleado,fecha, hora_entrada, tipo_registro) VALUES
            ('$idTienda','$idEmpleado','$fecha','$hora','FALTA')");

        $insertadosFalta++;
    }
}

echo "Fecha: $fecha<br>";
echo "Día: $dia<br>";
echo "Descansos generados: $insertadosDescanso<br>";
echo "Faltas generadas: $insertadosFalta";