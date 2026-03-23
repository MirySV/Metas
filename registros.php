<?php
include "conexion.php";
//date_default_timezone_set('America/Mazatlan');
date_default_timezone_set('America/Mexico_City');
$fecha=date("Y-m-d");
$hora=date("H:i:s");
		

$ip = $_SERVER['REMOTE_ADDR'];
//echo $ip;
// Buscar tienda por IP
$consultaTienda = mysqli_query($conec, "SELECT * FROM tiendas WHERE ip='$ip' AND estatus=1");
$tienda = mysqli_fetch_array($consultaTienda);
if($tienda){
    $id_tienda = $tienda['id_tienda'];
    $nombre_tienda = $tienda['nombre'];
} else {
    $id_tienda = 0;
    $nombre_tienda = "Tienda desconocida";
}

if (!empty($_POST['tarjeta']))
	{
		
		
	$scar=mysqli_query($conec,"SELECT * FROM registros as r,empleados as e WHERE r.idEmpleado=e.idEmpleado AND codigo=".$_POST['tarjeta']." AND r.fecha='".$fecha."'");
	   if(mysqli_num_rows($scar)<=0)
	   {
		$empleado=mysqli_query($conec,"SELECT idEmpleado FROM empleados WHERE codigo=".$_POST['tarjeta'].""); $idEmpl=mysqli_fetch_array($empleado);
		 if(mysqli_num_rows($empleado)>0)
		 {
		 $foto=$_POST['tarjeta'].".JPG";
		 $m=rand(1,9); $s=rand(10,40);
		 $salidas=mysqli_query($conec,"SELECT DISTINCT DATE_FORMAT( h_s,'%H:%".$m.":%".$s."') FROM horarios as h,empleados as e WHERE h.idHorarios=e.idHorarios AND e.codigo=".$_POST['tarjeta']."");
		 $sal=mysqli_fetch_array($salidas);
		 $insrtRegi=mysqli_query($conec,"INSERT INTO registros 
(id_registros, id_tienda, fecha, hora_e, hora_s, idEmpleado, registros, lugar) 
VALUES 
(NULL, '".$id_tienda."', '".$fecha."', '".$hora."', '".$sal[0]."', '".$idEmpl[0]."', '', 'A');");	 
		 }
		 else
		      {
           $mensaje="<div class='alerta'>⚠️ <strong>El empleado aun no ha sido dado de alta..</strong> </div>";
			  }
			  
	   }else{
		  $mensaje="<div class='alerta1'>🔵 <strong>El empleado ya se encuentra registrado el dia de hoy.</strong></div>";
	   
	   }
	}
	
	//ALMACEN, VIGILANCIA
?>
<!doctype html>

<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/estilos.css">
    <link rel="icon" href="./assets/img/icono.ico" type="image/x-icon">
    <link rel="stylesheet" href="./assets/css/estilos.css">
    
    <style>
        .btn a{
            text-decoration: none;
            color: inherit;
        }
    </style>

   <style>
  .alerta {
    background: #ff3860;
    color: white;
    padding: 15px 20px;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    animation: parpadeo 1.2s infinite;
    max-width: 400px;
  }

  .alerta strong {
    font-size: 20px;
  }

  @keyframes parpadeo {
    0% { transform: scale(1); }
    50% { transform: scale(1.03); }
    100% { transform: scale(1); }
  }
</style>

<style>
  .alerta1 {
    background: #007bff;    /* Azul intenso */
    color: white;
    padding: 15px 20px;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    animation: parpadeo 1.2s infinite;
    max-width: 400px;
  }

  .alerta1 strong {
    font-size: 20px;
  }

  @keyframes parpadeo {
    0% { transform: scale(1); }
    50% { transform: scale(1.03); }
    100% { transform: scale(1); }
  }
</style>


    <title>Sistemas Africam Safari</title>

    <style>
            #reloj {
      font-size: 3em; /* Aqui se ajusta el tamaño del texto*/
      font-weight: bold;
    }
  
    </style>
  </head>
  <!--<body onLoad="document.forms[0].tarjeta.focus()">-->
 <body style="background-image: url('assets/dulceria/<?php echo $nombre_tienda; ?>.png'); background-size: cover;">

  <?php
    
    //include "navbar.php";

  
?>
  <!--------------------------------------- FORMULARIO  ------------------------------------------>
 <!-- <h3><?php echo $nombre_tienda; ?></h3> -->

        <div class="container px-1 px-sm-5 mx-auto">
    <form class="form-inline" action="" method="post">

  <section class="">
    <div class="container py-3 h-100 fondo">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-2-strong" style="border-radius: 1rem;">
            
            
<div class="card-body p-5 text-center">
<!--<img src="fotografias/<?php // echo $foto; ?>" width="45%" alt=""/> -->

  
  <?php
                  function obtenerHoraActual() {
                  return date("H:i:s");
                  }
              ?>
  
  <div id="reloj">
                  <?php echo obtenerHoraActual(); ?>
              </div>
              
            <div class="mb-1">
             <!--   <label for="" class="form-label">MATRICULA</label>-->
                <input type="text"  id="tarjeta" name="tarjeta" onLoad = "enfocar()" required>
                <input type="submit" name="insertar" value="Aceptar">
              </div>

        </div>
        </div>
      </div>
    </div>
  </div>
</section>
</form>
<!--<center><h5><?php echo $mensaje;?></h5></center>-->
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Nombre</th>
      <th scope="col">Hora</th>
   </tr>
  </thead>
  <tbody>
 <?php 
 $mostrarR=mysqli_query($conec,"SELECT e.nombre,hora_e FROM registros as r,empleados as e WHERE r.idEmpleado=e.idEmpleado AND r.fecha='".$fecha."' AND lugar='A' ORDER BY hora_e DESC");
 $c=1;
 while($i=mysqli_fetch_array($mostrarR))
 {
 $cont=$c++;
 if($cont==1)
 {
	$color = "<font color='#7CFC00'>";
            $ccierre = "</font>";
 }else 
      {
        $color = "<font color=' #FDFEFE'>";
        $ccierre = "</font>";
	  }
 
 ?> 
      <tr>
      <th scope="row"><?php echo $cont; ?></th>
      <td><h6><?php echo $color;?><?php echo $i[0];?><?php echo $ccierre;?></h6></td>
      <td><?php echo $color;?><?php echo $i[1];?><?php echo $ccierre;?></td>
    
    </tr>
  <?php
 }
  ?>
    
     </tbody>
</table>
        
    
</div>


</form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- SCRIPT PARA OBTENER LA HORA -->
    <script>
    // Función para inicializar y luego actualizar el reloj cada segundo
    function inicializarReloj() {
      actualizarReloj(); // Inicializar el reloj con la hora actual
      setInterval(actualizarReloj, 1000); // Actualizar el reloj cada segundo
    }

    // Función para actualizar el reloj
    function actualizarReloj() {
      var reloj = document.getElementById('reloj');
      var fecha = new Date();
      var hora = fecha.getHours();
      var minutos = fecha.getMinutes();
      var segundos = fecha.getSeconds();
      var ampm = (hora >= 12) ? 'PM' : 'AM';

      // Convertir la hora a formato de 12 horas
      hora = (hora % 12) || 12;

      // Añade un cero delante de los minutos y segundos si son menores que 10
      minutos = minutos < 10 ? '0' + minutos : minutos;
      segundos = segundos < 10 ? '0' + segundos : segundos;

      // Actualiza el contenido del reloj
      reloj.innerHTML = hora + ':' + minutos + ':' + segundos + ' ' + ampm;
    }

    // Llama a la función de inicialización al cargar la página
    inicializarReloj();
  </script>
  </body>

  
</html>

