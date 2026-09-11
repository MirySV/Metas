<?php

/**
 * generar_periodo.php
 * ---------------------------------------------------------
 * Automatiza el llenado inicial de un periodo (temporada o puente)
 * RECIEN CREADO, calculando:
 *
 *   - metaYearAnterior  = comparativos.meta        del mismo periodo, año anterior
 *   - cantTempActYear   = comparativos.cantTempAct del mismo periodo, año anterior
 *   - cantTempAnterior  = comparativos.cantTempAct del periodo 2 lugares atrás
 *                         en la secuencia cronológica (temporadas + puentes)
 *   - puenteAnterior    = comparativos.cantTempAct del periodo 1 lugar atrás
 *                         en esa misma secuencia
 *
 * y con eso crea/actualiza:
 *   1) Una fila en `comparativos` por cada tienda (incluyendo `meta` recalculada)
 *   2) Una fila en `comparativos_dia` por cada tienda y cada día del periodo,
 *      copiando esos mismos 4 valores (meta_dia se sigue calculando aparte
 *      con los visitantes de taquilla, como ya hace ventas_pax.php / ventas_puentes.php)
 *
 * USO:
 *   generar_periodo.php?id_temporada=XX
 *   generar_periodo.php?id_puente=XX
 *
 * Se ejecuta UNA vez, justo después de dar de alta el periodo nuevo en la
 * tabla `temporadas` o `puentes` (con su fecha_inicio/fecha_fin ya definidas),
 * y ANTES de abrir ventas_pax.php / ventas_puentes.php para capturar el día a día.
 *
 * IMPORTANTE (bootstrap 2026->2027):
 *   La primera vez que corras esto para un periodo cuyo "año anterior" con el
 *   mismo nombre todavía no existe en la BD (ej. la primerísima vez que se usa
 *   el sistema), el script se detiene y te avisa: para esos casos sigues
 *   metiendo metaYearAnterior/cantTempActYear a mano, como ya lo haces hoy.
 *   A partir de que exista un año completo de historia, esto ya no hará falta.
 */

session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo "No tienes permisos para ejecutar esta accion";
    exit();
}

include 'conexion.php';

$id_temporada = $_GET['id_temporada'] ?? ($_POST['id_temporada'] ?? 0);
$id_puente    = $_GET['id_puente']    ?? ($_POST['id_puente']    ?? 0);

if ($id_temporada == 0 && $id_puente == 0) {
    exit("Debes indicar id_temporada o id_puente en la URL.");
}

if ($id_temporada != 0 && $id_puente != 0) {
    exit("Indica solo uno de los dos: id_temporada o id_puente, no ambos.");
}

// -----------------------------------------------------------------
// 1. Datos del periodo actual (nombre, fechas)
// -----------------------------------------------------------------
if ($id_temporada != 0) {
    $campoPeriodo = 'id_temporada';
    $valorPeriodo = $id_temporada;
    $q = mysqli_query($conec, "SELECT temporada AS nombre, fecha_inicio, fecha_fin FROM temporadas WHERE id_temporada = '$id_temporada'");
} else {
    $campoPeriodo = 'id_puente';
    $valorPeriodo = $id_puente;
    $q = mysqli_query($conec, "SELECT puente AS nombre, fecha_inicio, fecha_fin FROM puentes WHERE id_puente = '$id_puente'");
}

$periodoActual = mysqli_fetch_assoc($q);

if (!$periodoActual) {
    exit("El periodo indicado no existe en la tabla correspondiente.");
}

$nombreActual      = $periodoActual['nombre'];
$fechaInicioActual = $periodoActual['fecha_inicio'];
$fechaFinActual    = $periodoActual['fecha_fin'];
$nombreEsc         = mysqli_real_escape_string($conec, $nombreActual);

// -----------------------------------------------------------------
// 2. Secuencia cronológica de TODOS los periodos anteriores al actual
//    (temporadas + puentes juntos, ordenados por fecha_inicio DESC)
// -----------------------------------------------------------------
$sqlAnteriores = "(SELECT id_temporada AS id, 'temporada' AS tipo, temporada AS nombre, fecha_inicio FROM temporadas WHERE fecha_inicio < '$fechaInicioActual') UNION ALL
    (SELECT id_puente AS id, 'puente' AS tipo, puente AS nombre, fecha_inicio FROM puentes WHERE fecha_inicio < '$fechaInicioActual') ORDER BY fecha_inicio DESC";

$resAnteriores = mysqli_query($conec, $sqlAnteriores);

$listaAnteriores = [];
while ($fila = mysqli_fetch_assoc($resAnteriores)) {
    $listaAnteriores[] = $fila;
}

if (count($listaAnteriores) < 2) {
    exit("No hay suficientes periodos anteriores registrados (se necesitan al menos 2) para calcular cantTempAnterior y puenteAnterior automaticamente. Captura este periodo a mano por ahora.");
}

$periodoRank1 = $listaAnteriores[0]; // 1 lugar atras  -> fuente de puenteAnterior
$periodoRank2 = $listaAnteriores[1]; // 2 lugares atras -> fuente de cantTempAnterior

// -----------------------------------------------------------------
// 3. Periodo equivalente del año anterior: mismo nombre, la ocurrencia
//    mas reciente antes de la fecha del periodo actual
// -----------------------------------------------------------------
$sqlAnio = "(SELECT id_temporada AS id, 'temporada' AS tipo, fecha_inicio FROM temporadas WHERE temporada = '$nombreEsc' AND fecha_inicio < '$fechaInicioActual') UNION ALL
    (SELECT id_puente AS id, 'puente' AS tipo, fecha_inicio FROM puentes WHERE puente = '$nombreEsc' AND fecha_inicio < '$fechaInicioActual') ORDER BY fecha_inicio DESCLIMIT 1";

$resAnio = mysqli_query($conec, $sqlAnio);
$periodoAnioAnterior = mysqli_fetch_assoc($resAnio);

if (!$periodoAnioAnterior) {
    exit("No se encontro un periodo con el mismo nombre ('$nombreActual') antes de esta fecha. Esta es probablemente la primera ocurrencia de este periodo en el sistema: captura metaYearAnterior y cantTempActYear a mano esta unica vez.");
}

// -----------------------------------------------------------------
// 4. Helper: trae {id_tienda => [meta, cantTempAct]} de comparativos
//    para un periodo dado (temporada o puente)
// -----------------------------------------------------------------
function obtenerComparativosPorPeriodo($conec, $tipo, $id) {
    $campo = $tipo == 'temporada' ? 'id_temporada' : 'id_puente';
    $id    = (int) $id;
    $sql   = "SELECT id_tienda, meta, cantTempAct FROM comparativos WHERE $campo = '$id'";
    $res   = mysqli_query($conec, $sql);
    $datos = [];
    while ($f = mysqli_fetch_assoc($res)) {
        $datos[$f['id_tienda']] = $f;
    }
    return $datos;
}

$datosAnioAnterior = obtenerComparativosPorPeriodo($conec, $periodoAnioAnterior['tipo'], $periodoAnioAnterior['id']);
$datosRank1        = obtenerComparativosPorPeriodo($conec, $periodoRank1['tipo'], $periodoRank1['id']);
$datosRank2        = obtenerComparativosPorPeriodo($conec, $periodoRank2['tipo'], $periodoRank2['id']);

if (empty($datosAnioAnterior)) {
    exit("El periodo del año anterior ('$nombreActual') no tiene filas en comparativos todavia. Cierra/calcula ese periodo primero.");
}

// -----------------------------------------------------------------
// 5. Recorrer tiendas: calcular e insertar/actualizar comparativos
//    y comparativos_dia
// -----------------------------------------------------------------
$tiendas = mysqli_query($conec, "SELECT id_tienda FROM tiendas");

$reporte = [];

while ($t = mysqli_fetch_assoc($tiendas)) {

    $id_tienda = $t['id_tienda'];

    $metaYearAnterior = $datosAnioAnterior[$id_tienda]['meta']        ?? 0;
    $cantTempActYear  = $datosAnioAnterior[$id_tienda]['cantTempAct'] ?? 0;
    $cantTempAnterior = $datosRank2[$id_tienda]['cantTempAct']        ?? 0;
    $puenteAnterior   = $datosRank1[$id_tienda]['cantTempAct']        ?? 0;

    $meta = round(($cantTempActYear + $cantTempAnterior + $puenteAnterior) / 3, 2);

    // --- comparativos (fila general del periodo) ---
    $existe = mysqli_query($conec, "SELECT id_comparativos FROM comparativos WHERE $campoPeriodo = '$valorPeriodo' AND id_tienda = '$id_tienda'");

    if (mysqli_num_rows($existe) > 0) {
        mysqli_query($conec, "UPDATE comparativos SET metaYearAnterior = '$metaYearAnterior',cantTempActYear  = '$cantTempActYear',cantTempAnterior = '$cantTempAnterior',puenteAnterior = '$puenteAnterior', meta = '$meta' WHERE $campoPeriodo = '$valorPeriodo' AND id_tienda = '$id_tienda'");
    } else {
        mysqli_query($conec, "INSERT INTO comparativos ($campoPeriodo, id_tienda, metaYearAnterior, cantTempActYear, cantTempAnterior, puenteAnterior, meta) VALUES
 ('$valorPeriodo', '$id_tienda', '$metaYearAnterior', '$cantTempActYear', '$cantTempAnterior', '$puenteAnterior', '$meta')");
    }

    // --- comparativos_dia (una fila por cada dia del periodo) ---
    $fechaCursor = strtotime($fechaInicioActual);
    $fechaLimite = strtotime($fechaFinActual);

    while ($fechaCursor <= $fechaLimite) {
        $fechaDia = date('Y-m-d', $fechaCursor);

        $existeDia = mysqli_query($conec, "SELECT id_comparativosDia FROM comparativos_dia WHERE $campoPeriodo = '$valorPeriodo' AND id_tienda = '$id_tienda' AND fecha = '$fechaDia'");

        if (mysqli_num_rows($existeDia) > 0) {
            mysqli_query($conec, "UPDATE comparativos_dia SET metaYearAnterior = '$metaYearAnterior', cantTempActYear  = '$cantTempActYear',cantTempAnterior = '$cantTempAnterior',puenteAnterior = '$puenteAnterior' WHERE $campoPeriodo = '$valorPeriodo' AND id_tienda = '$id_tienda' AND fecha = '$fechaDia'");
        } else {
            mysqli_query($conec, "INSERT INTO comparativos_dia($campoPeriodo, id_tienda, fecha, metaYearAnterior, cantTempActYear, cantTempAnterior, puenteAnterior) VALUES('$valorPeriodo', '$id_tienda', '$fechaDia', '$metaYearAnterior', '$cantTempActYear', '$cantTempAnterior', '$puenteAnterior')");
        }

        $fechaCursor = strtotime('+1 day', $fechaCursor);
    }

    $reporte[] = "Tienda $id_tienda -> metaYearAnterior=$metaYearAnterior, cantTempActYear=$cantTempActYear, cantTempAnterior=$cantTempAnterior, puenteAnterior=$puenteAnterior, meta=$meta";
}

echo "<h3>Periodo procesado: $nombreActual ($fechaInicioActual a $fechaFinActual)</h3>";
echo "<p>Año anterior detectado: {$periodoAnioAnterior['tipo']} id {$periodoAnioAnterior['id']}</p>";
echo "<p>puenteAnterior tomado de: {$periodoRank1['tipo']} '{$periodoRank1['nombre']}'</p>";
echo "<p>cantTempAnterior tomado de: {$periodoRank2['tipo']} '{$periodoRank2['nombre']}'</p>";
echo "<pre>" . implode("\n", $reporte) . "</pre>";
echo "<p>Listo. Ya puedes abrir ventas_pax.php o ventas_puentes.php para este periodo y continuar con la captura del dia a dia.</p>";

header("Location: ventas_pax.php?id_temporada=$valorPeriodo");
exit();