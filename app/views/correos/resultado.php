<?php
/**
 * Vista: Resultado de Envío
 */

$title = 'Resultado de Envío #' . $envio['id'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Resultado de Envío de Correos</h4>
    <a href="correos.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<!-- Resumen del Envío -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="icon"><i class="bi bi-envelope"></i></div>
            <div class="number"><?= $envio['total_enviados'] ?></div>
            <div class="label">Total Enviados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="icon"><i class="bi bi-check-circle"></i></div>
            <div class="number"><?= $envio['total_exitosos'] ?></div>
            <div class="label">Exitosos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="icon"><i class="bi bi-x-circle"></i></div>
            <div class="number"><?= $envio['total_fallidos'] ?></div>
            <div class="label">Fallidos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="icon"><i class="bi bi-calendar"></i></div>
            <div class="number"><?= formatDate($envio['created_at'], 'd/m H:i') ?></div>
            <div class="label">Fecha Envío</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detalles del Envío -->
    <div class="col-lg-8">
        <div class="form-section mb-4">
            <h6 class="mb-3">Información del Envío</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted" width="150">ID:</td>
                    <td><strong>#<?= $envio['id'] ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Tipo:</td>
                    <td>
                        <span class="badge bg-<?= $envio['tipo'] === 'cobranza' ? 'warning' : 'info' ?>">
                            <?= ucfirst($envio['tipo']) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Comunidad:</td>
                    <td><?= e($envio['comunidad_nombre']) ?></td>
                </tr>
                <?php if ($envio['mes'] && $envio['anio']): ?>
                <tr>
                    <td class="text-muted">Período:</td>
                    <td><?= getMonthName((int)$envio['mes']) ?> <?= $envio['anio'] ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Enviado por:</td>
                    <td><?= e($envio['enviado_por_nombre']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Asunto:</td>
                    <td><?= e($envio['asunto']) ?></td>
                </tr>
            </table>

            <hr>
            <h6 class="mb-3">Contenido del Correo</h6>
            <div class="border rounded p-3 bg-light">
                <?= $envio['body'] ?>
            </div>
        </div>

        <!-- Detalle de Destinatarios -->
        <div class="table-container">
            <div class="table-header">
                <h5 class="mb-0">Detalle de Destinatarios</h5>
            </div>
            <div class="table-body">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Propiedad</th>
                            <th>Dueño</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($envio['detalles'])): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay detalles disponibles
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($envio['detalles'] as $detalle): ?>
                                <tr>
                                    <td><?= e($detalle['propiedad_nombre']) ?></td>
                                    <td><?= e($detalle['nombre_dueno']) ?></td>
                                    <td><?= e($detalle['email_enviado']) ?></td>
                                    <td>
                                        <?php if ($detalle['estado'] === 'enviado'): ?>
                                            <span class="badge bg-success">Enviado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Error</span>
                                            <?php if ($detalle['error_msg']): ?>
                                                <small class="d-block text-danger"><?= e($detalle['error_msg']) ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($detalle['estado'] === 'error'): ?>
                                            <a href="correos.php?action=reenviar&detalle_id=<?= $detalle['id'] ?>&envio_id=<?= $envio['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Reenviar">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="form-section mb-4">
            <h6 class="mb-3"><i class="bi bi-lightning-charge me-2"></i>Acciones Rápidas</h6>
            <div class="d-grid gap-2">
                <?php if ($envio['tipo'] === 'general'): ?>
                    <a href="correos.php?action=general" class="btn btn-outline-primary">
                        <i class="bi bi-envelope-plus me-2"></i>Nuevo Envío General
                    </a>
                <?php else: ?>
                    <a href="correos.php?action=cobranza" class="btn btn-outline-warning">
                        <i class="bi bi-envelope-exclamation me-2"></i>Nueva Cobranza
                    </a>
                <?php endif; ?>
                <a href="comunidades.php?action=show&id=<?= $envio['comunidad_id'] ?>" class="btn btn-outline-info">
                    <i class="bi bi-building me-2"></i>Ver Comunidad
                </a>
            </div>
        </div>

        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted small">
                Este envío se realizó el <strong><?= formatDate($envio['created_at'], 'd/m/Y a las H:i') ?></strong>.
            </p>
            <p class="text-muted small">
                Los correos se envían a las direcciones registradas de cada propiedad. 
                Si algún correo falló, puede reenviarlo individualmente.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
