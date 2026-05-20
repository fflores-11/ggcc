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
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>">
                                <div class="form-text">Campo opcional</div>
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

            <!-- Formulario de edición de propiedad -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-house-gear me-1"></i>Editar Datos de la Propiedad
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>perfil.php?action=updatePropiedad" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <h6 class="fw-bold mb-3 text-muted">Información del Dueño</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_dueno" class="form-label">Nombre del Dueño <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_dueno" name="nombre_dueno" 
                                       value="<?php echo htmlspecialchars($usuario['nombre_dueno']); ?>" required>
                                <div class="invalid-feedback">El nombre del dueño es obligatorio</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email_dueno" class="form-label">Email del Dueño</label>
                                <input type="email" class="form-control" id="email_dueno" name="email_dueno" 
                                       value="<?php echo htmlspecialchars($usuario['email_dueno']); ?>">
                                <div class="form-text">Campo opcional</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="whatsapp_dueno" class="form-label">WhatsApp del Dueño</label>
                                <input type="text" class="form-control" id="whatsapp_dueno" name="whatsapp_dueno" 
                                       value="<?php echo htmlspecialchars($usuario['whatsapp_dueno'] ?? ''); ?>"
                                       placeholder="+56912345678">
                                <div class="form-text">Formato: +569XXXXXXXX</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3 text-muted">Información del Agente (Opcional)</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre_agente" class="form-label">Nombre del Agente</label>
                                <input type="text" class="form-control" id="nombre_agente" name="nombre_agente" 
                                       value="<?php echo htmlspecialchars($usuario['nombre_agente'] ?? ''); ?>">
                                <div class="form-text">Campo opcional</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email_agente" class="form-label">Email del Agente</label>
                                <input type="email" class="form-control" id="email_agente" name="email_agente" 
                                       value="<?php echo htmlspecialchars($usuario['email_agente'] ?? ''); ?>">
                                <div class="form-text">Campo opcional</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="whatsapp_agente" class="form-label">WhatsApp del Agente</label>
                                <input type="text" class="form-control" id="whatsapp_agente" name="whatsapp_agente" 
                                       value="<?php echo htmlspecialchars($usuario['whatsapp_agente'] ?? ''); ?>"
                                       placeholder="+56912345678">
                                <div class="form-text">Formato: +569XXXXXXXX</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Nota:</strong> No puede editar el nombre de la propiedad, la comunidad ni el monto de gastos comunes. 
                            Para modificar estos datos, contacte a la administración.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Guardar Cambios de Propiedad
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sección de Mascotas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-heart me-1"></i>Mis Mascotas
                    </h6>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarMascota">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Mascota
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($mascotas)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-heart display-4 text-muted"></i>
                            <p class="text-muted mt-2">No tiene mascotas registradas</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMascota">
                                <i class="bi bi-plus-circle me-1"></i>Agregar Primera Mascota
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($mascotas as $mascota): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <?php if (!empty($mascota['imagen_path'])): ?>
                                                    <img src="<?php echo BASE_URL_FULL . $mascota['imagen_path']; ?>" 
                                                         alt="<?php echo htmlspecialchars($mascota['nombre']); ?>" 
                                                         class="rounded me-3" style="width: 200px; height: 200px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 200px; height: 200px;">
                                                        <i class="bi bi-heart display-4 text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($mascota['nombre']); ?></h6>
                                                    <span class="badge bg-info mb-2"><?php echo htmlspecialchars($mascota['tipo']); ?></span>
                                                    <p class="card-text small mb-1">
                                                        <strong>Edad:</strong> <?php echo $mascota['edad']; ?> años
                                                    </p>
                                                    <?php if (!empty($mascota['alimento'])): ?>
                                                        <p class="card-text small mb-0">
                                                            <strong>Alimento:</strong> <?php echo htmlspecialchars($mascota['alimento']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarMascota<?php echo $mascota['id']; ?>">
                                                    <i class="bi bi-pencil me-1"></i>Editar
                                                </button>
                                                <a href="<?php echo BASE_URL; ?>perfil.php?action=eliminarMascota&id=<?php echo $mascota['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Está seguro de eliminar esta mascota?')">
                                                    <i class="bi bi-trash me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Editar Mascota -->
                                <div class="modal fade" id="modalEditarMascota<?php echo $mascota['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Mascota
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="<?php echo BASE_URL; ?>perfil.php?action=actualizarMascota" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="mascota_id" value="<?php echo $mascota['id']; ?>">

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nombre" 
                                                                   value="<?php echo htmlspecialchars($mascota['nombre']); ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="tipo" required>
                                                                <option value="Gato" <?php echo $mascota['tipo'] === 'Gato' ? 'selected' : ''; ?>>Gato</option>
                                                                <option value="Perro" <?php echo $mascota['tipo'] === 'Perro' ? 'selected' : ''; ?>>Perro</option>
                                                                <option value="Ave" <?php echo $mascota['tipo'] === 'Ave' ? 'selected' : ''; ?>>Ave</option>
                                                                <option value="Hamster" <?php echo $mascota['tipo'] === 'Hamster' ? 'selected' : ''; ?>>Hamster</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Edad (años)</label>
                                                            <input type="number" class="form-control" name="edad" min="0" 
                                                                   value="<?php echo $mascota['edad']; ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Alimento que come</label>
                                                            <input type="text" class="form-control" name="alimento" 
                                                                   value="<?php echo htmlspecialchars($mascota['alimento'] ?? ''); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Cambiar Imagen</label>
                                                        <input type="file" class="form-control" name="imagen" accept="image/*">
                                                        <div class="form-text">Formatos permitidos: JPEG, PNG, GIF. Máximo 5MB.</div>
                                                        
                                                        <?php if (!empty($mascota['imagen_path'])): ?>
                                                            <div class="mt-2">
                                                                <small class="text-muted">Imagen actual:</small><br>
                                                                <img src="<?php echo BASE_URL_FULL . $mascota['imagen_path']; ?>" 
                                                                     alt="<?php echo htmlspecialchars($mascota['nombre']); ?>" 
                                                                     class="rounded mt-1" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal Agregar Mascota -->
            <div class="modal fade" id="modalAgregarMascota" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle me-2"></i>Agregar Nueva Mascota
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="<?php echo BASE_URL; ?>perfil.php?action=agregarMascota" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nombre" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                        <select class="form-select" name="tipo" required>
                                            <option value="">Seleccione un tipo</option>
                                            <option value="Gato">Gato</option>
                                            <option value="Perro">Perro</option>
                                            <option value="Ave">Ave</option>
                                            <option value="Hamster">Hamster</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Edad (años)</label>
                                        <input type="number" class="form-control" name="edad" min="0" placeholder="Ej: 3">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Alimento que come</label>
                                        <input type="text" class="form-control" name="alimento" placeholder="Ej: Croquetas Premium">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Imagen de la Mascota</label>
                                    <input type="file" class="form-control" name="imagen" accept="image/*">
                                    <div class="form-text">Formatos permitidos: JPEG, PNG, GIF. Máximo 5MB.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Mascota
                                </button>
                            </div>
                        </form>
                    </div>
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
