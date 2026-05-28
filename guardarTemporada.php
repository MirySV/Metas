<?php

include 'conexion.php';
session_start();
//Validar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para acceder a esta pagina";
    exit();
}

$usuario = $_SESSION['username'];
if (!isset($usuario)) {
    header('Location: index.php');
    exit();
}

//Verificar datos
if (
    isset($_POST['tipo']) &&
    isset($_POST['inicio']) &&
    isset($_POST['fin'])
) {
    $tipo = $_POST['tipo'];
    if ($tipo == "temporada") {
        $temporada = $_POST['temporada'];
        $inicio = $_POST['inicio'];
        $fin = $_POST['fin'];

        $guardar = mysqli_query($conec, "INSERT INTO temporadas (temporada, fecha_inicio, fecha_fin, estatus) VALUES ('$temporada','$inicio','$fin', 1)");
        if ($guardar) {
            $id_temporada = mysqli_insert_id($conec);
            echo '<script>
                alert("Temporada guardada correctamente");
                window.location.href =
                "ventas_pax.php?id_temporada=' . $id_temporada . '";
            </script>';
        } else {
            echo "Error al guardar temporada";
        }
    }
    else if ($tipo == "puente") {
        $puente = $_POST['puente'];
        $inicio = $_POST['inicio'];
        $fin = $_POST['fin'];

        $guardar = mysqli_query($conec, "INSERT INTO puentes (puente, fecha_inicio, fecha_fin, estatus) VALUES('$puente','$inicio','$fin', 1)");
        if ($guardar) {
            $id_puente = mysqli_insert_id($conec);
            echo '<script>
                alert("Puente guardado correctamente");
                window.location.href =
                "ventas_pax.php?id_puente=' . $id_puente . '";
                </script>';
        } else {
            echo "Error al guardar puente";
        }
    }
} else {
    echo "Faltan datos";
}
?>