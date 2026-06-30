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
    /*1*/"KARIBU" => "KARIBU",
    /*2*/"EXPLANADA JIRAFAS" => "EXPLANADA",
    /*3*/"CHAM CHAWI" => "CHAMCHAWI",
    /*4*/"MODULO DE NIEVES ESPECTACULOS" => "NIEVES ESPECTACULOS",
    /*5*/"AVENTURA AMAZONICA" => "AVENTURA AMAZONICA",
    /*6*/"MODULO DE FRUTAS" => "MATUNDA ESPECTACULOS",
    /*7*/"FOTO EXPERIENCIAS" => "FOTO EXPERIENCIAS",
    /*8*/"ZAWADI DUKA ZURI" => "ZAWADI DUKAZURI",
    /*9*/"ZAWADI ASIATICOS" => "ZAWADI ASIATICOS ",
    /*10*/"MOROCCO SOUVENIRS" => "MOROCCO SOUVENIRS",
    /*11*/"CARRITO DE NIEVES MOROCCO" => "NIEVES MOROCCO",
    /*12*/"MODULO DE MICHELADAS H80" => "MICHELADAS",
    /*13*/"CARRITO LEONES" => "CARRITO DE LEONES",
    /*14*/"AFRITATOOS" => "AFRITATOOS",
    /*15*/"MOROCCO DULCERIA" => "MOROCCO DULCERIA",
    /*16*/"CARRITO DE PALOMITAS MOROCCO" => "PALOMITAS MOROCCO",
    /*17*/"KAR LUI" => "KARLUI",
    /*18*/"PENDA" => "PENDA",
    /*19*/"MODULO DE FRUTAS M" => "MAHALI",
    /*20*/"MODULO DE NIEVES MOMBASA" => "NIEVES MOMBASA",
    /*21*/"KIBOKO" => "KIBOKO",
    /*22*/"ARAÑAS" => "KU-HU-ZU",
    /*23*/"FOTO SAFARI A" => "FOTO SAFARI",
    /*24*/"ZAWADI HUELLAS" => "ZAWADI HUELLAS",
    /*25*/"OCEANIA" => "OCEANIA",
    /*26*/"AVIARIO" => "AVIARIO AUSTRALIANO"];

    $ultimaFila = $hoja->getHighestRow();

for($fila=2; $fila<=$ultimaFila; $fila++){
  
        $nombre = trim($hoja->getCell('A'.$fila)->getValue());
        $venta = $hoja->getCell("J".$fila)->getCalculatedValue();

        $nombre = preg_replace('/\s+\d+$/', '', strtoupper($nombre));

    if(!isset($equivalencias[$nombre])) {
        $nombre=$equivalencias[$nombre]; // Salta a la siguiente iteración del bucle
    }
    if(!isset($ventas[$nombre])) {
        $ventas[$nombre] = 0; // Inicializa el valor si no existe
    }
    $ventas[$nombre] += $venta;

}

echo "<pre>";
print_r($ventas);
echo "</pre>";
exit;



  