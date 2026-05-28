<?php

include 'conexion.php';

if(isset($_POST['id_temporada'])){

    $id_temporada = $_POST['id_temporada'];

    $cerrar = mysqli_query($conec,"UPDATE temporadas SET estatus = 0 WHERE id_temporada = '$id_temporada'");

    if($cerrar){
        echo '
        <script>
            alert("Temporada cerrada");
            window.location.href="index.php";
        </script>
        ';
    }
    else{
        echo "Error al cerrar temporada";
    }
}
?>