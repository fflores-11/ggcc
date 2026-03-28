<?php
/**
 * Vista: Listado de Comunidades
 */

$title = 'Mantenedor de Comunidades';
require_once __DIR__ . '/../partials/header.php';

// Paginación
$totalRecords = $totalRecords ?? count($comunidades);
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Listado de Comunidades</h4>
    <a href="comunidades.php?action=create" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-2"></i>Nueva Comunidad
    </a>
</div>

<div class="table-container">
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Comuna</th>
                    <th>Presidente</th>
                    <th class="text-center">Propiedades</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comunidades)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-building display-4 d-block mb-3"></i>
                            No hay comunidades registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comunidades as $comunidad): ?>
                        <tr>
                            <td><?= $comunidad['id'] ?></td>
                            <td>
                                <strong><?= e($comunidad['nombre']) ?></strong>
                            </td>
                            <td><?= e($comunidad['direccion']) ?></td>
                            <td><?= e($comunidad['comuna']) ?></td>
                            <td>
                                <?= e($comunidad['nombre_presidente']) ?><br>
                                <small class="text-muted"><?= e($comunidad['whatsapp_presidente']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $comunidad['total_propiedades'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $comunidad['activo'] ? 'success' : 'warning' ?>">
                                    <?= $comunidad['activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="comunidades.php?action=show&id=<?= $comunidad['id'] ?>" 
                                   class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="comunidades.php?action=edit&id=<?= $comunidad['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if ($comunidad['activo']): ?>
                                    <a href="comunidades.php?action=delete&id=<?= $comunidad['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Está seguro de eliminar esta comunidad? Esto también desactivará todas sus propiedades.')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="comunidades.php?action=restore&id=<?= $comunidad['id'] ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Reactivar"
                                       onclick="return confirm('¿Está seguro de reactivar esta comunidad?')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                <?php endif; ?>
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
