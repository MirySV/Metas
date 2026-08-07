<?php

include 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (!isset($_SESSION['rol'])) {
    echo "No tienes permisos para acceder a esta pagina, favor de iniciar sesion";
    exit();
}

$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
$fecha = $_POST['fecha'];
$id_temporada = $_POST['id_temporada'];
$id_puente = $_POST['id_puente'];
//$id_tienda = $_POST['id_tienda'];
//$porcentaje_original = $_POST['porcentaje_original'];
//$porcentaje = $_POST['porcentaje'];
//$venta_real = $_POST['venta_real'];

if (!isset($_SESSION['rol'])) {
    echo "No tienes permisos para acceder a esta pagina, favor de iniciar sesion";
    exit();
}

if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

if (!isset($_FILES['archivo'])) {
    exit("No se recibió ningún archivo.");
}

$archivo = $_FILES['archivo']['tmp_name'];

$excel = IOFactory::load($archivo);

$hoja = $excel->getActiveSheet();

$ventas = [];

$equivalencias = [
    /*1*/
    "KARIBU" => "KARIBU",
    /*2*/
    "EXPLANADA JIRAFAS" => "EXPLANADA",
    /*3*/
    "CHAM CHAWI" => "CHAMCHAWI",
    /*4*/
    "MODULO DE NIEVES ESPECTACULOS" => "NIEVES ESPECTACULOS",
    /*5*/
    "AVENTURA AMAZONICA" => "AVENTURA AMAZONICA",
    /*6*/
    "MODULO DE FRUTAS" => "MATUNDA ESPECTACULOS",
    /*7*/
    "EXPLANADA JIRAFAS 2" => "FOTO EXPERIENCIAS",
    /*8*/
    "ZAWADI DUKA ZURI" => "ZAWADI DUKAZURI",
    /*9*/
    "ZAWADI ASIATICOS" => "ZAWADI ASIATICOS",
    /*10*/
    "MOROCCO SOUVENIRS" => "MOROCCO SOUVENIRS",
    /*11*/
    "CARRITO DE NIEVES MOROCCO" => "NIEVES MOROCCO",
    /*12*/
    "MODULO DE MICHELADAS H80" => "MICHELADAS",
    /*13*/
    "CARRITO LEONES" => "CARRITO DE LEONES",
    /*14*/
    "AFRITATOOS" => "AFRITATOOS",
    /*15*/
    "MOROCCO DULCERIA" => "MOROCCO DULCERIA",
    /*16*/
    "CARRITO DE PALOMITAS MOROCCO" => "PALOMITAS MOROCCO",
    /*17*/
    "KAR LUI" => "KARLUI",
    /*18*/
    "PENDA" => "PENDA",
    /*19*/
    "MODULO DE FRUTAS M60" => "MAHALI",
    /*"MODULO DE FRUTAS M60 CAJA" => "MAHALI",*/
    "MODULO DE FRUTAS M60 CAJA 2" => "MAHALI",
    /*20*/
    "MODULO DE NIEVES MOMBASA" => "NIEVES MOMBASA",
    /*21*/
    "KIBOKO" => "KIBOKO",
    /*22*/
    "ARAÑAS" => "KU-HU-ZU",
    /*23*/
    "FOTO SAFARI A1" => "FOTO SAFARI",
    /*"FOTO SAFARI A2" => "FOTO SAFARI",
    "FOTO SAFARI A3" => "FOTO SAFARI",
    "FOTO SAFARI A4" => "FOTO SAFARI",*/
    /*24*/
    "ZAWADI HUELLAS" => "ZAWADI HUELLAS",
    /*25*/
    "OCEANIA1" => "OCEANIA",
    /*26*/
    "AVIARIO" => "AVIARIO AUSTRALIANO",
    //Tiendas no oficiales del excel de Andy
    "ARBOTERRA" => "ARBOTERRA",
    "BAZAR TIENDAS" => "BAZAR TIENDAS",
    "PANDAS" => "PANDAS"
];


$ultimaFila = $hoja->getHighestRow();

for ($fila = 1; $fila <= $ultimaFila; $fila++) {

    $nombre = trim($hoja->getCell("A" . $fila)->getValue());
    //echo $fila . " - " . $nombre . "<br>";
    $venta  = $hoja->getCell("J" . $fila)->getCalculatedValue();

    // Ignora encabezados o filas sin cantidad de venta
    if (!is_numeric($venta)) {
        continue;
    }

    $nombre = strtoupper(trim($nombre));
    $nombre = preg_replace('/\s+/', ' ', $nombre);

    // CASOS ESPECIALES
    

    // EXPLANADA
    if ($nombre == "EXPLANADA JIRAFAS") {
        $nombre = "EXPLANADA";
    } elseif ($nombre == "EXPLANADA JIRAFAS 2" || $nombre == "EXPLANADA JIRAFAS 3") {
        $nombre = "FOTO EXPERIENCIAS";
    }

    // MAHALI
    elseif (
        $nombre == "MODULO DE FRUTAS M60" ||
        $nombre == "MODULO DE FRUTAS M60 CAJA 2"
    ) {
        $nombre = "MAHALI";
    }

    // MICHELADAS
    elseif ($nombre == "MODULO DE MICHELADAS H80") {
        $nombre = "MICHELADAS";
    }

    // FOTO SAFARI
    elseif (preg_match('/^FOTO SAFARI A\d+$/', $nombre)) {
        $nombre = "FOTO SAFARI";
    }

    // OCEANIA
    elseif ($nombre == "OCEANIA1") {
        $nombre = "OCEANIA";
    }

    // Para todos los demás quitar el número final
    elseif (
        !preg_match('/^MODULO DE MICHELADAS H80$/', $nombre) &&
        !preg_match('/^MODULO DE FRUTAS M60/', $nombre)
    ) {
        $nombre = preg_replace('/\s+\d+$/', '', $nombre);
    }

    // EQUIVALENCIAS

    if (isset($equivalencias[$nombre])) {
        echo "Antes: ".$nombre."<br>";
    $nombre = $equivalencias[$nombre];
    echo "Después: ".$nombre."<br>";
    }

    // SUMAR

    if (!isset($ventas[$nombre])) {
        $ventas[$nombre] = 0;
    }
    
    $ventas[$nombre] += $venta;
}

foreach ($ventas as $nombre => $venta) {

    // Buscar el id de la tienda
    $consulta = mysqli_query($conec, "SELECT id_tienda FROM tiendas WHERE nombre = '$nombre'");

    if ($tienda = mysqli_fetch_assoc($consulta)) {

        $id_tienda = $tienda['id_tienda'];

        // Actualizar únicamente ese día
        /*mysqli_query($conec, " UPDATE comparativos_dia AS cd INNER JOIN tiendas AS t ON cd.id_tienda = t.id_tienda SET cd.venta_real = '$venta', cd.porcentaje_comision = t.porcentaje, cd.comision = ROUND('$venta' * (t.porcentaje / 100), 2) WHERE cd.id_tienda = '$id_tienda' AND cd.fecha = '$fecha'");*/

        mysqli_query($conec,"UPDATE comparativos_dia cd INNER JOIN tiendas t ON cd.id_tienda = t.id_tienda SET cd.venta_real = '$venta',cd.porcentaje_comision = CASE WHEN cd.porcentaje_comision IS NULL OR cd.porcentaje_comision = 0 THEN t.porcentaje ELSE cd.porcentaje_comision END WHERE cd.id_tienda = '$id_tienda' AND cd.fecha = '$fecha'");

        mysqli_query($conec, "UPDATE comparativos_dia SET comision = ROUND(venta_real * (porcentaje_comision / 100),2) WHERE id_tienda='$id_tienda' AND fecha='$fecha';");

        mysqli_query($conec, "UPDATE comparativos_dia SET resultados = CASE WHEN venta_real = 0 THEN 0 ELSE ROUND(((venta_real / meta_dia) - 1) * 100, 2) END WHERE id_tienda = '$id_tienda' AND fecha = '$fecha' AND meta_dia > 0");
        
        /*mysqli_query($conec, " UPDATE comparativos_dia AS cd INNER JOIN tiendas AS t ON cd.id_tienda = t.id_tienda SET cd.porcentaje_comision = t.porcentaje, cd.comision = ROUND('$venta' * (t.porcentaje / 100), 2) WHERE cd.id_tienda = '$id_tienda' AND cd.fecha = '$fecha'");*/
        
    }
}

/*echo "<pre>";
print_r($ventas);
echo "</pre>";
exit;*/


if ($id_temporada != 0) {
    $cuandoes = "id_temporada = '$id_temporada'";
} else {
    $cuandoes = "id_puente = '$id_puente'";
}
//Actualizar la venta_total de la temporada de la vista general
$resultado_venta_total = mysqli_query($conec, "SELECT id_tienda, SUM(venta_real) AS venta_total FROM comparativos_dia WHERE " . $cuandoes . " GROUP BY id_tienda");

    while($fila = mysqli_fetch_assoc($resultado_venta_total)) {
        $id_tienda = $fila['id_tienda'];
        $venta_total = $fila['venta_total'];

        mysqli_query($conec, "UPDATE comparativos SET venta_total = '$venta_total' WHERE " . $cuandoes . " AND id_tienda = '$id_tienda'");
    }

    //Actualizar la comision de la temporada de la vista general
$resultado_comision = mysqli_query($conec, "SELECT id_tienda, SUM(comision) AS comision_total FROM comparativos_dia WHERE " . $cuandoes . " GROUP BY id_tienda");

    while($fila = mysqli_fetch_assoc($resultado_comision)) {
        $id_tienda = $fila['id_tienda'];
        $comision_total = $fila['comision_total'];

        mysqli_query($conec, "UPDATE comparativos SET comision = '$comision_total' WHERE " . $cuandoes . " AND id_tienda = '$id_tienda'");
    }

    $datos = null;
    if ($id_temporada != 0){
        $consultarPeriodo = mysqli_query($conec, "SELECT fecha_inicio, fecha_fin FROM temporadas WHERE id_temporada = '$id_temporada'");
        } else {
        $consultarPeriodo = mysqli_query($conec, "SELECT fecha_inicio, fecha_fin FROM puentes WHERE id_puente = '$id_puente'");
        }
        $periodo = mysqli_fetch_assoc($consultarPeriodo);
        
        $fecha_inicio = date('d.m.Y', strtotime($periodo['fecha_inicio']));
        $fecha_fin = date('d.m.Y', strtotime($periodo['fecha_fin']));
    

    include 'conTaquilla.php'; //Conexion a la base de datos de ibase de taquilla

              //Consulta para saber el numero de visitantes por dia consultando a la base de datos de taquilla
              $totalvisitantes = ibase_query($conn1, "SELECT SUM(ADULTOS_PV) + SUM(ADULTOS_VE) + SUM(NINOS_PV) + SUM(NINOS_VE) + SUM(BEBES_PV) + SUM(BEBES_VE) AS TOTAL_VISITANTES FROM mg_rep3('N','$fecha_inicio', '$fecha_fin',0)");

              $fila = ibase_fetch_assoc($totalvisitantes);

              $visitantesTotal = $fila['TOTAL_VISITANTES'];
              //echo $visitantesTotal;
    if ($visitantesTotal > 0) {
        $ventas = mysqli_query($conec, "SELECT id_tienda, venta_total FROM comparativos WHERE " . $cuandoes);

        while ($fila = mysqli_fetch_assoc($ventas)) {
            $vxp = round(($fila['venta_total'] / $visitantesTotal), 2);
            mysqli_query($conec, "UPDATE comparativos SET cantTempAct = '$vxp' WHERE " . $cuandoes . " AND id_tienda = '" . $fila['id_tienda'] . "'");
        }
    }

    // Obtener el PAX de Foto Experiencias para ese día
              $consulta = mysqli_query($conec, "SELECT SUM(grupos) AS total_grupos, SUM(pax) AS total_pax, SUM(visitantes) AS total_visitantes FROM tiendas_explanada WHERE fecha BETWEEN '".$periodo['fecha_inicio']."'AND '".$periodo['fecha_fin']."' AND id_tienda = 7");

              if ($datos = mysqli_fetch_assoc($consulta)) {

                $totalpax = $datos['total_pax'];
                $totalgrupos = $datos['total_grupos'];
                $totalvisitantes = $datos['total_visitantes'];
                echo "PAX: $totalpax, Grupos: $totalgrupos, Visitantes: $totalvisitantes";

                if ($id_temporada != 0) {

                // VXP Real Actual para EXPLANADA = Venta total / Total de PAX
                mysqli_query($conec, "UPDATE comparativos SET cantTempAct = ROUND(venta_total / $totalpax, 2) WHERE id_temporada = $cuandoes AND id_tienda = 2");

                // VXP Real Actual para FOTO EXPERIENCIAS = Venta total / Total de Grupos
                mysqli_query($conec, "UPDATE comparativos SET cantTempAct = ROUND(venta_total / $totalgrupos, 2) WHERE id_temporada = $cuandoes AND id_tienda = 7");
                
                }  elseif ($id_puente != 0){
                // VXP Real Actual para EXPLANADA = Venta total / Total de PAX
                mysqli_query($conec, "UPDATE comparativos SET cantTempAct = ROUND(venta_total / $totalvisitantes, 2) WHERE id_puente = $cuandoes AND id_tienda = 2");

                // VXP Real Actual para FOTO EXPERIENCIAS = Venta total / Total de Grupos
                mysqli_query($conec, "UPDATE comparativos SET cantTempAct = ROUND(venta_total / $totalgrupos, 2) WHERE id_puente = $cuandoes AND id_tienda = 7");
                }
                 
              }

              mysqli_query($conec,"UPDATE comparativos SET crecimiento = CASE WHEN cantTempActYear = 0 THEN 0 ELSE ROUND(((cantTempAct / cantTempActYear) - 1) , 2) END WHERE $cuandoes");


if ($id_temporada != 0) {
    header("Location: ventas_pax.php?id_temporada=$id_temporada&fecha=$fecha");
} else {
    header("Location: ventas_puentes.php?id_puente=$id_puente&fecha=$fecha");
}
exit();

/*echo "<pre>";
print_r($ventas);
echo "</pre>";
exit;*/