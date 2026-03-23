<?php
$ip = $_SERVER['REMOTE_ADDR'];

//identifica si el usuario esta en localhost o no, si no lo esta muestra su ip y con ella identifica la tienda 
if ($ip === '::1' || $ip === '127.0.0.1') {
    echo "Estás en localhost";
} else {
    echo "Tu IP es: " . $ip;
}
?>