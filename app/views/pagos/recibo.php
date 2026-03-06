<?php
/**
 * Vista: Recibo de Pago
 */

$title = 'Recibo de Pago #' . $pago['numero_recibo'];
require_once __DIR__ . '/../partials/header.php';

// Formatear meses pagados
$mesesPagados = [];
foreach ($pago['detalles'] as $detalle) {
    $mesesPagados[] = getMonthName((int)$detalle['mes']) . ' ' . $detalle['anio'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Recibo de Pago</h4>
    <div class="d-flex gap-2">
        <a href="pagos.php?action=enviar&id=<?= $pago['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-envelope me-2"></i>Enviar por Email
        </a>
        <button onclick="window.print()" class="btn btn-primary-custom">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Recibo -->
        <div class="card border-0 shadow-lg" id="recibo">
            <div class="card-header bg-primary text-white p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-0"><i class="bi bi-receipt me-2"></i>RECIBO DE PAGO</h3>
                        <p class="mb-0 mt-2 opacity-75"><?= e($pago['comunidad_nombre']) ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h4 class="mb-0"><?= $pago['numero_recibo'] ?></h4>
                        <p class="mb-0 mt-1">Fecha: <?= formatDate($pago['fecha']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Información de la Comunidad -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">COMUNIDAD</h6>
                        <p class="mb-1 fw-bold"><?= e($pago['comunidad_nombre']) ?></p>
                        <p class="mb-0 text-muted"><?= e($pago['comunidad_direccion']) ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-2">PRESIDENTE</h6>
                        <p class="mb-1"><?= e($pago['nombre_presidente']) ?></p>
                        <p class="mb-0 text-muted"><?= e($pago['email_presidente']) ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Información del Pagador -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">PROPIEDAD</h6>
                        <p class="mb-1 fw-bold fs-5"><?= e($pago['propiedad_nombre']) ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-2">PROPIETARIO</h6>
                        <p class="mb-1 fw-bold"><?= e($pago['nombre_dueno']) ?></p>
                        <p class="mb-0 text-muted"><?= e($pago['email_dueno']) ?></p>
                        <?php if ($pago['whatsapp_dueno']): ?>
                            <p class="mb-0 text-muted"><?= e($pago['whatsapp_dueno']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Detalle de Meses Pagados -->
                <h6 class="text-muted mb-3">DETALLE DE PAGO</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Período</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pago['detalles'] as $detalle): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-calendar-check text-success me-2"></i>
                                        <?= getMonthName((int)$detalle['mes']) ?> <?= $detalle['anio'] ?>
                                    </td>
                                    <td class="text-end"><?= formatMoney((float)$detalle['monto_pagado']) ?></td>
                                    <td><span class="badge bg-success">Pagado</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <td colspan="1" class="text-end fw-bold fs-5">TOTAL PAGADO:</td>
                                <td colspan="2" class="text-end fw-bold fs-4">
                                    <?= formatMoney((float)$pago['monto']) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($pago['observaciones']): ?>
                    <div class="alert alert-light mt-4">
                        <h6 class="text-muted mb-2">OBSERVACIONES:</h6>
                        <p class="mb-0"><?= nl2br(e($pago['observaciones'])) ?></p>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Firma -->
                <div class="row mt-5">
                    <div class="col-md-6 offset-md-6">
                        <div class="border-top pt-3 text-center">
                            <p class="mb-0 fw-bold">_______________________</p>
                            <p class="text-muted">Firma y Sello</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-center p-3">
                <p class="mb-0 text-muted small">
                    Este documento es un comprobante de pago válido. Conserve este recibo para futuras consultas.
                </p>
                <p class="mb-0 text-muted small mt-1">
                    Sistema <?= APP_NAME ?> v<?= APP_VERSION ?> - Generado el <?= date('d/m/Y H:i:s') ?>
                </p>
            </div>
        </div>

        <!-- Acciones -->
        <div class="d-flex justify-content-between mt-4 no-print">
            <a href="pagos.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver al Listado
            </a>
            <div class="d-flex gap-2">
                <a href="propiedades.php?action=show&id=<?= $pago['propiedad_id'] ?>" class="btn btn-outline-info">
                    <i class="bi bi-house-door me-2"></i>Ver Propiedad
                </a>
                <a href="pagos.php?action=create" class="btn btn-primary-custom">
                    <i class="bi bi-plus-lg me-2"></i>Nuevo Pago
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .no-print, .alert, .btn {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
    }
    #recibo {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    body {
        background: white !important;
    }
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
