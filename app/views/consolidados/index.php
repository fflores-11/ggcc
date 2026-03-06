<?php
/**
 * Vista: Consolidado de Pagos (Matriz)
 */

$title = 'Consolidado de Pagos';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Consolidado de Pagos</h4>
    <a href="consolidados.php?action=exportar" class="btn btn-success">
        <i class="bi bi-file-earmark-excel me-2"></i>Exportar a Excel
    </a>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="consolidados.php" class="row align-items-end">
        <div class="col-md-4">
            <label class="form-label">Comunidad <span class="text-danger">*</span></label>
            <select name="comunidad_id" class="form-select" required onchange="this.form.submit()">
                <option value="">Seleccione una comunidad...</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= (isset($_GET['comunidad_id']) && $_GET['comunidad_id'] == $com['id']) ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Año</label>
            <select name="anio" class="form-select" onchange="this.form.submit()">
                <?php foreach (getYearList(2, 1) as $year): ?>
                    <option value="<?= $year ?>" <?= $year == $anio ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-search me-2"></i>Ver
            </button>
        </div>
    </form>
</div>

<?php if ($comunidadId && !empty($matriz)): ?>
    <!-- Info de Comunidad -->
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><?= e($comunidad['nombre']) ?></h5>
                <p class="mb-0"><?= e($comunidad['direccion']) ?>, <?= e($comunidad['comuna']) ?></p>
            </div>
            <div class="text-end">
                <span class="badge bg-success fs-6"><?= formatMoney($totales['gran_total_pagado'] ?? 0) ?> Recaudado</span><br>
                <span class="badge bg-danger fs-6 mt-1"><?= formatMoney($totales['gran_total_pendiente'] ?? 0) ?> Pendiente</span>
            </div>
        </div>
    </div>

    <!-- Matriz de Pagos -->
    <div class="table-container">
        <div class="table-body" style="overflow-x: auto;">
            <table class="table table-bordered table-hover mb-0" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th style="min-width: 250px;">Propiedad / Dueño</th>
                        <?php foreach ($meses as $mes): ?>
                            <th class="text-center" style="min-width: 100px;">
                                <?= getMonthName($mes) ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-end">Total Pagado</th>
                        <th class="text-end">Total Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matriz as $fila): ?>
                        <tr>
                            <td>
                                <strong><?= e($fila['propiedad']['nombre']) ?></strong><br>
                                <small class="text-muted"><?= e($fila['propiedad']['nombre_dueno']) ?></small>
                            </td>
                            <?php foreach ($meses as $mes): ?>
                                <?php if (isset($fila['meses'][$mes])): ?>
                                    <?php $mesData = $fila['meses'][$mes]; ?>
                                    <td class="text-center">
                                        <?php if ($mesData['estado'] === 'Pagado'): ?>
                                            <span class="badge bg-success" title="Pagado: <?= formatMoney($mesData['monto']) ?>">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning" title="Pendiente: <?= formatMoney($mesData['monto']) ?>">
                                                P
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td class="text-center">
                                        <span class="text-muted">-</span>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td class="text-end fw-bold text-success">
                                <?= $fila['total_pagado'] > 0 ? formatMoney($fila['total_pagado']) : '-' ?>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                <?= $fila['total_pendiente'] > 0 ? formatMoney($fila['total_pendiente']) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-primary">
                    <tr>
                        <td class="fw-bold">TOTALES</td>
                        <?php foreach ($meses as $mes): ?>
                            <td class="text-center">
                                <?php if (isset($totales['totales'][$mes])): ?>
                                    <?php $mesTotal = $totales['totales'][$mes]; ?>
                                    <div>
                                        <span class="badge bg-success" title="Recaudado">
                                            <?= formatMoney($mesTotal['pagado']) ?>
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-danger" title="Pendiente">
                                            <?= formatMoney($mesTotal['pendiente']) ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-end fw-bold fs-5">
                            <?= formatMoney($totales['gran_total_pagado'] ?? 0) ?>
                        </td>
                        <td class="text-end fw-bold fs-5">
                            <?= formatMoney($totales['gran_total_pendiente'] ?? 0) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="mt-3">
        <small class="text-muted">
            <span class="badge bg-success me-2"><i class="bi bi-check-lg"></i></span> Pagado
            <span class="badge bg-warning text-dark ms-3 me-2">P</span> Pendiente
            <span class="text-muted ms-3">-</span> Sin deuda registrada
        </small>
    </div>

<?php elseif ($comunidadId): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No hay datos de deudas para esta comunidad en el año <?= $anio ?>. 
        <a href="pagos.php">Genere deudas mensuales primero</a>.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Seleccione una comunidad y año para ver el consolidado de pagos.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
