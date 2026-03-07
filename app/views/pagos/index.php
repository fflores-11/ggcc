<?php
/**
 * Vista: Listado de Pagos
 */

$title = 'Listado de Pagos';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Pagos Registrados</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#generarDeudasModal">
            <i class="bi bi-plus-circle me-2"></i>Generar Deudas Mensuales
        </button>
        <a href="pagos.php?action=create" class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-2"></i>Registrar Pago
        </a>
    </div>
</div>

<!-- Filtro por Comunidad -->
<div class="form-section mb-4">
    <form method="GET" action="pagos.php" class="row align-items-end">
        <div class="col-md-4">
            <label class="form-label">Filtrar por Comunidad</label>
            <select name="comunidad_id" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las comunidades</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= (isset($_GET['comunidad_id']) && $_GET['comunidad_id'] == $com['id']) ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Historial de Pagos</h5>
    </div>
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Recibo #</th>
                    <th>Fecha</th>
                    <th>Comunidad</th>
                    <th>Propiedad</th>
                    <th>Dueño</th>
                    <th>Meses Pagados</th>
                    <th class="text-end">Monto</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-cash-coin display-4 d-block mb-3"></i>
                            No hay pagos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary">REC-<?= str_pad($pago['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td><?= formatDate($pago['fecha']) ?></td>
                            <td><?= e($pago['comunidad_nombre'] ?? 'N/A') ?></td>
                            <td><?= e($pago['propiedad_nombre'] ?? 'N/A') ?></td>
                            <td><?= e($pago['nombre_dueno'] ?? 'N/A') ?></td>
                            <td>
                                <small class="text-muted">
                                    <?= $pago['meses_pagados'] ? str_replace('-', '/', $pago['meses_pagados']) : 'N/A' ?>
                                </small>
                            </td>
                            <td class="text-end fw-bold"><?= formatMoney((float)$pago['monto']) ?></td>
                            <td class="text-center">
                                <a href="pagos.php?action=recibo&id=<?= $pago['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Ver Recibo">
                                    <i class="bi bi-receipt"></i> Recibo
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Generar Deudas -->
<div class="modal fade" id="generarDeudasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="pagos.php?action=generar-deudas" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Generar Deudas Mensuales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Comunidad <span class="text-danger">*</span></label>
                        <select name="comunidad_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($comunidades as $com): ?>
                                <option value="<?= $com['id'] ?>"><?= e($com['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mes <span class="text-danger">*</span></label>
                            <select name="mes" class="form-select" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                        <?= getMonthName($i) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año <span class="text-danger">*</span></label>
                            <select name="anio" class="form-select" required>
                                <?php foreach (getYearList(1, 1) as $year): ?>
                                    <option value="<?= $year ?>" <?= $year == date('Y') ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="aplicar_saldos" name="aplicar_saldos" value="1">
                        <label class="form-check-label" for="aplicar_saldos">
                            <i class="bi bi-piggy-bank me-1"></i>
                            <strong>Aplicar saldos disponibles automáticamente</strong>
                        </label>
                        <div class="form-text">
                            Si una propiedad tiene saldo disponible, se usará para pagar las nuevas deudas automáticamente.
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Se generarán deudas automáticas para todas las propiedades activas de la comunidad seleccionada.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Generar Deudas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
