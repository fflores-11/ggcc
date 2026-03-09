<?php
/**
 * Vista: Formulario de Usuario (Crear/Editar)
 */

$isEdit = isset($usuario);
$title = $isEdit ? 'Editar Usuario' : 'Nuevo Usuario';

require_once __DIR__ . '/../partials/header.php';

// Valores por defecto
$usuario = $usuario ?? [
    'id' => '',
    'nombre' => '',
    'email' => '',
    'rol' => 'administrador',
    'activo' => 1,
    'comunidad_id' => ''
];

// Obtener comunidades para el selector
$comunidadModel = new Comunidad();
$comunidades = $comunidadModel->getForSelect();
?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <a href="usuarios.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="usuarios.php?action=<?= $isEdit ? 'update' : 'store' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required
                           value="<?= e($usuario['nombre']) ?>" 
                           placeholder="Ingrese el nombre completo">
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= e($usuario['email']) ?>" 
                           placeholder="correo@ejemplo.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Contraseña 
                        <?php if ($isEdit): ?>
                            <small class="text-muted">(Dejar en blanco para mantener la actual)</small>
                        <?php else: ?>
                            <span class="text-danger">*</span>
                        <?php endif; ?>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               <?= !$isEdit ? 'required' : '' ?> minlength="6"
                               placeholder="Mínimo 6 caracteres">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (!$isEdit): ?>
                        <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select" id="rol" name="rol" required onchange="toggleComunidadField()">
                        <option value="administrador" <?= $usuario['rol'] === 'administrador' ? 'selected' : '' ?>>
                            Administrador
                        </option>
                        <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>
                            Super Admin
                        </option>
                        <option value="presidente" <?= $usuario['rol'] === 'presidente' ? 'selected' : '' ?>>
                            Presidente
                        </option>
                    </select>
                    <div class="form-text">
                        <strong>Super Admin:</strong> Control total del sistema<br>
                        <strong>Administrador:</strong> Gestión operativa de UNA comunidad<br>
                        <strong>Presidente:</strong> Acceso limitado a su comunidad
                    </div>
                </div>

                <!-- Campo Comunidad - Solo visible para Administrador y Presidente -->
                <div class="mb-4" id="comunidad_field" style="display: <?= in_array($usuario['rol'], ['administrador', 'presidente']) ? 'block' : 'none' ?>;">
                    <label for="comunidad_id" class="form-label">Comunidad Asignada <span class="text-danger">*</span></label>
                    <select class="form-select" id="comunidad_id" name="comunidad_id">
                        <option value="">Seleccione una comunidad...</option>
                        <?php foreach ($comunidades as $com): ?>
                            <option value="<?= $com['id'] ?>" <?= $usuario['comunidad_id'] == $com['id'] ? 'selected' : '' ?>>
                                <?= e($com['nombre']) ?> (<?= e($com['comuna'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        El usuario solo podrá acceder a los registros de esta comunidad.
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                               value="1" <?= $usuario['activo'] ? 'checked' : '' ?>
                               <?= ($isEdit && $usuario['id'] == getUserId()) ? 'disabled' : '' ?>>
                        <label class="form-check-label" for="activo">Usuario Activo</label>
                    </div>
                    <?php if ($isEdit && $usuario['id'] == getUserId()): ?>
                        <div class="form-text text-warning">
                            <i class="bi bi-info-circle me-1"></i>
                            No puede desactivar su propia cuenta
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="usuarios.php" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Actualizar Usuario' : 'Crear Usuario' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($isEdit && ($usuario['id'] == getUserId() || getUserRole() === 'admin')): ?>
        <!-- Sección de Firma Digital -->
        <div class="form-section mt-4">
            <h6 class="mb-3"><i class="bi bi-pen me-2"></i>Firma Digital</h6>
            <p class="text-muted mb-3">
                Suba una imagen de su firma para usar en los recibos de pago. La imagen debe ser PNG o JPG con fondo transparente o blanco.
            </p>
            
            <?php
            $userModel = new Usuario();
            $tieneFirma = $userModel->firmaExists($usuario['id']);
            $firmaUrl = $tieneFirma ? $userModel->getFirmaUrl($usuario['id']) : '';
            ?>
            
            <?php if ($tieneFirma): ?>
                <div class="mb-3">
                    <label class="form-label">Firma Actual:</label>
                    <div class="border rounded p-3 text-center bg-light">
                        <img src="<?= $firmaUrl ?>" alt="Firma actual" style="max-height: 100px; max-width: 100%;">
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="usuarios.php?action=subir-firma" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                
                <div class="mb-3">
                    <label for="firma" class="form-label"><?= $tieneFirma ? 'Cambiar Firma:' : 'Subir Firma:' ?></label>
                    <input type="file" class="form-control" id="firma" name="firma" accept="image/png,image/jpeg,image/gif,image/webp" required>
                    <div class="form-text">
                        Formatos: PNG, JPG, GIF, WEBP. Máximo 2MB.
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-2"></i><?= $tieneFirma ? 'Actualizar Firma' : 'Subir Firma' ?>
                    </button>
                    
                    <?php if ($tieneFirma): ?>
                    <a href="usuarios.php?action=eliminar-firma&id=<?= $usuario['id'] ?>" 
                       class="btn btn-outline-danger"
                       onclick="return confirm('¿Está seguro de eliminar su firma?')">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted mb-3">
                Los usuarios del sistema pueden tener diferentes roles según sus responsabilidades:
            </p>
            <ul class="list-unstyled text-muted">
                <li class="mb-2">
                    <span class="badge bg-danger">Super Admin</span><br>
                    <small>Acceso completo a todas las funciones</small>
                </li>
                <li class="mb-2">
                    <span class="badge bg-primary">Administrador</span><br>
                    <small>Gestión de comunidades, propiedades y pagos</small>
                </li>
                <li>
                    <span class="badge bg-secondary">Presidente</span><br>
                    <small>Visualización de reportes de su comunidad</small>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Mostrar/ocultar campo comunidad según el rol
    function toggleComunidadField() {
        const rolSelect = document.getElementById('rol');
        const comunidadField = document.getElementById('comunidad_field');
        const comunidadSelect = document.getElementById('comunidad_id');
        
        if (rolSelect.value === 'administrador' || rolSelect.value === 'presidente') {
            comunidadField.style.display = 'block';
            comunidadSelect.setAttribute('required', 'required');
        } else {
            comunidadField.style.display = 'none';
            comunidadSelect.removeAttribute('required');
            comunidadSelect.value = '';
        }
    }

    // Ejecutar al cargar la página
    toggleComunidadField();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
