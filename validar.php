<?php
require 'conexion.php';
session_start();

//recupera los datos del formulario
$usuario = $_POST['usuario'];
$contraseña = $_POST['contraseña'];

//consulta para verificar si el usuariO existe en la base de datos
$consulta = "SELECT * FROM usuarios WHERE username='$usuario' AND contraseña='$contraseña'";
$resultado = mysqli_query($conec, $consulta);

if (mysqli_num_rows($resultado) > 0) {

    $filas = mysqli_fetch_assoc($resultado);

    $_SESSION['username'] = $filas['username'];
    $_SESSION['rol'] = $filas['rol'];
    $_SESSION['idUsuario'] = $filas['id_usuario'];
    $_SESSION['nombreUsuario'] = $filas['nombre'];

    header("location: index.php");
} else {
    echo '<script>
            alert("DATOS INCORRECTOS");
            window.location.href="login.php";
          </script>';
}
