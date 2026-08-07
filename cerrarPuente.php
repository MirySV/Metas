<?php

include 'conexion.php';

if(isset($_POST['id_puente'])){

    $id_puente = $_POST['id_puente'];

    $cerrar = mysqli_query($conec,"UPDATE puentes SET estatus = 0 WHERE id_puente = '$id_puente'");

    if($cerrar){
        echo '
        <script>
            alert("Puente cerrado");
            window.location.href="index.php";
        </script>
        ';
    }
    else{
        echo "Error al cerrar puente";
    }
}
?>