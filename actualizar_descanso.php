<?php
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método no permitido");
}

$id_registro = (int)($_POST['id_registro'] ?? 0);
$tipo_descanso = trim($_POST['tipo_descanso'] ?? '');

if ($id_registro <= 0 || $tipo_descanso == '') {
    exit("Debe seleccionar un motivo.");
}

$consulta = mysqli_prepare(
    $conec,"UPDATE registros SET tipo_descanso = ? WHERE id_registro = ? AND tipo_registro IN ('FALTA', 'NORMAL')");

mysqli_stmt_bind_param($consulta,"si",$tipo_descanso, $id_registro);

if (mysqli_stmt_execute($consulta)) {
    echo "ok";
} else {
    echo "Error al actualizar.";
}

mysqli_stmt_close($consulta);