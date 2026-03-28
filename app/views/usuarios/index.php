<?php
/**
 * Vista: Listado de Usuarios
 */

$title = 'Mantenedor de Usuarios';
require_once __DIR__ . '/../partials/header.php';

// Paginación
$totalRecords = $totalRecords ?? count($usuarios);
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Listado de Usuarios</h4>
    <a href="usuarios.php?action=create" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
    </a>
</div>

<div class="table-container">
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Comunidad</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-people display-4 d-block mb-3"></i>
                            No hay usuarios registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= $usuario['id'] ?></td>
                            <td>
                                <strong><?= e($usuario['nombre']) ?></strong>
                                <?php if ($usuario['id'] == getUserId()): ?>
                                    <span class="badge bg-info ms-2">Yo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($usuario['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $usuario['rol'] === 'admin' ? 'danger' : 
                                    ($usuario['rol'] === 'administrador' ? 'primary' : 'secondary') 
                                ?>">
                                    <?= ucfirst($usuario['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['comunidad_nombre']): ?>
                                    <small class="text-muted"><?= e($usuario['comunidad_nombre']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'warning' ?>">
                                    <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <?= $usuario['ultimo_acceso'] ? formatDate($usuario['ultimo_acceso'], 'd/m/Y H:i') : 'Nunca' ?>
                            </td>
                            <td class="text-end">
                                <a href="usuarios.php?action=edit&id=<?= $usuario['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if ($usuario['activo']): ?>
                                    <?php if ($usuario['id'] != getUserId()): ?>
                                        <a href="usuarios.php?action=delete&id=<?= $usuario['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="usuarios.php?action=restore&id=<?= $usuario['id'] ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Reactivar"
                                       onclick="return confirm('¿Está seguro de reactivar este usuario?')">
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
