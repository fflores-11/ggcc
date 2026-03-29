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
                    <th class="text-center">Boleta</th>
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
                            <td class="text-center">
                                <?php if (!empty($pago['imagen_path'])): ?>
                                    <span class="badge bg-success" title="Boleta adjunta">
                                        <i class="bi bi-paperclip"></i> Sí
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" title="Sin boleta">
                                        <i class="bi bi-x-circle"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                <?= formatMoney((float)$pago['monto']) ?>
                            </td>
                            <td><?= e($pago['pagado_por_nombre']) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="colaboradores.php?action=editPago&id=<?= $pago['id'] ?>" 
                                       class="btn btn-outline-primary" title="Editar Pago">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="colaboradores.php?action=generarReciboPDF&id=<?= $pago['id'] ?>" 
                                       class="btn btn-outline-success" title="Descargar Recibo PDF"
                                       target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <?php if (!empty($pago['imagen_path'])): ?>
                                        <a href="colaboradores.php?action=verImagen&id=<?= $pago['id'] ?>" 
                                           class="btn btn-outline-info" title="Ver Boleta/Recibo">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="colaboradores.php?action=show&id=<?= $pago['colaborador_id'] ?>" 
                                       class="btn btn-outline-secondary" title="Ver Colaborador">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="colaboradores.php?action=deletePago&id=<?= $pago['id'] ?>" 
                                       class="btn btn-outline-danger" 
                                       title="Eliminar Pago"
                                       onclick="return confirm('¿Está seguro de eliminar este pago?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
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
