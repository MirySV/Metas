<?php
include "conexion.php";
session_start();

if (!isset($_SESSION['username'])) {
    exit("Sesión no válida.");
}

$id_empleado = (int)($_POST['id_empleado'] ?? $_GET['id_empleado'] ?? 0);
$inicio = $_POST['inicio'] ?? $_GET['inicio'] ?? '';
$fin = $_POST['fin'] ?? $_GET['fin'] ?? '';

$sql = "SELECT r.id_registro,r.fecha,r.hora_entrada,t.nombre,r.tipo_registro,r.tipo_descanso FROM registros r INNER JOIN tiendas t ON r.id_tienda_actual = t.id_tienda WHERE r.id_empleado = ? AND r.fecha BETWEEN ? AND ? AND r.hora_entrada = (SELECT MAX(r2.hora_entrada)FROM registros r2 WHERE r2.id_empleado = r.id_empleado AND r2.fecha = r.fecha)ORDER BY r.fecha DESC";

$stmt = mysqli_prepare($conec, $sql);
mysqli_stmt_bind_param($stmt, "iss", $id_empleado, $inicio, $fin);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
?>

<div class="container-fluid">

    <div id="mensajeDescanso"></div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width:120px;">
                        <i class="bi bi-calendar-event"></i><br>Fecha
                    </th>
                    <th style="width:110px;">
                        <i class="bi bi-clock"></i><br>Hora
                    </th>
                    <th>
                        <i class="bi bi-shop"></i><br>Tienda
                    </th>
                    <th style="width:140px;">
                        <i class="bi bi-fingerprint"></i><br>Registro
                    </th>
                    <th style="width:320px;">
                        <i class="bi bi-cup-hot"></i><br>Motivo
                    </th>
                </tr>
            </thead>

            <tbody>

                <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>

                    <tr>
                        <td class="text-center fw-semibold">
                            <?= date("d/m/Y", strtotime($row['fecha'])) ?>
                        </td>

                        <td class="text-center">
                            <?= substr($row['hora_entrada'], 0, 5) ?>
                        </td>

                        <td><?= $row['nombre'] ?></td>
                        <td class="text-center">
                            <?php if ($row['tipo_registro'] == "DESCANSO") { ?>
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    DESCANSO
                                </span>
                            <?php } elseif ($row['tipo_registro'] == "FALTA") { ?>
                                <span class="badge bg-danger px-3 py-2">
                                    FALTA
                                </span>
                            <?php } else { ?>
                                <span class="badge bg-success px-3 py-2">
                                    <?= $row['tipo_registro'] ?>
                                </span>
                            <?php } ?>
                        </td>

                        <td>

                            <?php if ($row['tipo_registro'] == "FALTA") { ?>

                                <form class="form-descanso">
                                    <div class="input-group input-group-sm">

                                        <select name="tipo_descanso" class="form-select" required>
                                            <option value="">Seleccionar...</option>

                                            <?php
                                            $motivos = [
                                                "PERMISO CON GOCE",
                                                "PERMISO SIN GOCE",
                                                "CUMPLEAÑOS",
                                                "VACACIONES",
                                                "INCAPACIDAD",
                                                "MATERNIDAD",
                                                "PATERNIDAD"
                                            ];

                                            foreach ($motivos as $motivo) {
                                                $sel = ($row['tipo_descanso'] == $motivo) ? "selected" : "";
                                                echo "<option value='$motivo' $sel>$motivo</option>";
                                            }
                                            ?>
                                        </select>

                                        <input type="hidden" name="id_registro" value="<?= $row['id_registro'] ?>">

                                        <button class="btn btn-primary guardar-btn" type="submit">
                                            <i class="bi bi-save"></i>
                                        </button>

                                    </div>
                                </form>

                            <?php } elseif ($row['tipo_registro'] == "NORMAL") { ?>

                                <form class="form-descanso">
                                    <div class="input-group input-group-sm">

                                        <select name="tipo_descanso" class="form-select" required>
                                            <option value="">Seleccionar...</option>

                                            <?php
                                            $motivos = [
                                                "FESTIVO",
                                                "DESCANSO TRABAJADO",
                                                "DOBLE ASISTENCIA POR SAFARI NOCTURNO"
                                            ];

                                            foreach ($motivos as $motivo) {
                                                $sel = ($row['tipo_descanso'] == $motivo) ? "selected" : "";
                                                echo "<option value='$motivo' $sel>$motivo</option>";
                                            }
                                            ?>
                                        </select>

                                        <input type="hidden" name="id_registro" value="<?= $row['id_registro'] ?>">

                                        <button class="btn btn-primary guardar-btn" type="submit">
                                            <i class="bi bi-save"></i>
                                        </button>

                                    </div>
                                </form>

                            <?php } else { ?>

                                <span class="text-muted">—</span>

                            <?php } ?>

                        </td>
                    </tr>

                <?php } ?>

            </tbody>
        </table>
    </div>