<?php
/**
 * Vista: Configuración del Sistema
 */

$title = 'Configuración del Sistema';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-gear me-2"></i>Configuración del Sistema</h4>
</div>

<div class="row">
    <!-- Logo del Sistema (Dual: Claro y Oscuro) -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-images fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Logo del Sistema</h5>
                </div>
                
                <!-- Logo Modo Claro -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-bold"><i class="bi bi-sun me-1"></i>MODO CLARO</small>
                        <?php if ($logoExists): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Default</span>
                        <?php endif; ?>
                    </div>
                    <div class="bg-light p-2 rounded text-center" style="min-height: 60px;">
                        <?php if ($logoExists && $logoUrl): ?>
                            <img src="<?= $logoUrl ?>" alt="Logo Claro" style="max-height: 50px; max-width: 100%;">
                        <?php else: ?>
                            <div class="text-muted"><i class="bi bi-building fs-4"></i></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($logoExists && $logoUrl): ?>
                        <small class="text-muted d-block mt-1 text-center"><?= basename($logoUrl) ?></small>
                    <?php endif; ?>
                </div>
                
                <!-- Logo Modo Oscuro -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-bold"><i class="bi bi-moon me-1"></i>MODO OSCURO</small>
                        <?php if ($logoDarkExists): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">No configurado</span>
                        <?php endif; ?>
                    </div>
                    <div class="bg-dark p-2 rounded text-center" style="min-height: 60px;">
                        <?php if ($logoDarkExists && $logoDarkUrl): ?>
                            <img src="<?= $logoDarkUrl ?>" alt="Logo Oscuro" style="max-height: 50px; max-width: 100%;">
                        <?php elseif ($logoExists && $logoUrl): ?>
                            <img src="<?= $logoUrl ?>" alt="Logo" class="opacity-50" style="max-height: 50px; max-width: 100%;">
                        <?php else: ?>
                            <div class="text-secondary"><i class="bi bi-building fs-4"></i></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($logoDarkExists && $logoDarkUrl): ?>
                        <small class="text-muted d-block mt-1 text-center"><?= basename($logoDarkUrl) ?></small>
                    <?php endif; ?>
                </div>
                
                <a href="configuracion.php?action=logo" class="btn btn-outline-primary w-100">
                    <i class="bi bi-pencil me-2"></i>Gestionar Logos
                </a>
            </div>
        </div>
    </div>

    <!-- Configuraciones Generales -->
    <div class="col-md-8 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-info text-white rounded-circle p-3 me-3">
                        <i class="bi bi-sliders fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Configuraciones Generales</h5>
                </div>

                <form action="configuracion.php?action=actualizar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <?php foreach ($configuraciones as $config): ?>
                                    <?php if ($config['clave'] === 'logo_path') continue; // Saltar logo_path que se maneja aparte ?>
                                    
                                    <tr>
                                        <td width="30%" class="align-middle">
                                            <label class="form-label mb-0 fw-bold">
                                                <?= ucwords(str_replace('_', ' ', $config['clave'])) ?>
                                            </label>
                                            <?php if ($config['descripcion']): ?>
                                                <br><small class="text-muted"><?= e($config['descripcion']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td width="70%">
                                            <input type="text" 
                                                   name="<?= e($config['clave']) ?>" 
                                                   class="form-control" 
                                                   value="<?= e($config['valor']) ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Configuración -->
<div class="form-section mt-4">
    <h6 class="section-title"><i class="bi bi-list-check me-2"></i>Resumen de Configuración</h6>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Valor</th>
                    <th>Tipo</th>
                    <th>Última Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configuraciones as $config): ?>
                    <tr>
                        <td><code><?= e($config['clave']) ?></code></td>
                        <td>
                            <?php if ($config['clave'] === 'logo_path' && $logoExists): ?>
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i><?= e($config['valor']) ?></span>
                            <?php else: ?>
                                <?= e($config['valor']) ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= e($config['tipo']) ?></span></td>
                        <td>
                            <small class="text-muted">
                                <?= formatDate($config['updated_at']) ?>
                                <?php if ($config['updated_by_nombre']): ?>
                                    por <?= e($config['updated_by_nombre']) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
