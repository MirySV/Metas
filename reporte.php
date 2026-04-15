<?php
include "conexion.php";

session_start();
$usuario = $_SESSION['username'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
?>

$tienda = $_POST['tienda'];
$id_empleado = $_POST['id_empleado'];

$inicio = $_POST['inicio'];
$fin = $_POST['fin'];

$colaborador = mysqli_query($conec, "SELECT tienda, fecha, hora FROM registros WHERE fecha BETWEEN '$inicio' AND '$fin'");

echo "<table border='1' width='100%'>";
    echo "<tr>
        <th>Tienda</th>
        <th>Fecha</th>
        <th>Hora</th>
    </tr>";

    while($row = mysqli_fetch_assoc($query)){
    echo "<tr>
        <td>".$row['tienda']."</td>
        <td>".$row['fecha']."</td>
        <td>".$row['hora']."</td>
    </tr>";
    }

    echo "</table>";
?>