<?php
/**
 * Vista: Formulario de Propiedad (Crear/Editar)
 */

$isEdit = isset($propiedad);
$title = $isEdit ? 'Editar Propiedad' : 'Nueva Propiedad';

require_once __DIR__ . '/../partials/header.php';

// Valores por defecto
$propiedad = $propiedad ?? [
    'id' => '',
    'comunidad_id' => $_GET['comunidad_id'] ?? '',
    'nombre' => '',
    'tipo' => 'Casa',
    'precio_gastos_comunes' => '',
    'nombre_dueno' => '',
    'email_dueno' => '',
    'whatsapp_dueno' => '',
    'nombre_agente' => '',
    'email_agente' => '',
    'whatsapp_agente' => '',
    'activo' => 1
];

$tipos = ['Casa', 'Departamento', 'Parcela'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <a href="propiedades.php<?= $propiedad['comunidad_id'] ? '?comunidad_id=' . $propiedad['comunidad_id'] : '' ?>" 
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="propiedades.php?action=<?= $isEdit ? 'update' : 'store' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $propiedad['id'] ?>">
                <?php endif; ?>

                <h6 class="mb-3 text-primary">Información de la Propiedad</h6>
                
                <div class="mb-3">
                    <label for="comunidad_id" class="form-label">Comunidad <span class="text-danger">*</span></label>
                    <select class="form-select" id="comunidad_id" name="comunidad_id" required>
                        <option value="">Seleccione una comunidad...</option>
                        <?php foreach ($comunidades as $comunidad): ?>
                            <option value="<?= $comunidad['id'] ?>" 
                                <?= $propiedad['comunidad_id'] == $comunidad['id'] ? 'selected' : '' ?>>
                                <?= e($comunidad['nombre']) ?> (<?= e($comunidad['comuna']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="nombre" class="form-label">Nombre de la Propiedad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                               value="<?= e($propiedad['nombre']) ?>" 
                               placeholder="Ej: Casa A-101, Depto 301, Parcela 5">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= $tipo ?>" <?= $propiedad['tipo'] === $tipo ? 'selected' : '' ?>>
                                    <?= $tipo ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="precio_gastos_comunes" class="form-label">
                        Precio Gastos Comunes (CLP) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="precio_gastos_comunes" 
                               name="precio_gastos_comunes" required min="0" step="1000"
                               value="<?= $propiedad['precio_gastos_comunes'] ?>" 
                               placeholder="Ej: 85000">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Información del Dueño</h6>

                <div class="mb-3">
                    <label for="nombre_dueno" class="form-label">Nombre del Dueño <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_dueno" name="nombre_dueno" required
                           value="<?= e($propiedad['nombre_dueno']) ?>" 
                           placeholder="Nombre completo del propietario">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email_dueno" class="form-label">Email del Dueño <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email_dueno" name="email_dueno" required
                               value="<?= e($propiedad['email_dueno']) ?>" 
                               placeholder="correo@ejemplo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_dueno" class="form-label">WhatsApp del Dueño</label>
                        <input type="text" class="form-control" id="whatsapp_dueno" name="whatsapp_dueno"
                               value="<?= e($propiedad['whatsapp_dueno']) ?>" 
                               placeholder="Ej: +56912345678">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Información del Agente (Opcional)</h6>

                <div class="mb-3">
                    <label for="nombre_agente" class="form-label">Nombre del Agente</label>
                    <input type="text" class="form-control" id="nombre_agente" name="nombre_agente"
                           value="<?= e($propiedad['nombre_agente']) ?>" 
                           placeholder="Nombre del administrador/agente">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email_agente" class="form-label">Email del Agente</label>
                        <input type="email" class="form-control" id="email_agente" name="email_agente"
                               value="<?= e($propiedad['email_agente']) ?>" 
                               placeholder="correo@ejemplo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_agente" class="form-label">WhatsApp del Agente</label>
                        <input type="text" class="form-control" id="whatsapp_agente" name="whatsapp_agente"
                               value="<?= e($propiedad['whatsapp_agente']) ?>" 
                               placeholder="Ej: +56912345678">
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <hr class="my-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                   value="1" <?= $propiedad['activo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Propiedad Activa</label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <a href="propiedades.php<?= $propiedad['comunidad_id'] ? '?comunidad_id=' . $propiedad['comunidad_id'] : '' ?>" 
                       class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Actualizar Propiedad' : 'Crear Propiedad' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted mb-3">
                Las propiedades representan las unidades individuales dentro de una comunidad (casas, departamentos o parcelas).
            </p>
            <h6 class="mb-2">Tipos de propiedad:</h6>
            <ul class="list-unstyled text-muted mb-3">
                <li class="mb-1"><i class="bi bi-house-door text-primary me-2"></i><strong>Casa:</strong> Vivienda unifamiliar</li>
                <li class="mb-1"><i class="bi bi-building text-primary me-2"></i><strong>Departamento:</strong> Unidad en edificio</li>
                <li class="mb-1"><i class="bi bi-tree text-primary me-2"></i><strong>Parcela:</strong> Terreno o lote</li>
            </ul>
            
            <hr>
            <h6 class="mb-2">Campos obligatorios:</h6>
            <ul class="list-unstyled text-muted">
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Comunidad</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Nombre</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Tipo</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Precio de gastos comunes</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Datos del dueño</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
