<?php

include 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

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