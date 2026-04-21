<?php
include "conexion.php";

session_start();
$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; 
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CDN Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icoons de Boostrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Favicon (Icono de la pagina web)-->
    <link rel="shortcut icon" href="./assets/img/shop.svg" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!-- Tipografia Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Averia+Libre:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Glory:ital,wght@0,100..800;1,100..800&family=Macondo&family=Marcellus&display=swap" rel="stylesheet">
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .tabla_colaboradores {
            width: 100%;
            margin: 0 auto;
            margin-top: 30px;
            border-collapse: collapse;
            /*font-size: 12px;*/
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #77240f81;
            padding: 10px;
            font-size: 14px;
        }

        .encabezado {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: left;
      background-color: #77240f;
      color: white;
    }
    </style>

<?php

$id_empleado = $_POST['id_empleado'];
$inicio = $_POST['inicio'];
$fin = $_POST['fin'];

//echo "ID: $id_empleado | Inicio: $inicio | Fin: $fin";

$colaborador = mysqli_query($conec, "SELECT r.fecha, r.hora_entrada, t.nombre FROM registros AS r INNER JOIN tiendas AS t ON r.id_tienda = t.id_tienda WHERE r.id_empleado = $id_empleado AND r.fecha BETWEEN '$inicio' AND '$fin' ORDER BY r.fecha DESC");

if(!$colaborador){
    die("Error en consulta: " . mysqli_error($conec));
}

echo "<table class='tabla_colaboradores'>";
    echo "<tr class='encabezado'>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Tienda</th>
    </tr>";

    while($row = mysqli_fetch_assoc($colaborador)){
    echo "<tr>
        <td>".$row['fecha']."</td>
        <td>".$row['hora_entrada']."</td>
        <td>".$row['nombre']."</td>
    </tr>";
    }

    echo "</table>";
?>