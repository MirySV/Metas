<?php
include "conexion.php";

session_start();
$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

$id_empleado = $_POST['id_empleado'];
$inicio = $_POST['inicio'];
$fin = $_POST['fin'];

//echo "ID: $id_empleado | Inicio: $inicio | Fin: $fin";

$colaborador = mysqli_query($conec, "SELECT r.fecha, r.hora_entrada, t.nombre FROM registros AS r INNER JOIN tiendas AS t ON r.id_tienda = t.id_tienda WHERE r.id_empleado = $id_empleado AND r.fecha BETWEEN '$inicio' AND '$fin' ORDER BY r.fecha DESC");

if(!$colaborador){
    die("Error en consulta: " . mysqli_error($conec));
}

echo "<table border='1' width='100%'>";
    echo "<tr>
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