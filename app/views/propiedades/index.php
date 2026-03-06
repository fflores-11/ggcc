<?php
/**
 * Vista: Listado de Propiedades
 */

$title = isset($comunidad) ? 'Propiedades: ' . $comunidad['nombre'] : 'Mantenedor de Propiedades';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <div class="d-flex gap-2">
        <?php if (isset($comunidad)): ?>
            <a href="comunidades.php?action=show&id=<?= $comunidad['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a Comunidad
            </a>
        <?php endif; ?>
        <a href="propiedades.php?action=create<?= isset($comunidad) ? '&comunidad_id=' . $comunidad['id'] : '' ?>" 
           class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-2"></i>Nueva Propiedad
        </a>
    </div>
</div>

<!-- Filtro por Comunidad -->
<?php if (!isset($comunidad)): ?>
<div class="form-section mb-4">
    <form method="GET" action="propiedades.php" class="row align-items-end">
        <div class="col-md-4">
            <label class="form-label">Filtrar por Comunidad</label>
            <select name="comunidad_id" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las comunidades</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= (isset($_GET['comunidad_id']) && $_GET['comunidad_id'] == $com['id']) ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Gastos Comunes</th>
                    <?php if (!isset($comunidad)): ?>
                        <th>Comunidad</th>
                    <?php endif; ?>
                    <th>Dueño</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($propiedades)): ?>
                    <tr>
                        <td colspan="<?= isset($comunidad) ? 8 : 9 ?>" class="text-center text-muted py-5">
                            <i class="bi bi-house-door display-4 d-block mb-3"></i>
                            No hay propiedades registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($propiedades as $propiedad): ?>
                        <tr>
                            <td><?= $propiedad['id'] ?></td>
                            <td>
                                <strong><?= e($propiedad['nombre']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $propiedad['tipo'] ?>
                                </span>
                            </td>
                            <td><?= formatMoney((float)$propiedad['precio_gastos_comunes']) ?></td>
                            <?php if (!isset($comunidad)): ?>
                                <td><?= e($propiedad['comunidad_nombre']) ?></td>
                            <?php endif; ?>
                            <td><?= e($propiedad['nombre_dueno']) ?></td>
                            <td>
                                <small><?= e($propiedad['email_dueno']) ?><br>
                                <?= e($propiedad['whatsapp_dueno']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $propiedad['activo'] ? 'success' : 'warning' ?>">
                                    <?= $propiedad['activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="propiedades.php?action=show&id=<?= $propiedad['id'] ?>" 
                                   class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="propiedades.php?action=edit&id=<?= $propiedad['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if ($propiedad['activo']): ?>
                                    <a href="propiedades.php?action=delete&id=<?= $propiedad['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Está seguro de eliminar esta propiedad?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="propiedades.php?action=restore&id=<?= $propiedad['id'] ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Reactivar"
                                       onclick="return confirm('¿Está seguro de reactivar esta propiedad?')">
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
