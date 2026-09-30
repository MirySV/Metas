<?php

include "conexion.php";

session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (
    !isset($_SESSION['rol']) ||
    ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'supervisora')
) {
    echo "No tienes permisos para actualizar";
    exit();
}

$id_registro = (int)($_POST['id_registro'] ?? 0);
$tipo_descanso = trim($_POST['tipo_descanso'] ?? '');

if ($id_registro <= 0 || $tipo_descanso == '') {
    exit("Debe seleccionar un motivo.");
}


/*OBTENER DATOS ACTUALES DEL REGISTRO*/

$consulta_datos = mysqli_prepare(
    $conec,"SELECT id_empleado, tipo_descanso FROM registros WHERE id_registro = ? AND tipo_registro IN ('FALTA', 'NORMAL')");

mysqli_stmt_bind_param($consulta_datos, "i", $id_registro);

mysqli_stmt_execute($consulta_datos);

$resultado = mysqli_stmt_get_result($consulta_datos);
$datos = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($consulta_datos);


if (!$datos) {
    exit("No se encontró el registro.");
}


$id_empleado = $datos['id_empleado'];
$tipo_descanso_anterior = $datos['tipo_descanso'];


/* ACTUALIZAR MOTIVO DEL DESCANSO*/

$consulta = mysqli_prepare(
    $conec,"UPDATE registros SET tipo_descanso = ? WHERE id_registro = ? AND tipo_registro IN ('FALTA', 'NORMAL')");

mysqli_stmt_bind_param($consulta, "si", $tipo_descanso, $id_registro);

if (mysqli_stmt_execute($consulta)) {

    /* REGISTRAR CAMBIO EN HISTORIAL*/

    if ($tipo_descanso_anterior != $tipo_descanso) {

        $id_usuario = $_SESSION['idUsuario'] ?? 0;

        $historial = mysqli_prepare($conec,"INSERT INTO historial_colaboradores (id_historial, id_usuario, id_empleado, tipo_cambio, valor_anterior, valor_nuevo, fecha_hora) VALUES (NULL,?,?,'MOTIVO',?,?,NOW())");

        mysqli_stmt_bind_param($historial,"iiss",$id_usuario,$id_empleado, $tipo_descanso_anterior, $tipo_descanso);

        if (!mysqli_stmt_execute($historial)) {
            echo "El motivo se actualizó, pero ocurrió un error al registrar el historial.";
            mysqli_stmt_close($historial);
            mysqli_stmt_close($consulta);
            exit();
        }

        mysqli_stmt_close($historial);
    }

    echo "ok";

} else {

    echo "Error al actualizar.";
}

mysqli_stmt_close($consulta);