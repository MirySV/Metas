<?php
//Archivo encargado de actualizar los datos de la tienda de la explanada, recibe los datos del formulario de editarTExp.php y actualiza la informacion en la base de datos


include 'conexion.php'; //Conexion a la base de datos
//var_dump($_POST);
session_start();

//Verifica si el usuario tiene una sesion iniciada y si el rol del usuario es admin, en caso de que no tenga una sesion iniciada o el rol del usuario no sea admin, se muestra un mensaje de error y se detiene la ejecucion del codigo
if (
    !isset($_SESSION['rol']) ||
    ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'supervisora')
) {
    echo "No tienes permisos para actualizar";
    exit();
}

$id_usuario = $_SESSION['idUsuario'];
$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
    exit();
}
//Recibe los datos del formulario de actualizar tienda, el nombre de la tienda y el id del colaborador para actualizar la tienda a la que pertenece el colaborador en la base de datos
$tienda = $_POST['tienda'];
$id_empleado = $_POST['id_empleado'];
$descanso_nuevo = $_POST['descanso'];

//Consulta para obtener los datos actuales del colaborador
$consulta_actual = mysqli_query($conec, "SELECT id_tienda_actual, descanso FROM empleados WHERE id_empleado='$id_empleado'");
$actual = mysqli_fetch_array($consulta_actual);

if(!$actual){
    echo "No se encontro al colaborador";
    exit();
}

$tienda_anterior = $actual['id_tienda_actual'];
$descanso_anterior = $actual['descanso'];

//Busca el id de la tienda seleccionada en el formulario para actualizar la tienda a la que pertenece el colaborador, se busca por el nombre de la tienda, ya que es el valor que se envia desde el formulario, se obtiene el id de la tienda para actualizarlo en la base de datos
$buscar_tienda = mysqli_query($conec, "SELECT id_tienda FROM tiendas WHERE nombre='$tienda'");
$fila = mysqli_fetch_array($buscar_tienda);

if(!$fila){
    echo "No se encontro la tienda";
    exit();
}

$id_tienda = $fila['id_tienda'];

//Actualiza la tienda a la que pertenece el colaborador en la base de datos, se actualiza el id de la tienda y el descanso del colaborador
    $actualizar_tienda = mysqli_query($conec, "UPDATE empleados SET id_tienda_actual='$id_tienda', descanso='$descanso_nuevo' WHERE id_empleado='$id_empleado'");

    if (!$actualizar_tienda) {
    echo '<script>
            alert("Error al actualizar la información del colaborador");
            window.location.href="colaboradores.php";
          </script>'; 
    exit();
}

//Empieza el registro del historial de cambios de tiendas, se registra el usuario que realizo el cambio, la tienda anterior y la nueva tienda a la que se cambio el colaborador, asi como la fecha y hora del cambio

if ($tienda_anterior != $id_tienda){
    
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $historial_tienda = mysqli_query($conec, "INSERT INTO historial_colaboradores (id_historial, id_usuario, id_empleado, tipo_cambio, valor_anterior, valor_nuevo, fecha_hora) VALUES (NULL,'$id_usuario', '$id_empleado', 'TIENDA', '$tienda_anterior', '$id_tienda', NOW())");

    if (!$historial_tienda) {
        echo "Error al registrar el historial de cambios: " . mysqli_error($conec);
    }
}


//Empieza el registro del historial de cambios de descansos, se registra el usuario que realizo el cambio, el descanso anterior y el nuevo descanso a la que se cambio el colaborador, asi como la fecha y hora del cambio

if ($descanso_anterior != $descanso_nuevo){
    
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $historial_descanso = mysqli_query($conec, "INSERT INTO historial_colaboradores (id_historial, id_usuario, id_empleado, tipo_cambio, valor_anterior, valor_nuevo, fecha_hora) VALUES (NULL, '$id_usuario', '$id_empleado', 'DESCANSO', '$descanso_anterior', '$descanso_nuevo', NOW())");

    if (!$historial_descanso) {
        echo "Error al registrar el cambio de descanso" . mysqli_error($conec);
    }
}

echo '<script>
        alert("La información del colaborador se ha actualizado correctamente");
        window.location.href="colaboradores.php";
      </script>';
?>