<?php
/**
 * Vista: Formulario de Comunidad (Crear/Editar)
 */

$isEdit = isset($comunidad);
$title = $isEdit ? 'Editar Comunidad' : 'Nueva Comunidad';

require_once __DIR__ . '/../partials/header.php';

// Valores por defecto
$comunidad = $comunidad ?? [
    'id' => '',
    'nombre' => '',
    'direccion' => '',
    'pais' => 'Chile',
    'region' => '',
    'comuna' => '',
    'nombre_presidente' => '',
    'whatsapp_presidente' => '',
    'email_presidente' => '',
    'activo' => 1
];

// Lista de regiones de Chile
$regiones = [
    'Arica y Parinacota',
    'Tarapacá',
    'Antofagasta',
    'Atacama',
    'Coquimbo',
    'Valparaíso',
    'Metropolitana',
    "O'Higgins",
    'Maule',
    'Ñuble',
    'Biobío',
    'La Araucanía',
    'Los Ríos',
    'Los Lagos',
    'Aysén',
    'Magallanes'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <a href="comunidades.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="comunidades.php?action=<?= $isEdit ? 'update' : 'store' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $comunidad['id'] ?>">
                <?php endif; ?>

                <h6 class="mb-3 text-primary">Información de la Comunidad</h6>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Comunidad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required
                           value="<?= e($comunidad['nombre']) ?>" 
                           placeholder="Ej: Condominio Los Robles">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2" required
                              placeholder="Dirección completa"><?= e($comunidad['direccion']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="pais" class="form-label">País</label>
                        <input type="text" class="form-control" id="pais" name="pais"
                               value="<?= e($comunidad['pais']) ?>" placeholder="Chile">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="region" class="form-label">Región <span class="text-danger">*</span></label>
                        <select class="form-select" id="region" name="region" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($regiones as $region): ?>
                                <option value="<?= $region ?>" <?= $comunidad['region'] === $region ? 'selected' : '' ?>>
                                    <?= $region ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="comuna" class="form-label">Comuna <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="comuna" name="comuna" required
                               value="<?= e($comunidad['comuna']) ?>" placeholder="Ej: Las Condes">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Información del Presidente</h6>

                <div class="mb-3">
                    <label for="nombre_presidente" class="form-label">Nombre del Presidente <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_presidente" name="nombre_presidente" required
                           value="<?= e($comunidad['nombre_presidente']) ?>" 
                           placeholder="Nombre completo">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_presidente" class="form-label">WhatsApp del Presidente</label>
                        <input type="text" class="form-control" id="whatsapp_presidente" name="whatsapp_presidente"
                               value="<?= e($comunidad['whatsapp_presidente']) ?>" 
                               placeholder="Ej: +56912345678">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email_presidente" class="form-label">Email del Presidente</label>
                        <input type="email" class="form-control" id="email_presidente" name="email_presidente"
                               value="<?= e($comunidad['email_presidente']) ?>" 
                               placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <hr class="my-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                   value="1" <?= $comunidad['activo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Comunidad Activa</label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <a href="comunidades.php" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Actualizar Comunidad' : 'Crear Comunidad' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted mb-3">
                Una comunidad representa un condominio o edificio que será administrado en el sistema.
            </p>
            <h6 class="mb-2">Campos obligatorios:</h6>
            <ul class="list-unstyled text-muted mb-3">
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Nombre de la comunidad</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Dirección</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Región</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Comuna</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Nombre del presidente</li>
            </ul>
            
            <?php if ($isEdit && $comunidad['total_propiedades'] > 0): ?>
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading"><i class="bi bi-house-door me-2"></i>Propiedades</h6>
                    <p class="mb-0">Esta comunidad tiene <?= $comunidad['total_propiedades'] ?> propiedad(es) registrada(s).</p>
                    <hr>
                    <a href="propiedades.php?comunidad_id=<?= $comunidad['id'] ?>" class="alert-link">
                        Ver propiedades →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
