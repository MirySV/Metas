<?php
require 'conexion.php';
    session_start();

    //recupera los datos del formulario
    $usuario=$_POST['usuario'];
    $contraseña=$_POST['contraseña'];

    //consulta para verificar si el usuariO existe en la base de datos
    $consulta="SELECT COUNT(*) as contar FROM usuarios where username='$usuario' and contraseña='$contraseña'";
    $resultado=mysqli_query($conec,$consulta);
    $filas=mysqli_fetch_array($resultado);

    //almacena el id y el nombre del usuario en variables de sesion
    $_SESSION['idUsuario']=$filas['id']; 
    $_SESSION['nombreUsuario']=$filas['username'];
    
    //si el usuario existe redirige a la pagina principal, de lo contrario muestra un mensaje de error
    if($filas['contar']>0){
        $_SESSION['username']=$usuario;
        header("location: index.php");
    }else{
        echo'<script type="text/javascript">alert("DATOS INCORRECTOS");
                window.location.href="login.php";
            </script>';
        }
?>