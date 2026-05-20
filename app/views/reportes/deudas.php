<?php
/**
 * Vista: Reporte de Deudas
 */

$title = 'Reporte de Deudas por Período';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Reporte de Deudas</h4>
    <a href="reportes.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver a Reportes
    </a>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="reportes.php" class="row align-items-end">
        <input type="hidden" name="action" value="deudas">
        
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
            <button type="submit" class="btn btn-outline-warning w-100">
                <i class="bi bi-search me-2"></i>Generar
            </button>
        </div>
    </form>
</div>

<?php if ($comunidadId && !empty($deudas)): ?>
    <!-- Resumen -->
    <?php
    $pagadas = array_filter($deudas, fn($d) => $d['estado'] === 'Pagado');
    $pendientes = array_filter($deudas, fn($d) => $d['estado'] === 'Pendiente');
    $totalPagado = array_sum(array_column($pagadas, 'monto'));
    $totalPendiente = array_sum(array_column($pendientes, 'monto'));
    $porcentaje = count($deudas) > 0 ? (count($pagadas) / count($deudas)) * 100 : 0;
    ?>
    
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-receipt me-2"></i>
                    Deudas de <?= e($comunidad['nombre']) ?>
                </h5>
                <p class="mb-0">
                    Período: <strong><?= getMonthName($mes) ?> <?= $anio ?></strong>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-<?= $porcentaje >= 80 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger') ?> fs-5 mb-1">
                    <?= number_format($porcentaje, 1) ?>% Cobranza
                </span><br>
                <small class="text-muted">
                    <?= count($pagadas) ?> pagadas / <?= count($pendientes) ?> pendientes
                </small>
            </div>
        </div>
    </div>

    <!-- Tabla de Deudas -->
    <div class="table-container">
        <div class="table-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Dueño</th>
                        <th>Contacto</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deudas as $deuda): ?>
                        <tr class="<?= $deuda['estado'] === 'Pendiente' ? 'table-warning' : 'table-success' ?>">
                            <td><strong><?= e($deuda['propiedad_nombre']) ?></strong></td>
                            <td><?= e($deuda['nombre_dueno']) ?></td>
                            <td>
                                <small><?= e($deuda['email_dueno']) ?></small><br>
                                <small class="text-muted"><?= e($deuda['whatsapp_dueno']) ?></small>
                            </td>
                            <td class="text-end"><?= formatMoney((float)$deuda['monto']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $deuda['estado'] === 'Pagado' ? 'success' : 'warning' ?>">
                                    <?= $deuda['estado'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($deuda['estado'] === 'Pendiente'): ?>
                                    <a href="pagos.php?action=create&propiedad_id=<?= $deuda['propiedad_id'] ?>" 
                                       class="btn btn-sm btn-outline-success" title="Registrar Pago">
                                        <i class="bi bi-cash-coin"></i> Pagar
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-primary">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">TOTALES:</td>
                        <td class="text-end fw-bold"><?= formatMoney($totalPagado + $totalPendiente) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold text-success">Total Pagado:</td>
                        <td class="text-end fw-bold text-success"><?= formatMoney($totalPagado) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold text-danger">Total Pendiente:</td>
                        <td class="text-end fw-bold text-danger"><?= formatMoney($totalPendiente) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php elseif ($comunidadId): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-circle me-2"></i>
        No se encontraron deudas generadas para el período seleccionado. 
        <a href="pagos.php">Genere deudas mensuales aquí</a>.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Seleccione una comunidad, mes y año para ver el reporte de deudas.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
