<?php
/**
 * Vista: Editar Pago
 */

$title = 'Editar Pago #' . $pago['id'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Editar Pago #<?= $pago['id'] ?></h4>
    <a href="pagos.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="pagos.php?action=update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="pago_id" value="<?= $pago['id'] ?>">
                
                <h6 class="mb-3 text-primary">Información del Pago</h6>
                
                <!-- Información de Propiedad (Solo lectura) -->
                <div class="mb-4">
                    <label class="form-label">Propiedad</label>
                    <input type="text" class="form-control" value="<?= e($pago['propiedad_nombre']) ?>" readonly>
                    <small class="text-muted"><?= e($pago['nombre_dueno']) ?></small>
                </div>

                <!-- Fecha -->
                <div class="mb-4">
                    <label class="form-label">Fecha del Pago <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" class="form-control" 
                           value="<?= $pago['fecha'] ?>" required>
                </div>

                <!-- Meses Pagados (Mostrar cuáles están pagados) -->
                <div class="mb-4">
                    <h6 class="mb-3 text-primary">Meses Pagados Actualmente</h6>
                    <div class="alert alert-info">
                        <?php if (!empty($pago['detalles'])): ?>
                            <ul class="mb-0">
                                <?php foreach ($pago['detalles'] as $detalle): ?>
                                    <li>
                                        <?= getMonthName((int)$detalle['mes']) ?> <?= $detalle['anio'] ?>
                                        - <?= formatMoney((float)$detalle['monto_pagado']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            No hay detalles de meses pagados
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Para modificar los meses pagados, debe eliminar este pago y crear uno nuevo.
                    </small>
                </div>

                <!-- Observaciones -->
                <div class="mb-4">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?= e($pago['observaciones']) ?></textarea>
                </div>

                <!-- Monto (Solo lectura - calculado automáticamente) -->
                <div class="mb-4">
                    <label class="form-label">Monto Total</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control" 
                               value="<?= number_format($pago['monto'], 0, ',', '.') ?>" readonly>
                    </div>
                    <small class="text-muted">El monto se calcula automáticamente según los meses pagados</small>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                    </button>
                    <a href="pagos.php?action=recibo&id=<?= $pago['id'] ?>" class="btn btn-outline-info">
                        <i class="bi bi-receipt me-2"></i>Ver Recibo
                    </a>
                    <a href="pagos.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Información del Pago -->
        <div class="form-section">
            <h6 class="mb-3">Resumen del Pago</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Recibo #</strong></td>
                    <td>REC-<?= str_pad($pago['id'], 6, '0', STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha Original</strong></td>
                    <td><?= formatDate($pago['fecha']) ?></td>
                </tr>
                <tr>
                    <td><strong>Comunidad</strong></td>
                    <td><?= e($pago['comunidad_nombre']) ?></td>
                </tr>
                <tr>
                    <td><strong>Propiedad</strong></td>
                    <td><?= e($pago['propiedad_nombre']) ?></td>
                </tr>
                <tr>
                    <td><strong>Monto</strong></td>
                    <td class="fw-bold text-primary"><?= formatMoney((float)$pago['monto']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Acciones -->
        <div class="form-section">
            <h6 class="mb-3">Acciones</h6>
            <a href="pagos.php?action=recibo&id=<?= $pago['id'] ?>" class="btn btn-outline-primary w-100 mb-2">
                <i class="bi bi-receipt me-2"></i>Ver Recibo
            </a>
            <a href="pagos.php?action=pdf&id=<?= $pago['id'] ?>" class="btn btn-outline-secondary w-100 mb-2" target="_blank">
                <i class="bi bi-file-earmark-pdf me-2"></i>Descargar PDF
            </a>
            <a href="pagos.php?action=enviar&id=<?= $pago['id'] ?>" class="btn btn-outline-info w-100">
                <i class="bi bi-envelope me-2"></i>Enviar por Email
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>