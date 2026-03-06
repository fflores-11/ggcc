<?php
/**
 * Vista: Reporte de Morosidad
 */

$title = 'Reporte de Morosidad';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Reporte de Morosidad</h4>
    <a href="reportes.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver a Reportes
    </a>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="reportes.php" class="row align-items-end">
        <input type="hidden" name="action" value="morosidad">
        
        <div class="col-md-4">
            <label class="form-label">Comunidad <span class="text-danger">*</span></label>
            <select name="comunidad_id" class="form-select" required onchange="this.form.submit()">
                <option value="">Seleccione...</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= ($comunidadId ?? 0) == $com['id'] ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Mínimo de meses adeudados</label>
            <select name="minimo_meses" class="form-select" onchange="this.form.submit()">
                <option value="1" <?= ($minimoMeses ?? 1) == 1 ? 'selected' : '' ?>>1 mes o más</option>
                <option value="2" <?= ($minimoMeses ?? 1) == 2 ? 'selected' : '' ?>>2 meses o más</option>
                <option value="3" <?= ($minimoMeses ?? 1) == 3 ? 'selected' : '' ?>>3 meses o más</option>
                <option value="6" <?= ($minimoMeses ?? 1) == 6 ? 'selected' : '' ?>>6 meses o más</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-danger w-100">
                <i class="bi bi-search me-2"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<?php if ($comunidadId && !empty($morosos)): ?>
    <!-- Resumen -->
    <div class="alert alert-danger mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Propiedades Morosas: <?= e($comunidad['nombre']) ?>
                </h5>
                <p class="mb-0">
                    Se encontraron <strong><?= count($morosos) ?></strong> propiedades con <?= $minimoMeses ?> o más meses de deuda
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-danger fs-5">
                    <?= formatMoney(array_sum(array_column($morosos, 'total_adeudado'))) ?> Total Adeudado
                </span>
            </div>
        </div>
    </div>

    <!-- Tabla de Morosos -->
    <div class="table-container">
        <div class="table-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de Propiedades Morosas</h5>
            <a href="correos.php?action=cobranza&comunidad_id=<?= $comunidadId ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-envelope me-2"></i>Enviar Cobranzas
            </a>
        </div>
        <div class="table-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Dueño</th>
                        <th>Contacto</th>
                        <th class="text-center">Meses Adeudados</th>
                        <th>Períodos</th>
                        <th class="text-end">Total Adeudado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($morosos as $moroso): ?>
                        <tr class="table-danger">
                            <td>
                                <strong><?= e($moroso['propiedad_nombre']) ?></strong>
                            </td>
                            <td><?= e($moroso['nombre_dueno']) ?></td>
                            <td>
                                <small><?= e($moroso['email_dueno']) ?></small><br>
                                <small class="text-muted"><?= e($moroso['whatsapp_dueno']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6"><?= $moroso['meses_adeudados'] ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= str_replace(',', '<br>', e($moroso['periodos_adeudados'])) ?>
                                </small>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                <?= formatMoney((float)$moroso['total_adeudado']) ?>
                            </td>
                            <td class="text-center">
                                <a href="propiedades.php?action=show&id=<?= $moroso['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Ver Propiedad">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($comunidadId): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <strong>¡Excelente!</strong> No se encontraron propiedades morosas en esta comunidad 
        (con <?= $minimoMeses ?> o más meses de deuda).
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Seleccione una comunidad para ver el reporte de morosidad.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
