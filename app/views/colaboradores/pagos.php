<?php
/**
 * Vista: Listado de Pagos a Colaboradores
 */

$title = 'Pagos a Colaboradores';
require_once __DIR__ . '/../partials/header.php';

// Paginación
$totalRecords = $totalRecords ?? count($pagos);
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-cash-stack me-2"></i>Pagos a Colaboradores</h4>
    <div class="d-flex gap-2">
        <div class="alert alert-info py-2 px-3 mb-0">
            <strong>Total Mes Actual:</strong> <?= formatMoney($totalMesActual) ?>
        </div>
        <a href="colaboradores.php?action=createPago" class="btn btn-success">
            <i class="bi bi-plus-lg me-2"></i>Registrar Pago
        </a>
        <a href="colaboradores.php" class="btn btn-outline-secondary">
            <i class="bi bi-people me-2"></i>Colaboradores
        </a>
    </div>
</div>

<div class="table-container">
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Colaborador</th>
                    <th>Detalle</th>
                    <th class="text-end">Monto</th>
                    <th>Registrado por</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-cash-stack display-4 d-block mb-3"></i>
                            No hay pagos registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= formatDate($pago['fecha']) ?></td>
                            <td>
                                <strong><?= e($pago['colaborador_nombre']) ?></strong><br>
                                <small class="text-muted"><?= e($pago['colaborador_email']) ?></small>
                            </td>
                            <td><?= e($pago['detalle']) ?></td>
                            <td class="text-end fw-bold text-primary">
                                <?= formatMoney((float)$pago['monto']) ?>
                            </td>
                            <td><?= e($pago['pagado_por_nombre']) ?></td>
                            <td class="text-center">
                                <a href="colaboradores.php?action=show&id=<?= $pago['colaborador_id'] ?>" 
                                   class="btn btn-sm btn-outline-info me-1" title="Ver Colaborador">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="colaboradores.php?action=deletePago&id=<?= $pago['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Eliminar Pago"
                                   onclick="return confirm('¿Está seguro de eliminar este pago?')">
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

<?php if ($totalRecords > $perPage): ?>
<div class="mt-4">
    <?= renderPagination($totalRecords, $currentPage, $perPage) ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
