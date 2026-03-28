<?php 
require_once VIEWS_PATH . '/partials/header.php';

// Paginación
$totalRecords = $totalRecords ?? count($usuarios);
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-house-door me-2"></i>Usuarios por Propiedad
        </h1>
        <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nuevo Usuario
        </a>
    </div>

    <?php if ($success = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios Propietarios</h6>
            <span class="badge bg-primary"><?php echo count($usuarios); ?> usuarios</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead class="table-light">
                        <tr>
                            <th>Comunidad</th>
                            <th>Propiedad</th>
                            <th>Usuario</th>
                            <th>Propietario</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Último Acceso</th>
                            <th>Estado</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['comunidad_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($usuario['propiedad_nombre'] ?? 'N/A'); ?></strong>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($usuario['nombre']); ?></code>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['nombre_dueno'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <?php if (!empty($usuario['whatsapp'])): ?>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $usuario['whatsapp']); ?>" 
                                       target="_blank" class="text-decoration-none">
                                        <i class="bi bi-whatsapp text-success"></i>
                                        <?php echo htmlspecialchars($usuario['whatsapp']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usuario['ultimo_acceso']): ?>
                                    <small><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?></small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usuario['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button"
                                       class="btn btn-outline-primary btn-sm" 
                                       onclick="window.location.href='<?php echo BASE_URL; ?>usuarios_propiedad.php?action=edit&id=<?php echo $usuario['id']; ?>'"
                                       title="Editar usuario">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <?php if ($usuario['activo']): ?>
                                        <button type="button" 
                                           class="btn btn-outline-danger btn-sm" 
                                           onclick="if(confirm('¿Está seguro de desactivar este usuario?')) { window.location.href='<?php echo BASE_URL; ?>usuarios_propiedad.php?action=delete&id=<?php echo $usuario['id']; ?>'; }"
                                           title="Desactivar usuario">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button"
                                           class="btn btn-outline-success btn-sm"
                                           onclick="window.location.href='<?php echo BASE_URL; ?>usuarios_propiedad.php?action=restore&id=<?php echo $usuario['id']; ?>'"
                                           title="Reactivar usuario">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="button"
                                       class="btn btn-outline-warning btn-sm" 
                                       onclick="if(confirm('¿Generar nueva contraseña para este usuario?')) { window.location.href='<?php echo BASE_URL; ?>usuarios_propiedad.php?action=generarNuevaPassword&id=<?php echo $usuario['id']; ?>'; }"
                                       title="Generar nueva contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No hay usuarios propietarios registrados
                                </div>
                                <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=create" class="btn btn-primary btn-sm mt-2">
                                    Crear primer usuario
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($totalRecords > $perPage): ?>
<div class="mt-4">
    <?= renderPagination($totalRecords, $currentPage, $perPage) ?>
</div>
<?php endif; ?>

<?php 
require_once VIEWS_PATH . '/partials/footer.php';
?>
