<?php
/**
 * Vista: Formulario de Colaborador (Crear/Editar)
 */

$isEdit = isset($colaborador);
$title = $isEdit ? 'Editar Colaborador' : 'Nuevo Colaborador';

require_once __DIR__ . '/../partials/header.php';

// Valores por defecto
$colaborador = $colaborador ?? [
    'id' => '',
    'tipo_colaborador' => 'personal',
    'nombre' => '',
    'email' => '',
    'whatsapp' => '',
    'direccion' => '',
    'region' => '',
    'comuna' => '',
    'banco' => '',
    'tipo_cuenta' => 'vista',
    'numero_cuenta' => '',
    'numero_cliente' => '',
    'activo' => 1
];

$regiones = [
    'Arica y Parinacota', 'Tarapacá', 'Antofagasta', 'Atacama', 'Coquimbo',
    'Valparaíso', 'Metropolitana', "O'Higgins", 'Maule', 'Ñuble', 'Biobío',
    'La Araucanía', 'Los Ríos', 'Los Lagos', 'Aysén', 'Magallanes'
];

$tipoActual = $colaborador['tipo_colaborador'] ?? 'personal';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <a href="colaboradores.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="colaboradores.php?action=<?= $isEdit ? 'update' : 'store' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $colaborador['id'] ?>">
                <?php endif; ?>

                <h6 class="mb-3 text-primary">Tipo de Colaborador</h6>
                
                <div class="mb-4">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="tipo_colaborador" id="tipo_personal" value="personal" 
                               <?= $tipoActual === 'personal' ? 'checked' : '' ?> onchange="toggleTipoColaborador()">
                        <label class="btn btn-outline-primary" for="tipo_personal">
                            <i class="bi bi-person me-2"></i>Personal
                        </label>
                        
                        <input type="radio" class="btn-check" name="tipo_colaborador" id="tipo_empresa" value="empresa" 
                               <?= $tipoActual === 'empresa' ? 'checked' : '' ?> onchange="toggleTipoColaborador()">
                        <label class="btn btn-outline-primary" for="tipo_empresa">
                            <i class="bi bi-building me-2"></i>Empresa
                        </label>
                    </div>
                </div>

                <!-- Campos para EMPRESA -->
                <div id="campos_empresa" style="display: <?= $tipoActual === 'empresa' ? 'block' : 'none' ?>;">
                    <h6 class="mb-3 text-primary">Información de la Empresa</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre" required
                               value="<?= e($colaborador['nombre']) ?>" 
                               placeholder="Nombre de la empresa">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de Cliente <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="numero_cliente" required
                               value="<?= e($colaborador['numero_cliente']) ?>" 
                               placeholder="Ej: CLI-001234">
                    </div>
                </div>

                <!-- Campos para PERSONAL -->
                <div id="campos_personal" style="display: <?= $tipoActual === 'personal' ? 'block' : 'none' ?>;">
                    <h6 class="mb-3 text-primary">Información Personal</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre_personal"
                               value="<?= $tipoActual === 'personal' ? e($colaborador['nombre']) : '' ?>" 
                               placeholder="Nombre del colaborador">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= e($colaborador['email']) ?>" 
                                   placeholder="correo@ejemplo.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp"
                                   value="<?= e($colaborador['whatsapp']) ?>" 
                                   placeholder="Ej: +56912345678">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary">Dirección</h6>

                    <div class="mb-3">
                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="direccion" rows="2"
                                  placeholder="Dirección completa"><?= e($colaborador['direccion']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Región <span class="text-danger">*</span></label>
                            <select class="form-select" name="region">
                                <option value="">Seleccione...</option>
                                <?php foreach ($regiones as $region): ?>
                                    <option value="<?= $region ?>" <?= $colaborador['region'] === $region ? 'selected' : '' ?>>
                                        <?= $region ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Comuna <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="comuna"
                                   value="<?= e($colaborador['comuna']) ?>" 
                                   placeholder="Ej: Las Condes">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary">Información Bancaria</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Banco</label>
                            <input type="text" class="form-control" name="banco"
                                   value="<?= e($colaborador['banco']) ?>" 
                                   placeholder="Ej: Banco de Chile">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Cuenta</label>
                            <select class="form-select" name="tipo_cuenta">
                                <option value="vista" <?= $colaborador['tipo_cuenta'] === 'vista' ? 'selected' : '' ?>>Cuenta Vista (RUT)</option>
                                <option value="corriente" <?= $colaborador['tipo_cuenta'] === 'corriente' ? 'selected' : '' ?>>Cuenta Corriente</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de Cuenta</label>
                        <input type="text" class="form-control" name="numero_cuenta"
                               value="<?= e($colaborador['numero_cuenta']) ?>" 
                               placeholder="Ej: 1234567890">
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <hr class="my-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                   value="1" <?= $colaborador['activo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Colaborador Activo</label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <a href="colaboradores.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Actualizar Colaborador' : 'Crear Colaborador' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted mb-3">
                Los colaboradores son personas o empresas que prestan servicios a las comunidades y reciben pagos del sistema.
            </p>
            
            <div id="info_personal">
                <h6 class="mb-2">Personal - Campos obligatorios:</h6>
                <ul class="list-unstyled text-muted">
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Nombre completo</li>
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Email</li>
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Dirección</li>
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Región</li>
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Comuna</li>
                </ul>
                <hr class="my-3">
                <h6 class="mb-2">Campos opcionales:</h6>
                <ul class="list-unstyled text-muted">
                    <li class="mb-1"><i class="bi bi-circle text-info me-2"></i>WhatsApp</li>
                    <li class="mb-1"><i class="bi bi-circle text-info me-2"></i>Datos bancarios</li>
                </ul>
            </div>
            
            <div id="info_empresa" style="display: none;">
                <h6 class="mb-2">Empresa - Campos obligatorios:</h6>
                <ul class="list-unstyled text-muted">
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Nombre de la empresa</li>
                    <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Número de cliente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTipoColaborador() {
    const tipoPersonal = document.getElementById('tipo_personal').checked;
    const camposPersonal = document.getElementById('campos_personal');
    const camposEmpresa = document.getElementById('campos_empresa');
    const infoPersonal = document.getElementById('info_personal');
    const infoEmpresa = document.getElementById('info_empresa');
    
    // Campos del formulario
    camposPersonal.style.display = tipoPersonal ? 'block' : 'none';
    camposEmpresa.style.display = tipoPersonal ? 'none' : 'block';
    
    // Info sidebar
    infoPersonal.style.display = tipoPersonal ? 'block' : 'none';
    infoEmpresa.style.display = tipoPersonal ? 'none' : 'block';
    
    // Actualizar required attributes
    const personalInputs = camposPersonal.querySelectorAll('input, textarea, select');
    const empresaInputs = camposEmpresa.querySelectorAll('input');
    
    personalInputs.forEach(input => {
        if (tipoPersonal) {
            if (input.name === 'nombre_personal' || input.name === 'email' || 
                input.name === 'direccion' || input.name === 'region' || input.name === 'comuna') {
                input.setAttribute('required', 'required');
            }
        } else {
            input.removeAttribute('required');
        }
    });
    
    empresaInputs.forEach(input => {
        if (!tipoPersonal) {
            if (input.name === 'nombre' || input.name === 'numero_cliente') {
                input.setAttribute('required', 'required');
            }
        } else {
            input.removeAttribute('required');
        }
    });
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', toggleTipoColaborador);
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
