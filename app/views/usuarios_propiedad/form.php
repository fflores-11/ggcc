<?php 
require_once VIEWS_PATH . '/partials/header.php';

$isEdit = isset($usuario);
$comunidadIdSeleccionada = $isEdit ? ($usuario['comunidad_id'] ?? '') : '';
$propiedadIdSeleccionada = $isEdit ? ($usuario['propiedad_id'] ?? '') : '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-house-door me-2"></i>
            <?php echo $isEdit ? 'Editar Usuario por Propiedad' : 'Nuevo Usuario por Propiedad'; ?>
        </h1>
        <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo $isEdit ? 'Información del Usuario' : 'Datos del Nuevo Usuario'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=<?php echo $isEdit ? 'update' : 'store'; ?>" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <?php endif; ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="comunidad_id" class="form-label">Comunidad <span class="text-danger">*</span></label>
                                <select class="form-select" id="comunidad_id" name="comunidad_id" required 
                                        <?php echo $isEdit ? 'disabled' : ''; ?>>
                                    <option value="">Seleccione una comunidad</option>
                                    <?php foreach ($comunidades as $comunidad): ?>
                                        <option value="<?php echo $comunidad['id']; ?>" 
                                                <?php echo $comunidadIdSeleccionada == $comunidad['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($comunidad['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="comunidad_id" value="<?php echo $comunidadIdSeleccionada; ?>">
                                <?php endif; ?>
                                <div class="invalid-feedback">Debe seleccionar una comunidad</div>
                            </div>

                            <div class="col-md-6">
                                <label for="propiedad_id" class="form-label">Propiedad <span class="text-danger">*</span></label>
                                <select class="form-select" id="propiedad_id" name="propiedad_id" required 
                                        <?php echo $isEdit ? 'disabled' : ''; ?>>
                                    <option value="">Seleccione una propiedad</option>
                                    <?php if ($isEdit && !empty($usuario['propiedad_id'])): ?>
                                        <option value="<?php echo $usuario['propiedad_id']; ?>" selected>
                                            <?php echo htmlspecialchars($usuario['propiedad_nombre']); ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="propiedad_id" value="<?php echo $propiedadIdSeleccionada; ?>">
                                <?php endif; ?>
                                <div class="invalid-feedback">Debe seleccionar una propiedad</div>
                                <div class="form-text">Solo se muestran propiedades sin usuario asignado</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre de Usuario</label>
                                <input type="text" class="form-control bg-light" id="nombre" name="nombre" readonly
                                       value="<?php echo $isEdit ? htmlspecialchars($usuario['nombre']) : 'Se generará automáticamente'; ?>">
                                <div class="form-text">Generado automáticamente desde el nombre de la propiedad. Este será el nombre de usuario para iniciar sesión.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $isEdit ? htmlspecialchars($usuario['email']) : ''; ?>" required>
                                <div class="invalid-feedback">Ingrese un email válido</div>
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                       value="<?php echo $isEdit ? htmlspecialchars($usuario['whatsapp'] ?? '') : ''; ?>"
                                       placeholder="+56912345678">
                                <div class="form-text">Formato: +569XXXXXXXX</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Contraseña 
                                    <?php if (!$isEdit): ?><span class="text-danger">*</span><?php endif; ?>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="password" name="password" 
                                           value="<?php echo !$isEdit && isset($passwordGenerada) ? $passwordGenerada : ''; ?>"
                                           <?php echo !$isEdit ? 'required' : ''; ?>
                                           minlength="6">
                                    <?php if (!$isEdit): ?>
                                        <button type="button" class="btn btn-outline-secondary" onclick="generarPassword()">
                                            <i class="bi bi-shuffle"></i> Generar
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">
                                    <?php if ($isEdit): ?>
                                        Deje en blanco para mantener la contraseña actual
                                    <?php else: ?>
                                        Mínimo 6 caracteres. Se genera automáticamente.
                                    <?php endif; ?>
                                </div>
                                <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                            </div>

                            <?php if ($isEdit): ?>
                            <div class="col-md-6">
                                <label class="form-label">Información de la Propiedad</label>
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <strong><?php echo htmlspecialchars($usuario['propiedad_nombre']); ?></strong><br>
                                        <small class="text-muted">
                                            Propietario: <?php echo htmlspecialchars($usuario['nombre_dueno']); ?><br>
                                            Comunidad: <?php echo htmlspecialchars($usuario['comunidad_nombre']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                <?php echo $isEdit ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-info-circle me-1"></i>Información
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Campos no editables:</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="bi bi-x-circle text-danger me-1"></i>Comunidad</li>
                        <li><i class="bi bi-x-circle text-danger me-1"></i>Propiedad</li>
                        <li><i class="bi bi-x-circle text-danger me-1"></i>Nombre de usuario</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold">Campos editables:</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="bi bi-check-circle text-success me-1"></i>Email</li>
                        <li><i class="bi bi-check-circle text-success me-1"></i>WhatsApp</li>
                        <li><i class="bi bi-check-circle text-success me-1"></i>Contraseña</li>
                    </ul>

                    <hr>

                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Nota:</strong> Una vez creado el usuario, no se puede cambiar la propiedad asignada. 
                        Si necesita asignar otro usuario a esta propiedad, primero debe desactivar el usuario actual.
                    </div>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="card shadow mt-3">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-key me-1"></i>Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=generarNuevaPassword&id=<?php echo $usuario['id']; ?>" 
                       class="btn btn-warning w-100 mb-2"
                       onclick="return confirm('¿Generar nueva contraseña para este usuario? Se mostrará en pantalla.')">
                        <i class="bi bi-key me-1"></i>Generar Nueva Contraseña
                    </a>
                    
                    <?php if ($usuario['activo']): ?>
                        <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=delete&id=<?php echo $usuario['id']; ?>" 
                           class="btn btn-danger w-100"
                           onclick="return confirm('¿Está seguro de desactivar este usuario?')">
                            <i class="bi bi-trash me-1"></i>Desactivar Usuario
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>usuarios_propiedad.php?action=restore&id=<?php echo $usuario['id']; ?>" 
                           class="btn btn-success w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reactivar Usuario
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Generar contraseña aleatoria
function generarPassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let password = '';
    for (let i = 0; i < 10; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('password').value = password;
}

// Variable para almacenar las propiedades cargadas
let propiedadesCargadas = {};

// Cargar propiedades según comunidad seleccionada
document.getElementById('comunidad_id').addEventListener('change', function() {
    const comunidadId = this.value;
    const propiedadSelect = document.getElementById('propiedad_id');
    
    // Limpiar select de propiedades
    propiedadSelect.innerHTML = '<option value="">Cargando...</option>';
    propiedadSelect.disabled = true;
    document.getElementById('nombre').value = 'Seleccione una propiedad';
    
    if (!comunidadId) {
        propiedadSelect.innerHTML = '<option value="">Seleccione una propiedad</option>';
        propiedadSelect.disabled = false;
        return;
    }
    
    // Cargar propiedades vía AJAX
    fetch('<?php echo BASE_URL; ?>usuarios_propiedad.php?action=getPropiedadesByComunidad&comunidad_id=' + comunidadId)
        .then(response => response.json())
        .then(data => {
            // Guardar propiedades para uso posterior
            propiedadesCargadas = {};
            data.forEach(propiedad => {
                propiedadesCargadas[propiedad.id] = propiedad;
            });
            
            let options = '<option value="">Seleccione una propiedad</option>';
            data.forEach(propiedad => {
                options += `<option value="${propiedad.id}">${propiedad.nombre} - ${propiedad.nombre_dueno}</option>`;
            });
            propiedadSelect.innerHTML = options;
            propiedadSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            propiedadSelect.innerHTML = '<option value="">Error al cargar propiedades</option>';
            propiedadSelect.disabled = false;
        });
});

// Actualizar nombre de usuario cuando se selecciona una propiedad
document.getElementById('propiedad_id').addEventListener('change', function() {
    const propiedadId = this.value;
    const nombreUsuarioInput = document.getElementById('nombre');
    
    if (propiedadId && propiedadesCargadas[propiedadId]) {
        const propiedad = propiedadesCargadas[propiedadId];
        // Generar nombre de usuario desde el nombre de la propiedad (formato slug)
        const nombreSlug = propiedad.nombre
            .toLowerCase()
            .replace(/\s+/g, '-')           // Reemplazar espacios por guiones
            .replace(/[^a-z0-9\-]/g, '')    // Eliminar caracteres especiales
            .replace(/\-+/g, '-')           // Evitar guiones múltiples
            .replace(/^\-+|\-+$/g, '');     // Eliminar guiones al inicio/final
        nombreUsuarioInput.value = nombreSlug;
    } else {
        nombreUsuarioInput.value = 'Se generará automáticamente';
    }
});

// Validación del formulario
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php 
require_once VIEWS_PATH . '/partials/footer.php';
?>
