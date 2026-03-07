<?php
/**
 * Vista: Listado de Colaboradores
 */

$title = 'Mantenedor de Colaboradores';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-people-fill me-2"></i>Listado de Colaboradores</h4>
    <div class="d-flex gap-2">
        <a href="colaboradores.php?action=pagos" class="btn btn-info">
            <i class="bi bi-cash-stack me-2"></i>Ver Pagos
        </a>
        <a href="colaboradores.php?action=create" class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Colaborador
        </a>
    </div>
</div>

<div class="table-container">
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Contacto/Cliente</th>
                    <th>Ubicación</th>
                    <th class="text-center">Pagos</th>
                    <th class="text-end">Total Pagado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($colaboradores)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-people display-4 d-block mb-3"></i>
                            No hay colaboradores registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($colaboradores as $colaborador): 
                        $isEmpresa = ($colaborador['tipo_colaborador'] ?? 'personal') === 'empresa';
                        $tipoBadge = $isEmpresa ? 'bg-warning text-dark' : 'bg-primary';
                        $tipoIcon = $isEmpresa ? 'building' : 'person';
                        $tipoLabel = $isEmpresa ? 'Empresa' : 'Personal';
                    ?>
                        <tr>
                            <td><?= $colaborador['id'] ?></td>
                            <td>
                                <span class="badge <?= $tipoBadge ?>">
                                    <i class="bi bi-<?= $tipoIcon ?> me-1"></i><?= $tipoLabel ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= e($colaborador['nombre']) ?></strong>
                            </td>
                            <td>
                                <?php if ($isEmpresa): ?>
                                    <small class="text-muted">N° Cliente: <?= e($colaborador['numero_cliente'] ?? 'N/A') ?></small>
                                <?php else: ?>
                                    <?= e($colaborador['email']) ?><br>
                                    <small class="text-muted"><?= e($colaborador['whatsapp']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isEmpresa): ?>
                                    <span class="text-muted">-</span>
                                <?php else: ?>
                                    <?= e($colaborador['comuna']) ?>, <?= e($colaborador['region']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info"><?= $colaborador['total_pagos'] ?></span>
                            </td>
                            <td class="text-end fw-bold">
                                <?= formatMoney((float)$colaborador['total_pagado']) ?>
                            </td>
                            <td class="text-center">
                                <a href="colaboradores.php?action=show&id=<?= $colaborador['id'] ?>" 
                                   class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="colaboradores.php?action=createPago&colaborador_id=<?= $colaborador['id'] ?>" 
                                   class="btn btn-sm btn-outline-success me-1" title="Registrar Pago">
                                    <i class="bi bi-cash-coin"></i>
                                </a>
                                <a href="colaboradores.php?action=edit&id=<?= $colaborador['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="colaboradores.php?action=delete&id=<?= $colaborador['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar este colaborador?')">
                                    <i class="bi bi-trash"></i>
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
