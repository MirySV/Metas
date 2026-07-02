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

    // ----------------------
    // CASOS ESPECIALES
    // ----------------------

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

    // ----------------------
    // EQUIVALENCIAS
    // ----------------------

    if (isset($equivalencias[$nombre])) {
        echo "Antes: ".$nombre."<br>";
    $nombre = $equivalencias[$nombre];
    echo "Después: ".$nombre."<br>";
    }

    // ----------------------
    // SUMAR
    // ----------------------

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
        mysqli_query($conec, "UPDATE comparativos_dia SET venta_real = '$venta' WHERE id_tienda = '$id_tienda' AND fecha = '$fecha'");
    }
}

/*echo "<pre>";
print_r($ventas);
echo "</pre>";
exit;*/

if ($id_temporada != 0) {
    header("Location: ventas_pax.php?id_temporada=$id_temporada&fecha=$fecha");
} else {
    header("Location: ventas_pax.php?id_puente=$id_puente&fecha=$fecha");
}
exit();

/*echo "<pre>";
print_r($ventas);
echo "</pre>";
exit;*/