<?php
/*Evita que cualquier warning/notice/deprecated (o espacio en blanco accidental de algún archivo incluido) se mezcle con el binario del
    xlsx y lo corrompa. Los errores reales se registran en el log de PHP en vez de imprimirse en pantalla.*/
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

// Si ocurre un error fatal antes de terminar el XLSX, responde con HTTP 500
// en lugar de devolver un archivo incompleto que el navegador no pueda abrir.
register_shutdown_function(function () {
    $error = error_get_last();
    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if ($error && in_array($error['type'], $fatales, true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
        }
        error_log('Error fatal al exportar prenómina: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']);
        echo 'No se pudo generar el Excel. Revisa el registro de errores de PHP del servidor.';
    }
});

include "conexion.php";
session_start();

// El Excel también debe respetar los permisos del sistema.
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, ['admin', 'supervisora'], true)) {
    http_response_code(403);
    exit('No tienes permisos para exportar la prenómina.');
}
$idZonaSesion = (int)($_SESSION['id_zona'] ?? 0);

require "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

date_default_timezone_set('America/Mexico_City');

// La prenómina web puede enviar las filas editadas por POST.
$filasEditadas = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrada = json_decode(file_get_contents('php://input'), true);
    $inicio = $entrada['inicio'] ?? date("Y-m-01");
    $fin    = $entrada['fin'] ?? date("Y-m-d");
    $tienda = $entrada['tienda'] ?? "todas";
    $zonaFiltro = (int)($entrada['zona'] ?? 0);
    $filasEditadas = $entrada['filas'] ?? null;
} else {
    $inicio = $_GET['inicio'] ?? date("Y-m-01");
    $fin    = $_GET['fin'] ?? date("Y-m-d");
    $tienda = $_GET['tienda'] ?? "todas";
    $zonaFiltro = (int)($_GET['zona'] ?? 0);
}

// La zona de una supervisora siempre se toma de su sesión, nunca del formulario.
if ($rol === 'supervisora') {
    $zonaFiltro = $idZonaSesion;
}

if (!$inicio || !$fin || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fin) || $inicio > $fin) {
    http_response_code(400);
    if (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=utf-8');
    exit('El periodo seleccionado no es válido.');
}

$fechaInicioValidada = DateTime::createFromFormat('!Y-m-d', $inicio);
$fechaFinValidada = DateTime::createFromFormat('!Y-m-d', $fin);
$erroresInicio = DateTime::getLastErrors();
$erroresFin = DateTime::getLastErrors();
if (
    !$fechaInicioValidada || !$fechaFinValidada ||
    ($erroresInicio !== false && ($erroresInicio['warning_count'] > 0 || $erroresInicio['error_count'] > 0)) ||
    ($erroresFin !== false && ($erroresFin['warning_count'] > 0 || $erroresFin['error_count'] > 0)) ||
    $fechaInicioValidada->format('Y-m-d') !== $inicio ||
    $fechaFinValidada->format('Y-m-d') !== $fin
) {
    http_response_code(400);
    if (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=utf-8');
    exit('Las fechas seleccionadas no son válidas.');
}
$fechaInicio = $fechaInicioValidada;
$fechaFin = $fechaFinValidada;

/* NUMERACIÓN CONSECUTIVA DE PRENÓMINA
   El periodo 25/09/2026–09/10/2026 corresponde al número 18.
   Cada periodo consecutivo dura 15 días: el siguiente será el 19. */
$periodoBase = new DateTime('2026-09-25');
$numeroBase = 19;
$diferenciaDias = (int)$periodoBase->diff($fechaInicio)->format('%r%a');
$numeroQuincena = $numeroBase + (int)floor($diferenciaDias / 15);
$anio = $fechaInicio->format("Y");

/* ==========================
   CREAR EXCEL
========================== */

$excel = new Spreadsheet();
$hoja = $excel->getActiveSheet();
$hoja->setTitle("PRENOMINA");

/*COLORES*/

$COLORES = [
    "AZUL"      => "0070C0",
    "VERDE"     => "00B050",
    "ROJO"      => "FF0000",
    "AMARILLO"  => "FFC000",
    "GRIS"      => "808080",
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

/* CATALOGO DE CODIGOS DE ASISTENCIA
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

    // Estado base: se usa solo cuando NO hay tipo_descanso
    "A"   => ["tipo" => "NORMAL",   "subtipo" => null, "color" => $COLORES["BLANCO"], "fuente" => "000000", "contador" => "A"],
    "D"   => ["tipo" => "DESCANSO", "subtipo" => null, "color" => $COLORES["BLANCO"], "fuente" => "000000", "contador" => "D"],
    "F"   => ["tipo" => "FALTA",    "subtipo" => null, "color" => $COLORES["ROJO"],   "fuente" => "000000", "contador" => "F"],

    // Motivos: se buscan por tipo_descanso, sin importar tipo_registro
    "DT"  => ["tipo" => null, "subtipo" => "DESCANSO TRABAJADO", "color" => $COLORES["AZUL"],        "fuente" => "000000", "contador" => "DT"],
    "PCG" => ["tipo" => null, "subtipo" => "PERMISO CON GOCE",   "color" => $COLORES["NARANJA"],     "fuente" => "000000", "contador" => "PCG"],
    "PSG" => ["tipo" => null, "subtipo" => "PERMISO SIN GOCE",   "color" => $COLORES["MORADO"],      "fuente" => "000000", "contador" => "PSG"],
    "INC" => ["tipo" => null, "subtipo" => "INCAPACIDAD",        "color" => $COLORES["AMARILLO"],    "fuente" => "000000", "contador" => "INC"],
    "V"   => ["tipo" => null, "subtipo" => "VACACIONES",         "color" => $COLORES["GRIS"],        "fuente" => "000000", "contador" => "V"],
    "FES" => ["tipo" => null, "subtipo" => "FESTIVO",            "color" => $COLORES["AMARILLO_FES"], "fuente" => "000000", "contador" => "FES"],
    "PD"  => ["tipo" => null, "subtipo" => "PRIMA DOMINICAL",    "color" => $COLORES["VERDE"],       "fuente" => "000000", "contador" => "PD"],
    "R"   => ["tipo" => null, "subtipo" => "RETARDO",            "color" => $COLORES["AZUL_CLARO"],  "fuente" => "000000", "contador" => null],
    "M"   => ["tipo" => null, "subtipo" => "MATERNIDAD",         "color" => $COLORES["ROSA"],        "fuente" => "000000", "contador" => null],
    "P"   => ["tipo" => null, "subtipo" => "PATERNIDAD",         "color" => $COLORES["VERDE_CLARO"], "fuente" => "000000", "contador" => null],
    "HO"  => ["tipo" => null, "subtipo" => "HOME OFFICE",        "color" => $COLORES["NARANJA_HO"],  "fuente" => "000000", "contador" => null],
    "CUM" => ["tipo" => null, "subtipo" => "CUMPLEAÑOS",         "color" => $COLORES["CIAN"],        "fuente" => "000000", "contador" => null],
    "AA"  => ["tipo" => null, "subtipo" => "DOBLE ASISTENCIA POR SAFARI NOCTURNO", "color" => $COLORES["CAFE_OSCURO"], "fuente" => "000000", "contador" => null],

];

/*
    Indice inverso para encontrar rapidamente el codigo a partir
    de (tipo_registro, tipo_descanso)
*/
function buscarCodigo($CODIGOS, $tipoRegistro, $tipoDescanso)
{

    $tipoDescanso = trim((string)$tipoDescanso);

    // 1) Si hay un motivo específico en tipo_descanso, ese manda,
    //    sin importar si tipo_registro es NORMAL, FALTA o DESCANSO.
    if ($tipoDescanso !== "") {
        foreach ($CODIGOS as $codigo => $def) {
            if ($def["subtipo"] !== null && $def["subtipo"] === $tipoDescanso) {
                return $codigo;
            }
        }
    }

    // 2) Sin motivo especial: usar el estado base (A/D/F)
    foreach ($CODIGOS as $codigo => $def) {
        if ($def["subtipo"] === null && $def["tipo"] === $tipoRegistro) {
            return $codigo;
        }
    }

    return null;
}

/*ENCABEZADO*/

//$hoja->mergeCells("B2:C2");
$hoja->setCellValue("B2", "DIRECCION DE TIENDAS");

$hoja->getStyle("B2")->applyFromArray([
    "font" => [
        "bold" => true,
        "color" => ["rgb" => "000000"],
    ],
    "alignment" => [
        "horizontal" => Alignment::HORIZONTAL_CENTER
    ],
    "fill" => [
        "fillType" => Fill::FILL_SOLID,
        "startColor" => ["rgb" => $COLORES["AZUL"]]
    ]
]);

$hoja->setCellValue("B3", "QUINCENA No. " . $numeroQuincena . " " . $anio);

/*COLUMNAS FIJAS*/

$hoja->setCellValue("A4", "");
$hoja->setCellValue("B4", "NOMBRE");
$hoja->setCellValue("C4", "TIENDA");

/*GENERAR DIAS DEL RANGO*/

$dias = [];
$meses = [];

// Reemplazo de strftime()
$nombresMeses = [
    1 => "ENERO",
    2 => "FEBRERO",
    3 => "MARZO",
    4 => "ABRIL",
    5 => "MAYO",
    6 => "JUNIO",
    7 => "JULIO",
    8 => "AGOSTO",
    9 => "SEPTIEMBRE",
    10 => "OCTUBRE",
    11 => "NOVIEMBRE",
    12 => "DICIEMBRE"
];

$columna = 4; // D

$fecha = clone $fechaInicio;

while ($fecha <= $fechaFin) {

    $letra = Coordinate::stringFromColumnIndex($columna);

    $dias[$fecha->format("Y-m-d")] = $letra;

    $mes = $nombresMeses[(int)$fecha->format("n")];

    $meses[$mes][] = $letra;

    // DIA SEMANA
    $diasSemana = [
        "Mon" => "L",
        "Tue" => "M",
        "Wed" => "M",
        "Thu" => "J",
        "Fri" => "V",
        "Sat" => "S",
        "Sun" => "D"
    ];

    $hoja->setCellValue(
        $letra . "3",
        $diasSemana[$fecha->format("D")]
    );

    // DIA NUMERO
    $hoja->setCellValue(
        $letra . "4",
        $fecha->format("d")
    );

    $hoja->getStyle($letra . "3:" . $letra . "4")->applyFromArray([
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER
        ],
        "font" => [
            "bold" => true
        ]
    ]);

    // Resalta en verde la columna cuando el dia de la semana es Domingo
    if ($fecha->format("D") == "Sun") {
        $hoja->getStyle($letra . "3")->applyFromArray([
            "fill" => [
                "fillType" => Fill::FILL_SOLID,
                "startColor" => ["rgb" => $COLORES["VERDE"]]
            ],
            "font" => [
                "bold" => true,
                "color" => ["rgb" => "000000"]
            ]
        ]);
    }

    $columna++;

    $fecha->modify("+1 day");
}

/*DIBUJAR MESES*/

foreach ($meses as $mes => $cols) {

    $inicioMes = $cols[0];
    $finMes    = end($cols);

    $hoja->mergeCells($inicioMes . "1:" . $finMes . "1");

    $hoja->setCellValue($inicioMes . "1", $mes);

    $colorMes = ($mes == "SEPTIEMBRE") ?
        $COLORES["ROJO"] :
        $COLORES["VERDE"];

    $hoja->getStyle($inicioMes . "1:" . $finMes . "1")
        ->applyFromArray([
            "font" => [
                "bold" => true
            ],
            "alignment" => [
                "horizontal" => Alignment::HORIZONTAL_CENTER
            ],
            "fill" => [
                "fillType" => Fill::FILL_SOLID,
                "startColor" => [
                    "rgb" => $colorMes
                ]
            ]
        ]);
}

/* TEXTO DEL PERIODO*/

$fechasCalendario = array_keys($dias);
$ultimaFecha = $fechasCalendario ? end($fechasCalendario) : null;
$ultimaColumnaDias = end($dias);
$primeraColumnaDias = reset($dias);

$hoja->mergeCells("D2:" . $ultimaColumnaDias . "2");

$textoPeriodo = "DEL " .
    strtoupper($fechaInicio->format("d M")) .
    " AL " .
    strtoupper($fechaFin->format("d M")) .
    " DE " . $anio;

$hoja->setCellValue("D2", $textoPeriodo);

$hoja->getStyle("D2:" . $ultimaColumnaDias . "2")
    ->applyFromArray([
        "font" => [
            "bold" => true
        ],
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER
        ]
    ]);

/* COLUMNAS DEL RESUMEN (a la derecha del calendario)*/

// Ultima columna usada por los dias + 1 columna de separacion
$colResumenInicio = $columna + 1;

$colHrsExtra = $colResumenInicio;
$letraHrsExtra = Coordinate::stringFromColumnIndex($colHrsExtra);

// Orden de las columnas de resumen (debe coincidir con $contador)
$ordenResumen = ["A", "D", "F", "PD", "FES", "DT", "PCG", "PSG", "INC", "V"];

$letrasResumen = [];
$colActual = $colHrsExtra + 1;

foreach ($ordenResumen as $clave) {
    $letrasResumen[$clave] = Coordinate::stringFromColumnIndex($colActual);
    $colActual++;
}

$ultimaColumnaResumen = Coordinate::stringFromColumnIndex($colActual - 1);
$colObservaciones = $colActual + 1;
$letraObservaciones = Coordinate::stringFromColumnIndex($colObservaciones);

// Titulo "RESUMEN" (fila 3, sobre las columnas de conteo)
$hoja->mergeCells($letrasResumen["A"] . "3:" . $ultimaColumnaResumen . "3");
$hoja->setCellValue($letrasResumen["A"] . "3", "RESUMEN");
$hoja->getStyle($letrasResumen["A"] . "3:" . $ultimaColumnaResumen . "3")->applyFromArray([
    "font" => ["bold" => true],
    "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER],
    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => $COLORES["BLANCO"]]]
]);

// Encabezado "HRS EXTRA"
$hoja->mergeCells($letraHrsExtra . "4:" . $letraHrsExtra . "4");
$hoja->setCellValue($letraHrsExtra . "4", "HRS EXTRA");
$hoja->getStyle($letraHrsExtra . "4")->applyFromArray([
    "font" => ["bold" => true],
    "alignment" => [
        "horizontal" => Alignment::HORIZONTAL_CENTER,
        "vertical" => Alignment::VERTICAL_CENTER,
        "wrapText" => true
    ]
]);

// Encabezados de cada columna del resumen (fila 4), con su color
foreach ($ordenResumen as $clave) {

    $letra = $letrasResumen[$clave];

    $hoja->setCellValue($letra . "4", $clave);

    $colorEncabezado = isset($CODIGOS[$clave]) ? $CODIGOS[$clave]["color"] : $COLORES["BLANCO"];
    $colorFuente     = isset($CODIGOS[$clave]) ? $CODIGOS[$clave]["fuente"] : "000000";

    $hoja->getStyle($letra . "4")->applyFromArray([
        "font" => ["bold" => true, "color" => ["rgb" => $colorFuente]],
        "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER],
        "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => $colorEncabezado]]
    ]);
}

// Encabezado "OBSERVACIONES"
$hoja->mergeCells($letraObservaciones . "4:" . $letraObservaciones . "4");
$hoja->setCellValue($letraObservaciones . "4", "OBSERVACIONES");
$hoja->getStyle($letraObservaciones . "4")->applyFromArray([
    "font" => ["bold" => true],
    "alignment" => [
        "horizontal" => Alignment::HORIZONTAL_CENTER,
        "vertical" => Alignment::VERTICAL_CENTER,
        "wrapText" => true
    ]
]);

/* CONSULTAR EMPLEADOS Y PREPARAR INFORMACIÓN */

$sqlEmpleados = "SELECT e.id_empleado, e.nombre, e.descanso, t.nombre AS tienda
                 FROM empleados e
                 INNER JOIN tiendas t ON e.id_tienda_actual = t.id_tienda
                 WHERE e.status = 1";
$parametrosEmp = [];
$tiposEmp = '';
if ($rol === 'supervisora' || ($rol === 'admin' && $zonaFiltro > 0)) {
    $sqlEmpleados .= " AND t.id_zona = ?";
    $parametrosEmp[] = $zonaFiltro;
    $tiposEmp .= 'i';
}
if ($tienda !== 'todas') {
    $sqlEmpleados .= " AND t.nombre = ?";
    $parametrosEmp[] = $tienda;
    $tiposEmp .= 's';
}
$sqlEmpleados .= " ORDER BY e.nombre";
$stmtEmp = mysqli_prepare($conec, $sqlEmpleados);
if (!$stmtEmp) {
    throw new RuntimeException('No se pudo preparar la consulta de empleados: ' . mysqli_error($conec));
}
if ($tiposEmp !== '') {
    // mysqli_stmt_bind_param requiere referencias; se preparan para que
    // funcione con cualquier combinación de filtros de zona y tienda.
    $parametrosReferencia = [$tiposEmp];
    foreach ($parametrosEmp as $indice => $valor) {
        $parametrosReferencia[] = &$parametrosEmp[$indice];
    }
    if (!call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmtEmp], $parametrosReferencia))) {
        throw new RuntimeException('No se pudieron aplicar los filtros de zona y tienda.');
    }
}

if (!mysqli_stmt_execute($stmtEmp)) {
    throw new RuntimeException('No se pudo consultar la lista de empleados: ' . mysqli_stmt_error($stmtEmp));
}
$empleados = mysqli_stmt_get_result($stmtEmp);

/* CONSULTA DEL ÚLTIMO REGISTRO DEL DÍA*/

$sqlRegistro = "SELECT r.id_registro, r.fecha, r.hora_entrada, r.tipo_registro, r.tipo_descanso, IFNULL(r.horas_extra,0) AS horas_extra FROM registros r WHERE r.id_empleado = ?
AND r.fecha BETWEEN ? AND ? AND r.id_registro = (SELECT MAX(r2.id_registro) FROM registros r2 WHERE r2.id_empleado = r.id_empleado AND r2.fecha = r.fecha)
ORDER BY r.fecha";

$stmtRegistro = mysqli_prepare($conec, $sqlRegistro);

/* FILA DONDE COMIENZAN LOS COLABORADORES*/

$fila = 5;
$numeroEmpleado = 1;

// Estilo de borde delgado para las celdas del calendario
$bordeDelgado = [
    "borders" => [
        "allBorders" => [
            "borderStyle" => Border::BORDER_THIN,
            "color" => ["rgb" => "BFBFBF"]
        ]
    ]
];

while ($empleado = mysqli_fetch_assoc($empleados)) {

    $idEmpleado = $empleado["id_empleado"];

    // Número consecutivo
    $hoja->setCellValue("A" . $fila, $numeroEmpleado);

    // Nombre
    $hoja->setCellValue("B" . $fila, strtoupper($empleado["nombre"]));

    // Tienda
    $hoja->setCellValue("C" . $fila, strtoupper($empleado["tienda"]));

    // Estilo de nombre y tienda
    $hoja->getStyle("A$fila:C$fila")->applyFromArray([
        "font" => [
            "size" => 10
        ],
        "alignment" => [
            "vertical" => Alignment::VERTICAL_CENTER
        ]
    ]);

    // Fondo verde para nombre (como tu formato)
    $hoja->getStyle("B$fila")->applyFromArray([
        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => ["rgb" => "00B050"]
        ],
        "font" => [
            "bold" => true,
            "color" => ["rgb" => "000000"]
        ]
    ]);

    /* TRAER LOS REGISTROS DEL EMPLEADO
       Si vienen filas editadas desde la prenómina web, usamos esas filas.
       Si no, conservamos el comportamiento original consultando MySQL. */
    $filaEditada = null;
    if (is_array($filasEditadas)) {
        foreach ($filasEditadas as $fEdit) {
            if ((int)($fEdit['id_empleado'] ?? 0) === (int)$idEmpleado) {
                $filaEditada = $fEdit;
                break;
            }
        }
    }

    $registros = [];
    $diasEditados = [];
    $horasExtraEditadas = 0;
    $observacionesEditadas = '';

    if ($filaEditada !== null) {
        $diasEditados = is_array($filaEditada['dias'] ?? null) ? $filaEditada['dias'] : [];
        $horasExtraEditadas = (float)($filaEditada['horas_extra'] ?? 0);
        $observacionesEditadas = trim((string)($filaEditada['observaciones'] ?? ''));
    } else {
        mysqli_stmt_bind_param($stmtRegistro, "iss", $idEmpleado, $inicio, $fin);
        mysqli_stmt_execute($stmtRegistro);
        $resultadoRegistro = mysqli_stmt_get_result($stmtRegistro);
        while ($registro = mysqli_fetch_assoc($resultadoRegistro)) {
            $registros[$registro["fecha"]] = $registro;
        }
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

    /*Observaciones (vacaciones largas, incapacidades, etc.)
        Se generan agrupando dias consecutivos con el mismo codigo,
        solo dentro del rango de la quincena consultada.*/

    $observaciones = [];
    $bloqueActual = null; // ["codigo"=>.., "inicio"=>DateTime, "fin"=>DateTime]

    $codigosParaObservacion = ["V", "INC", "M", "P"]; // vacaciones, incapacidad, maternidad, paternidad

    /* RECORRER CADA DIA DEL CALENDARIO*/

    foreach ($dias as $fechaDia => $letraCol) {

        if ($filaEditada !== null) {
            $codigo = strtoupper(trim((string)($diasEditados[$fechaDia] ?? 'F')));
            // Conservar PD verde en domingos incluso si la asistencia editada quedó guardada como A.
            if ($codigo === 'A' && (new DateTime($fechaDia))->format('N') === '7') {
                $codigo = 'PD';
            }
            if (!isset($CODIGOS[$codigo])) {
                $codigo = 'F';
            }
            $contador["HE"] = $horasExtraEditadas;
        } else {
            $registro = $registros[$fechaDia] ?? null;
            if ($registro !== null) {
                $tipoRegistro  = $registro["tipo_registro"];
                $tipoDescanso  = $registro["tipo_descanso"];
                $contador["HE"] += (float)$registro["horas_extra"];
            } else {
                // No hay registro para este dia: se marca como falta
                $tipoRegistro = "FALTA";
                $tipoDescanso = null;
            }
            $codigo = buscarCodigo($CODIGOS, $tipoRegistro, $tipoDescanso);

            // Si el registro es una asistencia normal en domingo,
            // se muestra como prima dominical (PD) en el Excel.
            if (
                $codigo === 'A'
                && (new DateTime($fechaDia))->format('N') === '7'
            ) {
                $codigo = 'PD';
            }
        }

        $celda = $letraCol . $fila;

        // Borde en todas las celdas del calendario
        $hoja->getStyle($celda)->applyFromArray($bordeDelgado);


        if ($codigo !== null) {

            $def = $CODIGOS[$codigo];

            // Escribir todos los códigos, incluyendo A y D
            $hoja->setCellValue($celda, $codigo);

            // Aplicar color, fuente y alineación
            $hoja->getStyle($celda)->applyFromArray([
                "fill" => [
                    "fillType" => Fill::FILL_SOLID,
                    "startColor" => ["rgb" => $def["color"]]
                ],
                "font" => [
                    "bold" => true,
                    "color" => ["rgb" => $def["fuente"]]
                ],
                "alignment" => [
                    "horizontal" => Alignment::HORIZONTAL_CENTER,
                    "vertical" => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Contabilizar el código correspondiente
            if ($def["contador"] !== null) {
                $contador[$def["contador"]]++;
            }

            // Agrupar días consecutivos para las observaciones
            if (in_array($codigo, $codigosParaObservacion)) {
                $fechaObj = new DateTime($fechaDia);

                if (
                    $bloqueActual !== null
                    && $bloqueActual["codigo"] == $codigo
                    && $bloqueActual["fin"]->diff($fechaObj)->days == 1
                ) {

                    $bloqueActual["fin"] = $fechaObj;
                } else {

                    if ($bloqueActual !== null) {
                        $observaciones[] = $bloqueActual;
                    }

                    $bloqueActual = [
                        "codigo" => $codigo,
                        "inicio" => $fechaObj,
                        "fin" => $fechaObj
                    ];
                }
            } else if ($bloqueActual !== null) {

                $observaciones[] = $bloqueActual;
                $bloqueActual = null;
            }
        }
    }

    if ($bloqueActual !== null) {
        $observaciones[] = $bloqueActual;
    }

    /*ESCRIBIR RESUMEN DE LA FILA
    ====================================== */

    // HRS EXTRA: toma el valor editado en la prenómina web cuando existe.
    $hoja->setCellValue($letraHrsExtra . $fila, $filaEditada !== null ? $horasExtraEditadas : "");
    $hoja->getStyle($letraHrsExtra . $fila)->applyFromArray($bordeDelgado);
    $hoja->getStyle($letraHrsExtra . $fila)->applyFromArray([
        "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER]
    ]);

    foreach ($ordenResumen as $clave) {

        $letra = $letrasResumen[$clave];
        $hoja->getStyle($letra . $fila)->applyFromArray($bordeDelgado);

        $hoja->setCellValue($letra . $fila, $contador[$clave]);
        $hoja->getStyle($letra . $fila)->applyFromArray($bordeDelgado);

        $hoja->getStyle($letraHrsExtra . $fila)->applyFromArray($bordeDelgado);
        $hoja->getStyle($letra . $fila)->applyFromArray([
            "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER]
        ]);

        // Resalta la celda del resumen si tiene un valor mayor a 0,

        if ($contador[$clave] > 0 && isset($CODIGOS[$clave])) {
            $hoja->getStyle($letra . $fila)->applyFromArray([
                "fill" => [
                    "fillType" => Fill::FILL_SOLID,
                    "startColor" => ["rgb" => $CODIGOS[$clave]["color"]]
                ],
                "font" => [
                    "bold" => true,
                    "color" => ["rgb" => $CODIGOS[$clave]["fuente"]]
                ]
            ]);
        }
    }

    /* ======================================
       ESCRIBIR OBSERVACIONES
    ====================================== */

    $mesesTexto = [
        1 => "ENE",
        2 => "FEB",
        3 => "MAR",
        4 => "ABR",
        5 => "MAY",
        6 => "JUN",
        7 => "JUL",
        8 => "AGO",
        9 => "SEP",
        10 => "OCT",
        11 => "NOV",
        12 => "DIC"
    ];

    $textosObservaciones = [];

    foreach ($observaciones as $bloque) {

        $nombreEstado = [
            "V" => "VACACIONES",
            "INC" => "INCAPACIDAD",
            "M" => "MATERNIDAD",
            "P" => "PATERNIDAD"
        ][$bloque["codigo"]] ?? $bloque["codigo"];

        $textoInicio = $bloque["inicio"]->format("d") . " DE " . $mesesTexto[(int)$bloque["inicio"]->format("n")];
        $textoFin    = $bloque["fin"]->format("d") . " DE " . $mesesTexto[(int)$bloque["fin"]->format("n")] . " " . $bloque["fin"]->format("Y");

        if ($bloque["inicio"]->format("Y-m-d") == $bloque["fin"]->format("Y-m-d")) {
            $textosObservaciones[] = "$nombreEstado EL $textoFin";
        } else {
            $textosObservaciones[] = "$nombreEstado DEL $textoInicio AL $textoFin";
        }
    }

    // Exportar exclusivamente las observaciones escritas en la pantalla; no agregar comentarios automáticos.
    $hoja->setCellValue($letraObservaciones . $fila, $observacionesEditadas);
    $hoja->getStyle($letraObservaciones . $fila)->applyFromArray([
        "alignment" => ["wrapText" => true, "vertical" => Alignment::VERTICAL_CENTER]
    ]);

    $fila++;
    $numeroEmpleado++;
}

/* LEYENDA */

$filaLeyenda = $fila + 2;

$leyendaIzquierda = [
    ["FES", "DIA FESTIVO"],
    ["PD", "PRIMA DOMINICAL"],
    ["PCG", "PERMISO CON GOCE DE SUELDO"],
    ["A", "ASISTENCIA"],
    ["D", "DESCANSO"],
    ["V", "VACACIONES"],
    ["AA", "DOBLE ASISTENCIA POR SAFARI NOCTURNO"],
];

$leyendaDerecha = [
    ["F", "FALTA"],
    ["PSG", "PERMISO SIN GOCE"],
    ["R", "RETARDO"],
    ["INC", "INCAPACIDAD"],
    ["M", "MATERNIDAD"],
    ["P", "PATERNIDAD"],
    ["HO", "HOME OFFICE"],
    ["CUM", "CUMPLEAÑOS"],
    ["DT", "DESCANSO TRABAJADO"],
];

foreach ($leyendaIzquierda as $i => $item) {

    [$codigo, $texto] = $item;
    $filaActual = $filaLeyenda + $i;
    $def = $CODIGOS[$codigo] ?? null;

    $hoja->setCellValue("D" . $filaActual, $codigo);
    $hoja->setCellValue("E" . $filaActual, $texto);

    if ($def !== null) {
        $hoja->getStyle("D" . $filaActual)->applyFromArray([
            "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => $def["color"]]],
            "font" => ["bold" => true, "color" => ["rgb" => $def["fuente"]]],
            "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER]
        ]);
    }
}

foreach ($leyendaDerecha as $i => $item) {

    [$codigo, $texto] = $item;
    $filaActual = $filaLeyenda + $i;
    $def = $CODIGOS[$codigo] ?? null;

    $hoja->setCellValue("H" . $filaActual, $codigo);
    $hoja->setCellValue("I" . $filaActual, $texto);

    if ($def !== null) {
        $hoja->getStyle("H" . $filaActual)->applyFromArray([
            "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => $def["color"]]],
            "font" => ["bold" => true, "color" => ["rgb" => $def["fuente"]]],
            "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER]
        ]);
    }
}

/* AJUSTES DE FORMATO */

$hoja->getColumnDimension("A")->setWidth(4);
$hoja->getColumnDimension("B")->setWidth(28);
$hoja->getColumnDimension("C")->setWidth(20);
$hoja->getColumnDimension($letraObservaciones)->setWidth(35);


foreach ($dias as $letra) {
    $hoja->getColumnDimension($letra)->setWidth(4);
}

$hoja->freezePane("D7");

/* ==========================================================
   GUARDAR Y DESCARGAR EL ARCHIVO
========================================================== */

$nombreArchivo = "PRENOMINA_QUINCENA_" . $numeroQuincena . "_" . $anio . ".xlsx";

// Descarta cualquier cosa que se haya impreso antes (warnings, BOM, etc.)
if (ob_get_length() !== false) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
header('Content-Transfer-Encoding: binary');

$writer = new Xlsx($excel);
$writer->save('php://output');
exit;
