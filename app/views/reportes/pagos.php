<?php
/**
 * Vista: Reporte de Pagos
 */

$title = 'Reporte de Pagos por Período';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Reporte de Pagos</h4>
    <a href="reportes.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver a Reportes
    </a>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="reportes.php" class="row align-items-end">
        <input type="hidden" name="action" value="pagos">
        
        <div class="col-md-3">
            <label class="form-label">Comunidad <span class="text-danger">*</span></label>
            <select name="comunidad_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= ($comunidadId ?? 0) == $com['id'] ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == ($mes ?? date('n')) ? 'selected' : '' ?>>
                        <?= getMonthName($i) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Año</label>
            <select name="anio" class="form-select">
                <?php foreach (getYearList(1, 1) as $year): ?>
                    <option value="<?= $year ?>" <?= $year == ($anio ?? date('Y')) ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-success w-100">
                <i class="bi bi-search me-2"></i>Generar
            </button>
        </div>
    </form>
</div>

<?php if ($comunidadId && !empty($pagos)): ?>
    <!-- Resumen -->
    <div class="alert alert-success mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-cash-coin me-2"></i>
                    Pagos de <?= e($comunidad['nombre']) ?>
                </h5>
                <p class="mb-0">
                    Período: <strong><?= getMonthName($mes) ?> <?= $anio ?></strong>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-success fs-5">
                    <?= formatMoney($totalRecaudado) ?> Recaudado
                </span><br>
                <small class="text-muted"><?= count($pagos) ?> pagos registrados</small>
            </div>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="table-container">
        <div class="table-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Recibo</th>
                        <th>Propiedad</th>
                        <th>Dueño</th>
                        <th>Meses Pagados</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= formatDate($pago['fecha']) ?></td>
                            <td>
                                <span class="badge bg-primary">REC-<?= str_pad($pago['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td><?= e($pago['propiedad_nombre']) ?></td>
                            <td><?= e($pago['nombre_dueno']) ?></td>
                            <td>
                                <small class="text-muted">
                                    <?= $pago['meses_pagados'] ? str_replace('-', '/', $pago['meses_pagados']) : 'N/A' ?>
                                </small>
                            </td>
                            <td class="text-end fw-bold"><?= formatMoney((float)$pago['monto']) ?></td>
                            <td class="text-center">
                                <a href="pagos.php?action=recibo&id=<?= $pago['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Ver Recibo">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-success">
                    <tr>
                        <td colspan="5" class="text-end fw-bold fs-5">TOTAL RECAUDADO:</td>
                        <td colspan="2" class="fw-bold fs-4"><?= formatMoney($totalRecaudado) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php elseif ($comunidadId): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-circle me-2"></i>
        No se encontraron pagos para el período seleccionado.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Seleccione una comunidad, mes y año para ver el reporte de pagos.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
