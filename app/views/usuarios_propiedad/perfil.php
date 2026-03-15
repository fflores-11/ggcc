<?php 
require_once VIEWS_PATH . '/partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-person-circle me-2"></i>Mi Perfil
        </h1>
        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
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

    <div class="row">
        <!-- Columna izquierda: Información de la propiedad -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-house me-1"></i>Mi Propiedad
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-house-door display-1 text-primary"></i>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($usuario['propiedad_nombre']); ?></h4>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($usuario['propiedad_tipo']); ?></p>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p class="mb-1">
                            <strong><i class="bi bi-person me-1"></i>Propietario:</strong><br>
                            <?php echo htmlspecialchars($usuario['nombre_dueno']); ?>
                        </p>
                        <p class="mb-1">
                            <strong><i class="bi bi-building me-1"></i>Comunidad:</strong><br>
                            <?php echo htmlspecialchars($usuario['comunidad_nombre']); ?>
                        </p>
                        <p class="mb-1">
                            <strong><i class="bi bi-geo-alt me-1"></i>Dirección:</strong><br>
                            <?php echo htmlspecialchars($usuario['comunidad_direccion']); ?>
                        </p>
                        <p class="mb-0">
                            <strong><i class="bi bi-cash me-1"></i>Gastos Comunes:</strong><br>
                            $<?php echo number_format($usuario['precio_gastos_comunes'], 0, ',', '.'); ?> mensual
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-info-circle me-1"></i>Contacto Administración
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Presidente:</strong><br>
                        <?php echo htmlspecialchars($usuario['nombre_presidente'] ?? 'No disponible'); ?>
                    </p>
                    
                    <?php if (!empty($usuario['whatsapp_presidente'])): ?>
                    <p class="mb-2">
                        <strong>WhatsApp:</strong><br>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $usuario['whatsapp_presidente']); ?>" 
                           target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-whatsapp me-1"></i><?php echo htmlspecialchars($usuario['whatsapp_presidente']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($usuario['email_presidente'])): ?>
                    <p class="mb-0">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($usuario['email_presidente']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($usuario['email_presidente']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Formulario de edición -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pencil-square me-1"></i>Editar Mis Datos
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>perfil.php?action=updatePerfil" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <h6 class="fw-bold mb-3 text-muted">Información de Contacto</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                <div class="invalid-feedback">Ingrese un email válido</div>
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                       value="<?php echo htmlspecialchars($usuario['whatsapp'] ?? ''); ?>"
                                       placeholder="+56912345678">
                                <div class="form-text">Formato: +569XXXXXXXX</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3 text-muted">Cambiar Contraseña</h6>
                        <p class="text-muted small">Complete estos campos solo si desea cambiar su contraseña</p>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="password_actual" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="password_actual" name="password_actual">
                            </div>

                            <div class="col-md-4">
                                <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" minlength="6">
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>

                            <div class="col-md-4">
                                <label for="password_confirmar" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-muted">
                        <i class="bi bi-info-circle me-1"></i>Información de la Cuenta
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Usuario:</strong> 
                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                            </p>
                            <p class="mb-1">
                                <strong>Último acceso:</strong> 
                                <?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Cuenta creada:</strong> 
                                <?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Estado:</strong> 
                                <?php if ($usuario['activo']): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactiva</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Validar contraseñas si se están cambiando
            const passwordActual = document.getElementById('password_actual').value;
            const passwordNuevo = document.getElementById('password_nuevo').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;
            
            if (passwordActual || passwordNuevo || passwordConfirmar) {
                if (!passwordActual) {
                    event.preventDefault();
                    event.stopPropagation();
                    alert('Debe ingresar su contraseña actual para cambiarla');
                    document.getElementById('password_actual').focus();
                    return;
                }
                if (passwordNuevo !== passwordConfirmar) {
                    event.preventDefault();
                    event.stopPropagation();
                    alert('Las contraseñas nuevas no coinciden');
                    document.getElementById('password_confirmar').focus();
                    return;
                }
                if (passwordNuevo.length < 6) {
                    event.preventDefault();
                    event.stopPropagation();
                    alert('La nueva contraseña debe tener al menos 6 caracteres');
                    document.getElementById('password_nuevo').focus();
                    return;
                }
            }
            
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
