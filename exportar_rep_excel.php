<?php

/*
    Evita que cualquier warning/notice/deprecated (o espacio en blanco
    accidental de algún archivo incluido) se mezcle con el binario del
    xlsx y lo corrompa. Los errores reales se registran en el log de
    PHP en vez de imprimirse en pantalla.
*/
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

include "conexion.php";
require "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

date_default_timezone_set('America/Mexico_City');

$inicio = $_GET['inicio'] ?? date("Y-m-01");
$fin    = $_GET['fin'] ?? date("Y-m-d");
$tienda = $_GET['tienda'] ?? "todas";

$fechaInicio = new DateTime($inicio);
$fechaFin    = new DateTime($fin);

/* ==========================
   CALCULAR QUINCENA
========================== */

$numeroQuincena = ceil((int)$fechaInicio->format("z") / 15);
$anio = $fechaInicio->format("Y");

/* ==========================
   CREAR EXCEL
========================== */

$excel = new Spreadsheet();
$hoja = $excel->getActiveSheet();
$hoja->setTitle("PRENOMINA");

/* ==========================
   COLORES
========================== */

$COLORES = [

    "AZUL"      => "0070C0",
    "VERDE"     => "00B050",
    "ROJO"      => "FF0000",
    "AMARILLO"  => "FFC000",
    "GRIS"      => "D9D9D9",
    "NARANJA"   => "F4B183",
    "MORADO"    => "D9A5FF",
    "CIAN"      => "00B0F0",
    "BLANCO"    => "FFFFFF",
    "NEGRO"     => "000000",

    // Colores extra para la leyenda de asistencia
    "AMARILLO_FES" => "FFFF00",
    "AZUL_CLARO"   => "9DC3E6",
    "ROSA"         => "FF66CC",
    "VERDE_CLARO"  => "C6E0B4",
    "NARANJA_HO"   => "ED7D31",
    "CAFE"         => "9C6B30",
    "CAFE_OSCURO"  => "7F5B3E"

];

/* ==========================================================
   CATALOGO DE CODIGOS DE ASISTENCIA
   ----------------------------------------------------------
   "tipo"           => valor de registros.tipo_registro
   "subtipo"        => valor de registros.tipo_descanso (opcional)
   "color"          => color de fondo de la celda
   "fuente"         => color de la fuente (por defecto negro)
   "contador"       => llave del arreglo $contador que se incrementa
                        (si no aplica, se deja null y solo se pinta la celda)

   *** AJUSTA LA COLUMNA "tipo" / "subtipo" SI EN TU BD USAS OTROS
       NOMBRES PARA ESTOS ESTADOS ***
========================================================== */

$CODIGOS = [

    "A"   => ["tipo"=>"NORMAL",           "subtipo"=>null,        "color"=>$COLORES["BLANCO"],      "fuente"=>"000000", "contador"=>"A"],
    "D"   => ["tipo"=>"DESCANSO",         "subtipo"=>null,        "color"=>$COLORES["BLANCO"],      "fuente"=>"000000", "contador"=>"D"],
    "DT"  => ["tipo"=>"DESCANSO",         "subtipo"=>"TRABAJADO", "color"=>$COLORES["AZUL"],        "fuente"=>"FFFFFF", "contador"=>"DT"],
    "F"   => ["tipo"=>"FALTA",            "subtipo"=>null,        "color"=>$COLORES["ROJO"],        "fuente"=>"FFFFFF", "contador"=>"F"],
    "FES" => ["tipo"=>"FESTIVO",          "subtipo"=>null,        "color"=>$COLORES["AMARILLO_FES"],"fuente"=>"000000", "contador"=>"FES"],
    "PD"  => ["tipo"=>"PRIMA_DOMINICAL",  "subtipo"=>null,        "color"=>$COLORES["VERDE"],       "fuente"=>"FFFFFF", "contador"=>"PD"],
    "PCG" => ["tipo"=>"PERMISO_CON_GOCE", "subtipo"=>null,        "color"=>$COLORES["NARANJA"],     "fuente"=>"000000", "contador"=>"PCG"],
    "PSG" => ["tipo"=>"PERMISO_SIN_GOCE", "subtipo"=>null,        "color"=>$COLORES["MORADO"],      "fuente"=>"000000", "contador"=>"PSG"],
    "INC" => ["tipo"=>"INCAPACIDAD",      "subtipo"=>null,        "color"=>$COLORES["AMARILLO"],    "fuente"=>"000000", "contador"=>"INC"],
    "V"   => ["tipo"=>"VACACIONES",       "subtipo"=>null,        "color"=>$COLORES["CAFE"],        "fuente"=>"FFFFFF", "contador"=>"V"],

    // Codigos que se pintan en el calendario pero no tienen columna
    // propia en el resumen (segun la plantilla que compartiste)
    "R"   => ["tipo"=>"RETARDO",          "subtipo"=>null,        "color"=>$COLORES["AZUL_CLARO"],  "fuente"=>"000000", "contador"=>null],
    "M"   => ["tipo"=>"MATERNIDAD",       "subtipo"=>null,        "color"=>$COLORES["ROSA"],        "fuente"=>"000000", "contador"=>null],
    "P"   => ["tipo"=>"PATERNIDAD",       "subtipo"=>null,        "color"=>$COLORES["VERDE_CLARO"], "fuente"=>"000000", "contador"=>null],
    "HO"  => ["tipo"=>"HOME_OFFICE",      "subtipo"=>null,        "color"=>$COLORES["NARANJA_HO"],  "fuente"=>"000000", "contador"=>null],
    "CUM" => ["tipo"=>"CUMPLEAÑOS",       "subtipo"=>null,        "color"=>$COLORES["CIAN"],        "fuente"=>"000000", "contador"=>null],
    "AA"  => ["tipo"=>"DOBLE_ASISTENCIA", "subtipo"=>null,        "color"=>$COLORES["CAFE_OSCURO"], "fuente"=>"FFFFFF", "contador"=>null],

];

/*
    Indice inverso para encontrar rapidamente el codigo a partir
    de (tipo_registro, tipo_descanso)
*/
function buscarCodigo($CODIGOS, $tipoRegistro, $tipoDescanso){

    foreach($CODIGOS as $codigo => $def){

        if($def["tipo"] !== $tipoRegistro){
            continue;
        }

        if($def["subtipo"] === null){
            // Coincide con cualquier subtipo (incluyendo null)
            // salvo que exista otro codigo mas especifico con el
            // mismo tipo (ej. DESCANSO / DESCANSO+TRABAJADO)
            if($tipoDescanso === null || $tipoDescanso === ""){
                return $codigo;
            }
            continue;
        }

        if($def["subtipo"] === $tipoDescanso){
            return $codigo;
        }
    }

    // Si no hubo match exacto, intenta solo por tipo_registro
    foreach($CODIGOS as $codigo => $def){
        if($def["tipo"] === $tipoRegistro){
            return $codigo;
        }
    }

    return null;
}

/* ==========================
   ENCABEZADO
========================== */

$hoja->mergeCells("B1:C1");
$hoja->setCellValue("B1","DIRECCION DE TIENDAS");

$hoja->getStyle("B1:C1")->applyFromArray([
    "font"=>[
        "bold"=>true,
        "color"=>["rgb"=>"FFFFFF"]
    ],
    "alignment"=>[
        "horizontal"=>Alignment::HORIZONTAL_CENTER
    ],
    "fill"=>[
        "fillType"=>Fill::FILL_SOLID,
        "startColor"=>["rgb"=>$COLORES["AZUL"]]
    ]
]);

$hoja->setCellValue("B2","QUINCENA No. ".$numeroQuincena." ".$anio);

/* ==========================
   COLUMNAS FIJAS
========================== */

$hoja->setCellValue("A6","#");
$hoja->setCellValue("B6","NOMBRE");
$hoja->setCellValue("C6","TIENDA");

/* ==========================
   GENERAR DIAS DEL RANGO
========================== */

$dias = [];
$meses = [];

// Reemplazo de strftime() (deprecated desde PHP 8.1)
$nombresMeses = [
    1=>"ENERO", 2=>"FEBRERO", 3=>"MARZO", 4=>"ABRIL",
    5=>"MAYO", 6=>"JUNIO", 7=>"JULIO", 8=>"AGOSTO",
    9=>"SEPTIEMBRE", 10=>"OCTUBRE", 11=>"NOVIEMBRE", 12=>"DICIEMBRE"
];

$columna = 4; // D

$fecha = clone $fechaInicio;

while($fecha <= $fechaFin){

    $letra = Coordinate::stringFromColumnIndex($columna);

    $dias[$fecha->format("Y-m-d")] = $letra;

    $mes = $nombresMeses[(int)$fecha->format("n")];

    $meses[$mes][] = $letra;

    // DIA SEMANA
    $diasSemana = [
        "Mon"=>"L",
        "Tue"=>"M",
        "Wed"=>"M",
        "Thu"=>"J",
        "Fri"=>"V",
        "Sat"=>"S",
        "Sun"=>"D"
    ];

    $hoja->setCellValue(
        $letra."5",
        $diasSemana[$fecha->format("D")]
    );

    // DIA NUMERO
    $hoja->setCellValue(
        $letra."6",
        $fecha->format("d")
    );

    $hoja->getStyle($letra."5:".$letra."6")->applyFromArray([
        "alignment"=>[
            "horizontal"=>Alignment::HORIZONTAL_CENTER
        ],
        "font"=>[
            "bold"=>true
        ]
    ]);

    // Resalta en verde la columna cuando el dia de la semana es Domingo
    if($fecha->format("D") == "Sun"){
        $hoja->getStyle($letra."5")->applyFromArray([
            "fill"=>[
                "fillType"=>Fill::FILL_SOLID,
                "startColor"=>["rgb"=>$COLORES["VERDE"]]
            ],
            "font"=>[
                "bold"=>true,
                "color"=>["rgb"=>"FFFFFF"]
            ]
        ]);
    }

    $columna++;

    $fecha->modify("+1 day");

}

/* ==========================
   DIBUJAR MESES
========================== */

foreach($meses as $mes=>$cols){

    $inicioMes = $cols[0];
    $finMes    = end($cols);

    $hoja->mergeCells($inicioMes."3:".$finMes."3");

    $hoja->setCellValue($inicioMes."3",$mes);

    $colorMes = ($mes=="SEPTIEMBRE") ?
        $COLORES["ROJO"] :
        $COLORES["VERDE"];

    $hoja->getStyle($inicioMes."3:".$finMes."3")
        ->applyFromArray([
            "font"=>[
                "bold"=>true
            ],
            "alignment"=>[
                "horizontal"=>Alignment::HORIZONTAL_CENTER
            ],
            "fill"=>[
                "fillType"=>Fill::FILL_SOLID,
                "startColor"=>[
                    "rgb"=>$colorMes
                ]
            ]
        ]);

}

/* ==========================
   TEXTO DEL PERIODO
========================== */

$ultimaFecha = end(array_keys($dias));
$ultimaColumnaDias = end($dias);
$primeraColumnaDias = reset($dias);

$hoja->mergeCells("D4:".$ultimaColumnaDias."4");

$textoPeriodo = "DEL ".
    strtoupper($fechaInicio->format("d M")).
    " AL ".
    strtoupper($fechaFin->format("d M")).
    " DE ".$anio;

$hoja->setCellValue("D4",$textoPeriodo);

$hoja->getStyle("D4:".$ultimaColumnaDias."4")
    ->applyFromArray([
        "font"=>[
            "bold"=>true
        ],
        "alignment"=>[
            "horizontal"=>Alignment::HORIZONTAL_CENTER
        ]
    ]);

/* ==========================================================
   COLUMNAS DEL RESUMEN (a la derecha del calendario)
========================================================== */

// Ultima columna usada por los dias + 1 columna de separacion
$colResumenInicio = $columna + 1;

$colHrsExtra = $colResumenInicio;
$letraHrsExtra = Coordinate::stringFromColumnIndex($colHrsExtra);

// Orden de las columnas de resumen (debe coincidir con $contador)
$ordenResumen = ["A","D","F","PD","FES","DT","PCG","PSG","INC","V"];

$letrasResumen = [];
$colActual = $colHrsExtra + 1;

foreach($ordenResumen as $clave){
    $letrasResumen[$clave] = Coordinate::stringFromColumnIndex($colActual);
    $colActual++;
}

$ultimaColumnaResumen = Coordinate::stringFromColumnIndex($colActual - 1);
$colObservaciones = $colActual + 1;
$letraObservaciones = Coordinate::stringFromColumnIndex($colObservaciones);

// Titulo "RESUMEN" (fila 3, sobre las columnas de conteo)
$hoja->mergeCells($letrasResumen["A"]."3:".$ultimaColumnaResumen."3");
$hoja->setCellValue($letrasResumen["A"]."3","RESUMEN");
$hoja->getStyle($letrasResumen["A"]."3:".$ultimaColumnaResumen."3")->applyFromArray([
    "font"=>["bold"=>true],
    "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER],
    "fill"=>["fillType"=>Fill::FILL_SOLID,"startColor"=>["rgb"=>$COLORES["GRIS"]]]
]);

// Encabezado "HRS EXTRA"
$hoja->mergeCells($letraHrsExtra."3:".$letraHrsExtra."6");
$hoja->setCellValue($letraHrsExtra."3","HRS EXTRA");
$hoja->getStyle($letraHrsExtra."3")->applyFromArray([
    "font"=>["bold"=>true],
    "alignment"=>[
        "horizontal"=>Alignment::HORIZONTAL_CENTER,
        "vertical"=>Alignment::VERTICAL_CENTER,
        "wrapText"=>true
    ]
]);

// Encabezados de cada columna del resumen (fila 4), con su color
foreach($ordenResumen as $clave){

    $letra = $letrasResumen[$clave];

    $hoja->setCellValue($letra."4",$clave);

    $colorEncabezado = isset($CODIGOS[$clave]) ? $CODIGOS[$clave]["color"] : $COLORES["GRIS"];
    $colorFuente     = isset($CODIGOS[$clave]) ? $CODIGOS[$clave]["fuente"] : "000000";

    $hoja->getStyle($letra."4")->applyFromArray([
        "font"=>["bold"=>true,"color"=>["rgb"=>$colorFuente]],
        "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER],
        "fill"=>["fillType"=>Fill::FILL_SOLID,"startColor"=>["rgb"=>$colorEncabezado]]
    ]);
}

// Encabezado "OBSERVACIONES"
$hoja->mergeCells($letraObservaciones."3:".$letraObservaciones."6");
$hoja->setCellValue($letraObservaciones."3","OBSERVACIONES");
$hoja->getStyle($letraObservaciones."3")->applyFromArray([
    "font"=>["bold"=>true],
    "alignment"=>[
        "horizontal"=>Alignment::HORIZONTAL_CENTER,
        "vertical"=>Alignment::VERTICAL_CENTER,
        "wrapText"=>true
    ]
]);

/* ==========================================================
   PARTE 2
   CONSULTAR EMPLEADOS Y PREPARAR INFORMACIÓN
========================================================== */

if ($tienda == "todas") {

    $sqlEmpleados = "SELECT e.id_empleado, e.nombre, e.descanso, t.nombre AS tienda FROM empleados e INNER JOIN tiendas t ON e.id_tienda_actual = t.id_tienda WHERE e.status = 1 ORDER BY t.nombre, e.nombre";

    $stmtEmp = mysqli_prepare($conec, $sqlEmpleados);

} else {

    $sqlEmpleados = "SELECT e.id_empleado, e.nombre, e.descanso, t.nombre AS tienda FROM empleados e INNER JOIN tiendas t ON e.id_tienda_actual = t.id_tienda WHERE e.status = 1 AND t.nombre = ? ORDER BY e.nombre";

    $stmtEmp = mysqli_prepare($conec, $sqlEmpleados);
    mysqli_stmt_bind_param($stmtEmp, "s", $tienda);
}

mysqli_stmt_execute($stmtEmp);
$empleados = mysqli_stmt_get_result($stmtEmp);

/* ==========================================================
   CONSULTA DEL ÚLTIMO REGISTRO DEL DÍA
========================================================== */

$sqlRegistro = "SELECT
    r.id_registro,
    r.fecha,
    r.hora_entrada,
    r.tipo_registro,
    r.tipo_descanso,
    IFNULL(r.horas_extra,0) AS horas_extra
FROM registros r
WHERE r.id_empleado = ?
AND r.fecha BETWEEN ? AND ?
AND r.id_registro = (

    SELECT MAX(r2.id_registro)

    FROM registros r2

    WHERE r2.id_empleado = r.id_empleado
    AND r2.fecha = r.fecha

)
ORDER BY r.fecha
";

$stmtRegistro = mysqli_prepare($conec, $sqlRegistro);

/* ==========================================================
   FILA DONDE COMIENZAN LOS COLABORADORES
========================================================== */

$fila = 7;
$numeroEmpleado = 1;

// Estilo de borde delgado para las celdas del calendario
$bordeDelgado = [
    "borders"=>[
        "allBorders"=>[
            "borderStyle"=>Border::BORDER_THIN,
            "color"=>["rgb"=>"BFBFBF"]
        ]
    ]
];

while($empleado = mysqli_fetch_assoc($empleados)){

    $idEmpleado = $empleado["id_empleado"];

    // Número consecutivo
    $hoja->setCellValue("A".$fila, $numeroEmpleado);

    // Nombre
    $hoja->setCellValue("B".$fila, strtoupper($empleado["nombre"]));

    // Tienda
    $hoja->setCellValue("C".$fila, strtoupper($empleado["tienda"]));

    // Estilo de nombre y tienda
    $hoja->getStyle("A$fila:C$fila")->applyFromArray([
        "font"=>[
            "size"=>10
        ],
        "alignment"=>[
            "vertical"=>Alignment::VERTICAL_CENTER
        ]
    ]);

    // Fondo verde para nombre (como tu formato)
    $hoja->getStyle("B$fila")->applyFromArray([
        "fill"=>[
            "fillType"=>Fill::FILL_SOLID,
            "startColor"=>["rgb"=>"00B050"]
        ],
        "font"=>[
            "bold"=>true,
            "color"=>["rgb"=>"FFFFFF"]
        ]
    ]);

    /* ======================================
       TRAER LOS REGISTROS DEL EMPLEADO
    ====================================== */

    mysqli_stmt_bind_param(
        $stmtRegistro,
        "iss",
        $idEmpleado,
        $inicio,
        $fin
    );

    mysqli_stmt_execute($stmtRegistro);

    $resultadoRegistro = mysqli_stmt_get_result($stmtRegistro);

    $registros = [];

    while($registro = mysqli_fetch_assoc($resultadoRegistro)){
        $registros[$registro["fecha"]] = $registro;
    }

    /*
        Contadores del resumen
    */

    $contador = [
        "HE"  => 0,
        "A"   => 0,
        "D"   => 0,
        "F"   => 0,
        "PD"  => 0,
        "FES" => 0,
        "DT"  => 0,
        "PCG" => 0,
        "PSG" => 0,
        "INC" => 0,
        "V"   => 0
    ];

    /*
        Observaciones (vacaciones largas, incapacidades, etc.)
        Se generan agrupando dias consecutivos con el mismo codigo,
        solo dentro del rango de la quincena consultada.

        NOTA: si necesitas que la observacion muestre el rango COMPLETO
        aunque empiece o termine fuera de la quincena (como en tu
        ejemplo "INCAPACIDAD DEL 10 DE JUN AL 1 DE SEP 2026"), esa
        informacion tendria que venir de una tabla adicional
        (por ejemplo "incapacidades" o "permisos" con fecha_inicio /
        fecha_fin) que aun no me has compartido. Avísame si la tienes
        y ajusto esta parte para leerla de ahí.
    */

    $observaciones = [];
    $bloqueActual = null; // ["codigo"=>.., "inicio"=>DateTime, "fin"=>DateTime]

    $codigosParaObservacion = ["V","INC","M","P"]; // vacaciones, incapacidad, maternidad, paternidad

    /* ======================================
       RECORRER CADA DIA DEL CALENDARIO
    ====================================== */

    foreach($dias as $fechaDia => $letraCol){

        $registro = $registros[$fechaDia] ?? null;

        if($registro !== null){
            $tipoRegistro  = $registro["tipo_registro"];
            $tipoDescanso  = $registro["tipo_descanso"];
            $contador["HE"] += (float)$registro["horas_extra"];
        } else {
            // No hay registro para este dia: se marca como falta
            $tipoRegistro = "FALTA";
            $tipoDescanso = null;
        }

        $codigo = buscarCodigo($CODIGOS, $tipoRegistro, $tipoDescanso);

        $celda = $letraCol.$fila;

        // Borde en todas las celdas del calendario
        $hoja->getStyle($celda)->applyFromArray($bordeDelgado);

        if($codigo !== null){

            $def = $CODIGOS[$codigo];

            // "A" y "D" se dejan en blanco (sin texto) como en tu plantilla,
            // solo se cuentan. El resto de codigos SI se escriben en la celda.
            if(!in_array($codigo, ["A","D"])){

                $hoja->setCellValue($celda, $codigo);

                $hoja->getStyle($celda)->applyFromArray([
                    "fill"=>[
                        "fillType"=>Fill::FILL_SOLID,
                        "startColor"=>["rgb"=>$def["color"]]
                    ],
                    "font"=>[
                        "bold"=>true,
                        "color"=>["rgb"=>$def["fuente"]]
                    ],
                    "alignment"=>[
                        "horizontal"=>Alignment::HORIZONTAL_CENTER
                    ]
                ]);
            }

            if($def["contador"] !== null){
                $contador[$def["contador"]]++;
            }

            // Agrupar dias consecutivos para las observaciones
            if(in_array($codigo, $codigosParaObservacion)){

                $fechaObj = new DateTime($fechaDia);

                if($bloqueActual !== null
                    && $bloqueActual["codigo"] == $codigo
                    && $bloqueActual["fin"]->diff($fechaObj)->days == 1){

                    $bloqueActual["fin"] = $fechaObj;

                } else {

                    if($bloqueActual !== null){
                        $observaciones[] = $bloqueActual;
                    }

                    $bloqueActual = [
                        "codigo"=>$codigo,
                        "inicio"=>$fechaObj,
                        "fin"=>$fechaObj
                    ];
                }

            } else if($bloqueActual !== null){
                $observaciones[] = $bloqueActual;
                $bloqueActual = null;
            }

        }

    }

    if($bloqueActual !== null){
        $observaciones[] = $bloqueActual;
    }

    /* ======================================
       ESCRIBIR RESUMEN DE LA FILA
    ====================================== */

    $hoja->setCellValue($letraHrsExtra.$fila, $contador["HE"]);
    $hoja->getStyle($letraHrsExtra.$fila)->applyFromArray([
        "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER]
    ]);

    foreach($ordenResumen as $clave){

        $letra = $letrasResumen[$clave];

        $hoja->setCellValue($letra.$fila, $contador[$clave]);

        $hoja->getStyle($letra.$fila)->applyFromArray([
            "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER]
        ]);

        // Resalta la celda del resumen si tiene un valor mayor a 0,
        // usando el color de su propio codigo (como en tu ejemplo INC=7)
        if($contador[$clave] > 0 && isset($CODIGOS[$clave])){
            $hoja->getStyle($letra.$fila)->applyFromArray([
                "fill"=>[
                    "fillType"=>Fill::FILL_SOLID,
                    "startColor"=>["rgb"=>$CODIGOS[$clave]["color"]]
                ],
                "font"=>[
                    "bold"=>true,
                    "color"=>["rgb"=>$CODIGOS[$clave]["fuente"]]
                ]
            ]);
        }
    }

    /* ======================================
       ESCRIBIR OBSERVACIONES
    ====================================== */

    $mesesTexto = [
        1=>"ENE",2=>"FEB",3=>"MAR",4=>"ABR",5=>"MAY",6=>"JUN",
        7=>"JUL",8=>"AGO",9=>"SEP",10=>"OCT",11=>"NOV",12=>"DIC"
    ];

    $textosObservaciones = [];

    foreach($observaciones as $bloque){

        $nombreEstado = [
            "V"=>"VACACIONES",
            "INC"=>"INCAPACIDAD",
            "M"=>"MATERNIDAD",
            "P"=>"PATERNIDAD"
        ][$bloque["codigo"]] ?? $bloque["codigo"];

        $textoInicio = $bloque["inicio"]->format("d")." DE ".$mesesTexto[(int)$bloque["inicio"]->format("n")];
        $textoFin    = $bloque["fin"]->format("d")." DE ".$mesesTexto[(int)$bloque["fin"]->format("n")]." ".$bloque["fin"]->format("Y");

        if($bloque["inicio"]->format("Y-m-d") == $bloque["fin"]->format("Y-m-d")){
            $textosObservaciones[] = "$nombreEstado EL $textoFin";
        } else {
            $textosObservaciones[] = "$nombreEstado DEL $textoInicio AL $textoFin";
        }
    }

    $hoja->setCellValue($letraObservaciones.$fila, implode(" / ", $textosObservaciones));
    $hoja->getStyle($letraObservaciones.$fila)->applyFromArray([
        "alignment"=>["wrapText"=>true,"vertical"=>Alignment::VERTICAL_CENTER]
    ]);

    $fila++;
    $numeroEmpleado++;

}

/* ==========================================================
   LEYENDA (igual a la que compartiste)
========================================================== */

$filaLeyenda = $fila + 2;

$leyendaIzquierda = [
    ["FES","DIA FESTIVO"],
    ["PD","PRIMA DOMINICAL"],
    ["PCG","PERMISO CON GOCE DE SUELDO"],
    ["A","ASISTENCIA"],
    ["D","DESCANSO"],
    ["V","VACACIONES"],
    ["AA","DOBLE ASISTENCIA POR SAFARI NOCTURNO"],
];

$leyendaDerecha = [
    ["F","FALTA"],
    ["PSG","PERMISO SIN GOCE"],
    ["R","RETARDO"],
    ["INC","INCAPACIDAD"],
    ["M","MATERNIDAD"],
    ["P","PATERNIDAD"],
    ["HO","HOME OFFICE"],
    ["CUM","CUMPLEAÑOS"],
    ["DT","DESCANSO TRABAJADO"],
];

foreach($leyendaIzquierda as $i => $item){

    [$codigo,$texto] = $item;
    $filaActual = $filaLeyenda + $i;
    $def = $CODIGOS[$codigo] ?? null;

    $hoja->setCellValue("D".$filaActual, $codigo);
    $hoja->setCellValue("E".$filaActual, $texto);

    if($def !== null){
        $hoja->getStyle("D".$filaActual)->applyFromArray([
            "fill"=>["fillType"=>Fill::FILL_SOLID,"startColor"=>["rgb"=>$def["color"]]],
            "font"=>["bold"=>true,"color"=>["rgb"=>$def["fuente"]]],
            "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER]
        ]);
    }
}

foreach($leyendaDerecha as $i => $item){

    [$codigo,$texto] = $item;
    $filaActual = $filaLeyenda + $i;
    $def = $CODIGOS[$codigo] ?? null;

    $hoja->setCellValue("H".$filaActual, $codigo);
    $hoja->setCellValue("I".$filaActual, $texto);

    if($def !== null){
        $hoja->getStyle("H".$filaActual)->applyFromArray([
            "fill"=>["fillType"=>Fill::FILL_SOLID,"startColor"=>["rgb"=>$def["color"]]],
            "font"=>["bold"=>true,"color"=>["rgb"=>$def["fuente"]]],
            "alignment"=>["horizontal"=>Alignment::HORIZONTAL_CENTER]
        ]);
    }
}

/* ==========================================================
   AJUSTES FINALES DE FORMATO
========================================================== */

$hoja->getColumnDimension("A")->setWidth(4);
$hoja->getColumnDimension("B")->setWidth(28);
$hoja->getColumnDimension("C")->setWidth(20);
$hoja->getColumnDimension($letraObservaciones)->setWidth(35);

foreach($dias as $letra){
    $hoja->getColumnDimension($letra)->setWidth(4);
}

$hoja->freezePane("D7");

/* ==========================================================
   GUARDAR Y DESCARGAR EL ARCHIVO
========================================================== */

$nombreArchivo = "PRENOMINA_QUINCENA_".$numeroQuincena."_".$anio.".xlsx";

// Descarta cualquier cosa que se haya impreso antes (warnings, BOM, etc.)
if (ob_get_length() !== false) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$nombreArchivo.'"');
header('Cache-Control: max-age=0');
header('Content-Transfer-Encoding: binary');

$writer = new Xlsx($excel);
$writer->save('php://output');
exit;