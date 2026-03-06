<?php
/**
 * Vista: Listado de Envíos de Correo
 */

$title = 'Envío de Correos';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Historial de Envíos de Correo</h4>
    <div class="d-flex gap-2">
        <a href="correos.php?action=cobranza" class="btn btn-warning">
            <i class="bi bi-envelope-exclamation me-2"></i>Enviar Cobranza
        </a>
        <a href="correos.php?action=general" class="btn btn-primary-custom">
            <i class="bi bi-envelope-plus me-2"></i>Envío General
        </a>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Envíos Realizados</h5>
    </div>
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Comunidad</th>
                    <th>Asunto</th>
                    <th class="text-center">Destinatarios</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($envios)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-envelope display-4 d-block mb-3"></i>
                            No hay envíos de correo registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($envios as $envio): ?>
                        <tr>
                            <td><?= $envio['id'] ?></td>
                            <td><?= formatDate($envio['created_at'], 'd/m/Y H:i') ?></td>
                            <td>
                                <span class="badge bg-<?= $envio['tipo'] === 'cobranza' ? 'warning' : 'info' ?>">
                                    <?= ucfirst($envio['tipo']) ?>
                                </span>
                            </td>
                            <td><?= e($envio['comunidad_nombre'] ?? 'N/A') ?></td>
                            <td><?= e(substr($envio['asunto'], 0, 50)) ?><?= strlen($envio['asunto']) > 50 ? '...' : '' ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $envio['total_enviados'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($envio['total_fallidos'] > 0): ?>
                                    <span class="badge bg-danger">
                                        <?= $envio['total_exitosos'] ?> éxito / <?= $envio['total_fallidos'] ?> error
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success">Completado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="correos.php?action=resultado&id=<?= $envio['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
