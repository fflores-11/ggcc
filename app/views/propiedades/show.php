<?php
/**
 * Vista: Detalle de Propiedad
 */

$title = 'Detalle de Propiedad: ' . $propiedad['nombre'];
require_once __DIR__ . '/../partials/header.php';

// Calcular deuda total
$totalDeuda = array_sum(array_column($propiedad['deudas'], 'monto'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Detalle de Propiedad</h4>
    <div class="d-flex gap-2">
        <a href="propiedades.php?action=edit&id=<?= $propiedad['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="propiedades.php<?= $propiedad['comunidad_id'] ? '?comunidad_id=' . $propiedad['comunidad_id'] : '' ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información de la Propiedad -->
    <div class="col-lg-4 mb-4">
        <div class="form-section">
            <h6 class="section-title">Información General</h6>
            
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted" width="40%">Nombre:</td>
                    <td class="fw-bold"><?= e($propiedad['nombre']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tipo:</td>
                    <td><span class="badge bg-secondary"><?= $propiedad['tipo'] ?></span></td>
                </tr>
                <tr>
                    <td class="text-muted">Comunidad:</td>
                    <td><?= e($propiedad['comunidad_nombre']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Gastos Comunes:</td>
                    <td class="fw-bold"><?= formatMoney((float)$propiedad['precio_gastos_comunes']) ?>/mes</td>
                </tr>
            </table>

            <hr>
            <h6 class="mb-3">Dueño</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted" width="40%">Nombre:</td>
                    <td><?= e($propiedad['nombre_dueno']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Email:</td>
                    <td><?= e($propiedad['email_dueno']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">WhatsApp:</td>
                    <td><?= e($propiedad['whatsapp_dueno']) ?></td>
                </tr>
            </table>

            <?php if ($propiedad['nombre_agente']): ?>
            <hr>
            <h6 class="mb-3">Agente</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted" width="40%">Nombre:</td>
                    <td><?= e($propiedad['nombre_agente']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Email:</td>
                    <td><?= e($propiedad['email_agente']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">WhatsApp:</td>
                    <td><?= e($propiedad['whatsapp_agente']) ?></td>
                </tr>
            </table>
            <?php endif; ?>
        </div>

        <!-- Resumen Financiero -->
        <div class="form-section mt-4">
            <h6 class="section-title">Resumen Financiero</h6>
            
            <div class="text-center mb-3">
                <div class="display-6 fw-bold text-danger"><?= formatMoney($totalDeuda) ?></div>
                <small class="text-muted">Deuda Total Pendiente</small>
            </div>

            <div class="d-grid gap-2">
                <a href="pagos.php?action=create&propiedad_id=<?= $propiedad['id'] ?>" class="btn btn-success">
                    <i class="bi bi-cash-coin me-2"></i>Registrar Pago
                </a>
                <a href="correos.php?action=cobranza&comunidad_id=<?= $propiedad['comunidad_id'] ?>" class="btn btn-warning">
                    <i class="bi bi-envelope me-2"></i>Enviar Cobranza
                </a>
            </div>
        </div>
    </div>

    <!-- Deudas Pendientes -->
    <div class="col-lg-8 mb-4">
        <div class="table-container">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Deudas Pendientes</h5>
                <span class="badge bg-danger"><?= count($propiedad['deudas']) ?> meses</span>
            </div>
            <div class="table-body">
                <?php if (empty($propiedad['deudas'])): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                        <p class="mb-0">No hay deudas pendientes</p>
                        <p class="text-success">¡Esta propiedad está al día!</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($propiedad['deudas'] as $deuda): ?>
                                <tr class="table-warning">
                                    <td>
                                        <i class="bi bi-calendar text-warning me-2"></i>
                                        <strong><?= getMonthName((int)$deuda['mes']) ?> <?= $deuda['anio'] ?></strong>
                                    </td>
                                    <td class="fw-bold"><?= formatMoney((float)$deuda['monto']) ?></td>
                                    <td>
                                        <span class="badge bg-warning">Pendiente</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="pagos.php?action=create&propiedad_id=<?= $propiedad['id'] ?>" 
                                           class="btn btn-sm btn-outline-success" title="Pagar">
                                            <i class="bi bi-cash-coin"></i> Pagar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-danger">
                            <tr>
                                <td class="fw-bold">TOTAL ADEUDADO:</td>
                                <td colspan="3" class="fw-bold fs-5"><?= formatMoney($totalDeuda) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de Pagos -->
        <div class="table-container mt-4">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Pagos</h5>
                <span class="badge bg-success"><?= count($pagos) ?> pagos</span>
            </div>
            <div class="table-body">
                <?php if (empty($pagos)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clock-history display-6 mb-3"></i>
                        <p class="mb-0">No hay pagos registrados aún</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Meses Pagados</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= formatDate($pago['fecha']) ?></td>
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
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
