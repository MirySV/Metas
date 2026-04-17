<?php

session_start();
// Destruye todas las variables de sesión
$usuario=$_SESSION['username'];
//echo "Adios, " .$usuario; //Confirmo el usuario que ha cerrado sesion
session_destroy(); 
header("Location: login.php"); 
exit();
?>