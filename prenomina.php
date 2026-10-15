<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include "conexion.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$rol = $_SESSION['rol'] ?? '';

if (!in_array($rol, ['admin', 'supervisora'])) {
    header("Location: colaboradores.php");
    exit;
}

date_default_timezone_set('America/Mexico_City');

/* GUARDAR PRENÓMINA*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    $entrada = json_decode(file_get_contents('php://input'), true);
    $accion = $entrada['accion'] ?? '';

    if ($accion === 'guardar') {

        if ($rol !== 'supervisora' && $rol !== 'admin') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tienes permisos para guardar la prenómina.'
            ]);
            exit;
        }

        $inicio = $entrada['inicio'] ?? '';
        $fin = $entrada['fin'] ?? '';
        $tienda = $entrada['tienda'] ?? 'todas';
        $filas = $entrada['filas'] ?? [];

        if ($inicio === '' || $fin === '' || !is_array($filas)) {

            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos.'
            ]);

            exit;
        }

        $id_usuario = (int)($_SESSION['idUsuario'] ?? 0);

        try {

            $conec->begin_transaction();
            $sql = "INSERT INTO prenomina_ediciones(inicio,fin,id_empleado,tienda,dias,horas_extra,observaciones,id_usuario)VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE tienda = VALUES(tienda), dias = VALUES(dias), horas_extra = VALUES(horas_extra), observaciones = VALUES(observaciones), id_usuario = VALUES(id_usuario), fecha_actualizacion = CURRENT_TIMESTAMP";
            $stmt = $conec->prepare($sql);

            if (!$stmt) {
                throw new Exception($conec->error);
            }

            foreach ($filas as $fila) {

                $id_empleado = (int)($fila['id_empleado'] ?? 0);

                if ($id_empleado <= 0) {
                    continue;
                }

                // Seguridad por zona: una supervisora solo puede guardar
                // información de empleados de su propia zona.
                if ($rol === 'supervisora') {
                    $id_zona_sesion = (int)($_SESSION['id_zona'] ?? 0);
                    $stmtZonaEmpleado = $conec->prepare("SELECT 1 FROM empleados e INNER JOIN tiendas t ON t.id_tienda = e.id_tienda_actual WHERE e.id_empleado = ? AND t.id_zona = ? LIMIT 1");
                    if (!$stmtZonaEmpleado) {
                        throw new Exception($conec->error);
                    }
                    $stmtZonaEmpleado->bind_param("ii", $id_empleado, $id_zona_sesion);
                    $stmtZonaEmpleado->execute();
                    $empleadoAutorizado = $stmtZonaEmpleado->get_result()->num_rows > 0;
                    $stmtZonaEmpleado->close();
                    if (!$empleadoAutorizado) {
                        continue;
                    }
                }

                $dias = $fila['dias'] ?? [];

                if (!is_array($dias)) {
                    $dias = [];
                }

                $diasJson = json_encode(
                    $dias,
                    JSON_UNESCAPED_UNICODE
                );

                $horas_extra =
                    (float)($fila['horas_extra'] ?? 0);

                $observaciones =
                    trim($fila['observaciones'] ?? '');

                $stmt->bind_param(
                    "ssissdsi",
                    $inicio,
                    $fin,
                    $id_empleado,
                    $tienda,
                    $diasJson,
                    $horas_extra,
                    $observaciones,
                    $id_usuario
                );

                $stmt->execute();
            }

            $stmt->close();

            $conec->commit();

            echo json_encode([
                'success' => true,
                'mensaje' => 'La prenómina se guardó correctamente.'
            ]);
        } catch (Exception $e) {

            $conec->rollback();

            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar la prenómina: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    exit;
}


/* =========================================================
   DATOS DE LA PRENÓMINA
   ========================================================= */

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-d');
$tienda = $_GET['tienda'] ?? 'todas';
$id_zona_filtro = isset($_GET['zona']) ? (int)$_GET['zona'] : 0;
$id_zona_sesion = (int)($_SESSION['id_zona'] ?? 0);

// La supervisora nunca puede elegir otra zona; se fuerza la zona de su sesión.
if ($rol === 'supervisora') {
    $id_zona_filtro = $id_zona_sesion;
}

/* =========================================================
   ZONAS Y TIENDAS
   ========================================================= */

$zonas = [];

if ($rol === 'admin') {
    $resultadoZonas = $conec->query(
        "SELECT DISTINCT z.id_zona, z.nombre
         FROM zonas AS z
         INNER JOIN tiendas t ON t.id_zona = z.id_zona
         ORDER BY z.id_zona ASC"
    );

    if ($resultadoZonas) {
        while ($filaZona = $resultadoZonas->fetch_assoc()) {
            $zonas[] = $filaZona;
        }
    }
}

if ($rol === 'supervisora') {
    $stmt = $conec->prepare(
        "SELECT id_tienda, nombre FROM tiendas WHERE id_zona = ? ORDER BY nombre"
    );
    $stmt->bind_param("i", $id_zona_sesion);
    $stmt->execute();
    $resultadoTiendas = $stmt->get_result();
} elseif ($rol === 'admin' && $id_zona_filtro > 0) {
    $stmt = $conec->prepare(
        "SELECT id_tienda, nombre FROM tiendas WHERE id_zona = ? ORDER BY nombre"
    );
    $stmt->bind_param("i", $id_zona_filtro);
    $stmt->execute();
    $resultadoTiendas = $stmt->get_result();
} else {
    $resultadoTiendas = $conec->query("SELECT id_tienda, nombre FROM tiendas ORDER BY nombre");
}

$tiendas = [];
while ($row = $resultadoTiendas->fetch_assoc()) {
    $tiendas[] = $row;
}

/* =========================================================
   EMPLEADOS
   ========================================================= */

$empleados = [];

$sql = "SELECT
            e.id_empleado,
            e.nombre,
            e.tipo_jornada,
            e.descanso,
            t.nombre AS tienda
        FROM empleados e
        INNER JOIN tiendas t ON e.id_tienda_actual = t.id_tienda
        WHERE e.status = 1";
$parametros = [];
$tipos = '';

// Filtro obligatorio para supervisoras y filtro seleccionable para admin.
if ($rol === 'supervisora' || ($rol === 'admin' && $id_zona_filtro > 0)) {
    $sql .= " AND t.id_zona = ?";
    $parametros[] = $id_zona_filtro;
    $tipos .= 'i';
}
if ($tienda !== 'todas') {
    // Si una tienda fue manipulada en la URL, la zona sigue restringiendo resultados.
    $sql .= " AND t.nombre = ?";
    $parametros[] = $tienda;
    $tipos .= 's';
}
$sql .= " ORDER BY e.nombre";
$stmt = $conec->prepare($sql);
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();

$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {
    $empleados[] = $row;
}

$stmt->close();


/* =========================================================
   REGISTROS NORMALES
   ========================================================= */

$registros = [];

$sqlRegistros = "SELECT id_registro,
        id_empleado,
        fecha,
        hora_entrada,
        tipo_registro,
        tipo_descanso,
        horas_extra
    FROM registros
    WHERE fecha BETWEEN ? AND ?
    ORDER BY fecha ASC
";

$stmt = $conec->prepare($sqlRegistros);

$stmt->bind_param(
    "ss",
    $inicio,
    $fin
);

$stmt->execute();

$resRegistros = $stmt->get_result();

while ($row = $resRegistros->fetch_assoc()) {

    $registros[$row['id_empleado']][$row['fecha']] = $row;
}

$stmt->close();


/* =========================================================
   CAMBIOS GUARDADOS DE PRENÓMINA
   ========================================================= */

$ediciones = [];

$sqlEdiciones = "SELECT
        id_empleado,
        dias,
        horas_extra,
        observaciones,
        inicio,
        fin,
        fecha_actualizacion
    FROM prenomina_ediciones
    WHERE inicio <= ?
      AND fin >= ?
    ORDER BY fecha_actualizacion ASC, inicio ASC
";

$stmt = $conec->prepare($sqlEdiciones);

// Recupera ediciones de periodos que se crucen con el rango consultado.
// El orden permite que una edición más reciente prevalezca para la misma fecha.
$stmt->bind_param(
    "ss",
    $fin,
    $inicio
);

$stmt->execute();

$resEdiciones = $stmt->get_result();

while ($row = $resEdiciones->fetch_assoc()) {

    $diasGuardados =
        json_decode(
            $row['dias'],
            true
        );

    if (!is_array($diasGuardados)) {
        $diasGuardados = [];
    }

    $idEmpleadoEdicion = (int)$row['id_empleado'];

    if (!isset($ediciones[$idEmpleadoEdicion])) {
        $ediciones[$idEmpleadoEdicion] = [
            'dias' => [],
            'horas_extra' => $row['horas_extra'],
            'observaciones' => $row['observaciones']
        ];
    }

    // Combina los códigos por fecha, en vez de exigir que coincidan
    // exactamente las fechas inicial y final guardadas originalmente.
    $ediciones[$idEmpleadoEdicion]['dias'] = array_merge(
        $ediciones[$idEmpleadoEdicion]['dias'],
        $diasGuardados
    );

    // Los datos generales corresponden a la edición más reciente encontrada.
    $ediciones[$idEmpleadoEdicion]['horas_extra'] = $row['horas_extra'];
    $ediciones[$idEmpleadoEdicion]['observaciones'] = $row['observaciones'];
}

$stmt->close();


/* =========================================================
   DÍAS
   ========================================================= */

$dias = [];

$fechaInicio = new DateTime($inicio);
$fechaFin = new DateTime($fin);

while ($fechaInicio <= $fechaFin) {

    $dias[] =
        $fechaInicio->format('Y-m-d');

    $fechaInicio->modify('+1 day');
}


/* =========================================================
   CÓDIGOS
   ========================================================= */

$codigos = [

    'A' =>
    'NORMAL',

    'D' =>
    'DESCANSO',

    'F' =>
    'FALTA',

    'DT' =>
    'DESCANSO TRABAJADO',

    'PCG' =>
    'PERMISO CON GOCE',

    'PSG' =>
    'PERMISO SIN GOCE',

    'INC' =>
    'INCAPACIDAD',

    'V' =>
    'VACACIONES',

    'FES' =>
    'FESTIVO',

    'PD' =>
    'PRIMA DOMINICAL',

    'R' =>
    'RETARDO',

    'M' =>
    'MATERNIDAD',

    'P' =>
    'PATERNIDAD',

    'HO' =>
    'HOME OFFICE',

    'CUM' =>
    'CUMPLEAÑOS',

    'AA' =>
    'DOBLE ASISTENCIA POR SAFARI NOCTURNO'
];


/* =========================================================
   COLORES
   ========================================================= */

$colores = [

    'A' =>
    '#ffffff',

    'D' =>
    '#ffffff',

    'F' =>
    '#ff0000',

    'PD' =>
    '#00B050',

    'FES' =>
    '#FFFF00',

    'DT' =>
    '#0070C0',

    'PCG' =>
    '#F4B183',

    'PSG' =>
    '#D9A5FF',

    'INC' =>
    '#FFC000',

    'V' =>
    '#808080',

    'R' =>
    '#9DC3E6',

    'M' =>
    '#FF66CC',

    'P' =>
    '#C6E0B4',

    'HO' =>
    '#ED7D31',

    'CUM' =>
    '#00B0F0',

    'AA' =>
    '#7F5B3E '
];


/* =========================================================
   FUNCIÓN PARA DETERMINAR CÓDIGO
   ========================================================= */

function codigoRegistro($registro, $fecha = null)
{
    if (!$registro) {
        return 'F';
    }

    if (!empty($registro['tipo_descanso'])) {

        $tipo =
            strtoupper(
                trim(
                    $registro['tipo_descanso']
                )
            );

        $mapa = [

            'DESCANSO' =>
            'D',

            'FALTA' =>
            'F',

            'PRIMA DOMINICAL' =>
            'PD',

            'FESTIVO' =>
            'FES',

            'PERMISO CON GOCE' =>
            'PCG',

            'PERMISO SIN GOCE' =>
            'PSG',

            'INCAPACIDAD' =>
            'INC',

            'VACACIONES' =>
            'V',

            'RETARDO' =>
            'R',

            'MATERNIDAD' =>
            'M',

            'PATERNIDAD' =>
            'P',

            'HOME OFFICE' =>
            'HO',

            'CUMPLEAÑOS' =>
            'CUM',

            'DESCANSO TRABAJADO' =>
            'DT',

            'DOBLE ASISTENCIA POR SAFARI NOCTURNO' =>
            'AA'
        ];

        if (isset($mapa[$tipo])) {
            return $mapa[$tipo];
        }
    }

    $tipo =
        strtoupper(
            trim(
                $registro['tipo_registro'] ?? ''
            )
        );

    switch ($tipo) {

        case 'NORMAL':
            // Una asistencia normal registrada en domingo se identifica como prima dominical.
            if ($fecha !== null) {
                $fechaObj = new DateTime($fecha);
                if ((int)$fechaObj->format('N') === 7) {
                    return 'PD';
                }
            }
            return 'A';

        case 'DESCANSO':
            return 'D';

        case 'FALTA':
            return 'F';

        default:
            return 'F';
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CDN Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icoons de Boostrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Favicon (Icono de la pagina web)-->
    <link rel="shortcut icon" href="./assets/img/shop.svg" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!-- Tipografia Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Averia+Libre:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Glory:ital,wght@0,100..800;1,100..800&family=Macondo&family=Marcellus&display=swap" rel="stylesheet">
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <title>Tiendas Africam Safari</title>

    <style>
        body {
            background: #f5f6f8;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #15222e;
            z-index: 1000;
        }

        .sidebar-logo {
            width: 130px;
            display: block;
            margin: 20px auto;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 13px 20px;
            transition: .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #3d4b5a;
        }

        .sidebar a i {
            margin-right: 8px;
        }

        .contenido {
            margin-left: 250px;
            padding: 25px;
        }

        .tabla-prenomina {
            background: white;
            border-radius: 10px;
            overflow: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .tabla-prenomina table {
            margin: 0;
            min-width: 1200px;
        }

        .tabla-prenomina th,
        .tabla-prenomina td {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            font-size: 12px;
        }

        .empleado {
            text-align: left !important;
            min-width: 230px;
            position: sticky;
            left: 0;
            background: white !important;
            z-index: 5;
        }

        .tienda {
            min-width: 150px;
        }

        .dia {
            width: 48px;
            min-width: 48px;
            padding: 4px !important;
        }

        .celda-dia {
            width: 44px;
            height: 36px;
            border: 1px solid #aaa;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
        }

        .horas-extra {
            width: 80px;
        }

        .observaciones {
            min-width: 250px;
        }

        .leyenda span {
            display: inline-block;
            padding: 5px 8px;
            margin: 3px;
            border: 1px solid #aaa;
            border-radius: 4px;
            font-size: 11px;
        }

        /* Encabezado general de la prenómina */
        .encabezado-prenomina th {
            text-align: center;
            vertical-align: middle;
            padding: 8px 5px;
            font-weight: bold;
            border: 1px solid #b8c7d9;
        }

        /* Columnas de nombre y tienda */
        .encabezado-prenomina .columna-fija {
            background-color: #1f3a5f !important;
            color: white !important;
        }

        /* Días de la semana y números */
        .encabezado-prenomina .columna-dia {
            background-color: #d9e8f5;
            color: #1f3a5f;
        }

        /* Horas extra */
        .encabezado-prenomina .columna-extra {
            background-color: #287a68;
            color: white;
        }

        /* Observaciones */
        .encabezado-prenomina .columna-observaciones {
            background-color: #6c757d;
            color: white;
        }

        @media(max-width: 768px) {

            .sidebar {
                width: 70px;
            }

            .sidebar span {
                display: none;
            }

            .sidebar-logo {
                width: 50px;
            }

            .contenido {
                margin-left: 70px;
                padding: 15px;
            }
        }
    </style>

</head>

<body>


    <!-- =====================================================
     SIDEBAR
     ===================================================== -->

    <div class="sidebar">
        <img
            src="assets/img/logo.png"
            class="sidebar-logo"
            alt="Africam Safari">

        <div class="text-center text-white mb-4 px-2">
            <p class="mb-1">Bienvenido/a,</p>
            <strong>
                <?= htmlspecialchars($_SESSION['username'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?>
            </strong>
        </div>

        <a href="index.php">
            <i class="bi bi-house"></i>
            <span>
                Menú principal
            </span>
        </a>


        <a href="colaboradores.php">
            <i class="bi bi-people"></i>
            <span>
                Colaboradores
            </span>
        </a>


        <a href="prenomina.php"
            class="active">
            <i class="bi bi-calendar3"></i>
            <span>
                Prenómina
            </span>
        </a>


        <a href="logout.php"
            class="mt-4">
            <i class="bi bi-box-arrow-right"></i>
            <span>
                Cerrar sesión
            </span>
        </a>
    </div>


    <!-- =====================================================
     CONTENIDO
     ===================================================== -->

    <div class="contenido">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-calendar3"></i>
                    Prenómina
                </h2>
                <small class="text-muted">
                    <?= $rol === 'supervisora'
                        ? 'Edición de prenómina'
                        : 'Consulta y descarga de prenómina'
                    ?>
                </small>
            </div>
        </div>


        <!-- =================================================
         FILTROS
         ================================================= -->

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Inicio
                            </label>
                            <input
                                type="date"
                                name="inicio"
                                class="form-control"
                                value="<?= htmlspecialchars($inicio) ?>"
                                required>
                        </div>


                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Fin
                            </label>
                            <input
                                type="date"
                                name="fin"
                                class="form-control"
                                value="<?= htmlspecialchars($fin) ?>"
                                required>

                        </div>


                        <?php if ($rol === 'admin'): ?>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Zona</label>
                                <select name="zona" class="form-select" onchange="this.form.submit()">
                                    <option value="0" <?= $id_zona_filtro === 0 ? 'selected' : '' ?>>Todas las zonas</option>
                                    <?php foreach ($zonas as $zonaItem): ?>
                                        <option value="<?= (int)$zonaItem['id_zona'] ?>" <?= $id_zona_filtro === (int)$zonaItem['id_zona'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($zonaItem['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-3">

                            <label class="form-label fw-bold">
                                Tienda
                            </label>

                            <select
                                name="tienda"
                                class="form-select">

                                <option value="todas">
                                    Todas
                                </option>

                                <?php foreach ($tiendas as $item): ?>

                                    <option
                                        value="<?= htmlspecialchars($item['nombre']) ?>"
                                        <?= $tienda === $item['nombre']
                                            ? 'selected'
                                            : '' ?>>

                                        <?= htmlspecialchars($item['nombre']) ?>

                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div class="col-md-3">
                            <button
                                type="submit"
                                class="btn btn-primary w-100">
                                <i class="bi bi-search"></i>
                                Consultar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- =================================================
         BOTONES
         ================================================= -->

        <div class="d-flex gap-2 mb-3">

            <?php if ($rol === 'supervisora'): ?>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="guardarCambios()">
                    <i class="bi bi-save"></i>
                    Guardar cambios
                </button>

            <?php endif; ?>


            <?php if ($rol === 'admin'): ?>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="exportarExcel()">

                    <i class="bi bi-file-earmark-excel"></i>
                    Descargar Excel
                </button>
            <?php endif; ?>
        </div>


        <!-- =================================================
         TABLA
         ================================================= -->

        <div class="tabla-prenomina">
            <table class="table table-bordered">
                <thead class="encabezado-prenomina">
                    <tr>
                        <th rowspan="2" class="empleado columna-fija">
                            NOMBRE
                        </th>

                        <th rowspan="2" class="tienda columna-fija">
                            TIENDA
                        </th>

                        <?php foreach ($dias as $dia): ?>
                            <?php
                            $fecha = new DateTime($dia);
                            $diaSemana = (int)$fecha->format('N');

                            $iniciales = [
                                1 => 'L',
                                2 => 'M',
                                3 => 'M',
                                4 => 'J',
                                5 => 'V',
                                6 => 'S',
                                7 => 'D'
                            ];
                            ?>

                            <th class="dia columna-dia">
                                <?= $iniciales[$diaSemana] ?>
                            </th>
                        <?php endforeach; ?>

                        <th rowspan="2" class="columna-extra">
                            HRS EXTRA
                        </th>

                        <th rowspan="2" class="columna-observaciones">
                            OBSERVACIONES
                        </th>
                    </tr>

                    <tr>
                        <?php foreach ($dias as $dia): ?>
                            <th class="dia columna-dia">
                                <?= date('j', strtotime($dia)) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>


                    <?php foreach ($empleados as $empleado): ?>

                        <?php

                        $idEmpleado =
                            $empleado['id_empleado'];

                        $edicion =
                            $ediciones[$idEmpleado]
                            ?? null;

                        ?>

                        <tr
                            data-empleado="<?= $idEmpleado ?>">

                            <td class="empleado fw-bold">

                                <?= htmlspecialchars(
                                    $empleado['nombre']
                                ) ?>

                            </td>

                            <td class="tienda">

                                <?= htmlspecialchars(
                                    $empleado['tienda'] ?? ''
                                ) ?>

                            </td>

                            <?php foreach ($dias as $dia): ?>

                                <?php


                                $registro = $registros[$idEmpleado][$dia] ?? null;

                                $diaSemana = (int) date('N', strtotime($dia));

                                // Empleados que trabajan fines de semana
                                if ((int)$empleado['tipo_jornada'] === 0 && $diaSemana <= 5) {

                                    // Si existe un registro real, respetarlo.
                                    // Si no existe, dejar el día en blanco.
                                    if ($registro === null) {
                                        $codigo = '';
                                    } else {
                                        $codigo = codigoRegistro($registro, $dia);
                                    }
                                } else {

                                    // Para el resto de empleados, conservar la lógica actual.
                                    if (
                                        $edicion &&
                                        isset($edicion['dias'][$dia])
                                    ) {
                                        $codigo = strtoupper(trim((string)$edicion['dias'][$dia]));

                                        // Mantener la prima dominical.
                                        if ($codigo === 'A' && $diaSemana === 7) {
                                            $codigo = 'PD';
                                        }
                                    } else {
                                        $codigo = codigoRegistro($registro, $dia);
                                    }
                                }




                                $background =
                                    $colores[$codigo]
                                    ?? '#ffffff';

                                ?>

                                <td>
                                    <select
                                        class="celda-dia"
                                        data-fecha="<?= $dia ?>"
                                        style="background-color: <?= $background ?>;"
                                        onchange="cambiarColor(this)"
                                        <?= $rol === 'admin' ? 'disabled' : '' ?>>

                                        <option value="" <?= $codigo === '' ? 'selected' : '' ?>></option>

                                        <?php foreach ($codigos as $codigoOpcion => $descripcion): ?>
                                            <option
                                                value="<?= $codigoOpcion ?>"
                                                <?= $codigo === $codigoOpcion ? 'selected' : '' ?>>
                                                <?= $codigoOpcion ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            <?php endforeach; ?>

                            <td>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    class="form-control form-control-sm horas-extra"
                                    value="<?= htmlspecialchars(
                                                $edicion['horas_extra'] ?? 0
                                            ) ?>"
                                    <?= $rol === 'admin'
                                        ? 'readonly'
                                        : '' ?>>

                            </td>

                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm observaciones"
                                    value="<?= htmlspecialchars(
                                                $edicion['observaciones'] ?? ''
                                            ) ?>"
                                    <?= $rol === 'admin'
                                        ? 'readonly'
                                        : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <!-- =================================================
         LEYENDA
         ================================================= -->

        <div class="card mt-4">
            <div class="card-body">
                <h6 class="fw-bold">
                    Leyenda
                </h6>
                <div class="leyenda">
                    <?php foreach ($codigos as $codigo => $descripcion): ?>
                        <span
                            style="background: <?= $colores[$codigo] ?? '#fff' ?>;">
                            <strong>
                                <?= $codigo ?>
                            </strong>
                            -
                            <?= $descripcion ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>


    <script>
        const colores =
            <?= json_encode($colores) ?>;


        /* =====================================================
           CAMBIAR COLOR
           ===================================================== */

        function cambiarColor(select) {
            const codigo =
                select.value;

            select.style.backgroundColor =
                colores[codigo] || '#ffffff';
        }


        /* =====================================================
           OBTENER FILAS
           ===================================================== */

        function obtenerFilas() {
            const filas = [];


            document
                .querySelectorAll(
                    '.tabla-prenomina tbody tr'
                )
                .forEach(function(fila) {

                    const idEmpleado =
                        fila.dataset.empleado;

                    const dias = {};

                    fila
                        .querySelectorAll(
                            '.celda-dia'
                        )
                        .forEach(function(select) {

                            dias[
                                    select.dataset.fecha
                                ] =
                                select.value;
                        });

                    const horasExtra =
                        fila
                        .querySelector(
                            '.horas-extra'
                        )
                        .value;

                    const observaciones =
                        fila
                        .querySelector(
                            '.observaciones'
                        )
                        .value;

                    filas.push({

                        id_empleado: idEmpleado,
                        dias: dias,
                        horas_extra: horasExtra,
                        observaciones: observaciones
                    });
                });
            return filas;
        }


        /* =====================================================
           GUARDAR
           ===================================================== */

        async function guardarCambios() {
            const filas =
                obtenerFilas();
            if (filas.length === 0) {
                alert(
                    'No hay empleados para guardar.'
                );

                return;
            }

            try {
                const respuesta =
                    await fetch(
                        'prenomina.php', {

                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },

                            body: JSON.stringify({
                                accion: 'guardar',
                                inicio: '<?= $inicio ?>',
                                fin: '<?= $fin ?>',
                                tienda: <?= json_encode($tienda) ?>,
                                filas: filas
                            })
                        }
                    );


                const resultado =
                    await respuesta.json();

                if (resultado.success) {
                    alert(
                        resultado.mensaje
                    );

                } else {
                    alert(
                        resultado.mensaje
                    );
                }

            } catch (error) {
                console.error(error);
                alert(
                    'Ocurrió un error al guardar la prenómina.'
                );

            }
        }


        /* =====================================================
           EXPORTAR EXCEL
           ===================================================== */

        async function exportarExcel() {
            const filas =
                obtenerFilas();

            // Tomar las fechas directamente de los campos visibles para exportar
            // exactamente el periodo seleccionado por el usuario.
            const campoInicio = document.querySelector('input[name="inicio"]');
            const campoFin = document.querySelector('input[name="fin"]');

            const datos = {
                inicio: campoInicio ? campoInicio.value : '<?= $inicio ?>',
                fin: campoFin ? campoFin.value : '<?= $fin ?>',
                tienda: <?= json_encode($tienda) ?>,
                zona: <?= json_encode($id_zona_filtro) ?>,
                filas: filas
            };

            if (!datos.inicio || !datos.fin || datos.inicio > datos.fin) {
                alert('Selecciona un periodo válido: la fecha de inicio debe ser anterior o igual a la fecha de fin.');
                return;
            }


            try {

                const respuesta =
                    await fetch(
                        'exportar_rep_excel.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },

                            body: JSON.stringify(datos)
                        }
                    );


                if (!respuesta.ok) {
                    const detalle = await respuesta.text();
                    throw new Error(detalle.trim() || ('El servidor respondió con error HTTP ' + respuesta.status + '.'));
                }

                const tipoContenido = (respuesta.headers.get('Content-Type') || '').toLowerCase();
                if (!tipoContenido.includes('spreadsheetml') && !tipoContenido.includes('application/octet-stream')) {
                    const detalle = await respuesta.text();
                    throw new Error(detalle.trim() || 'El servidor no devolvió un archivo Excel válido. Revisa exportar_rep_excel.php.');
                }

                const blob = await respuesta.blob();
                if (!blob.size) {
                    throw new Error('El archivo Excel llegó vacío. Revisa los errores de PHP del servidor.');
                }

                const url =
                    window.URL.createObjectURL(
                        blob
                    );

                const enlace =
                    document.createElement('a');

                enlace.href =
                    url;

                enlace.download =
                    'PRENOMINA_' + datos.inicio + '_al_' + datos.fin + '.xlsx';

                document.body.appendChild(
                    enlace
                );

                enlace.click();

                enlace.remove();

                window.URL.revokeObjectURL(
                    url
                );

            } catch (error) {
                console.error(error);
                alert('Ocurrió un error al generar el Excel: ' + (error.message || 'error desconocido'));
            }
        }
    </script>
</body>

</html>