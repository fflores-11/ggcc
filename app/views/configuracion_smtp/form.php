<?php
/**
 * Vista: Formulario de Configuración SMTP
 */

$isEdit = isset($config);
$title = $isEdit ? 'Editar Configuración SMTP' : 'Nueva Configuración SMTP';

require_once __DIR__ . '/../partials/header.php';

// Valores por defecto
$config = $config ?? [
    'id' => '',
    'comunidad_id' => $_GET['comunidad_id'] ?? '',
    'host' => '',
    'port' => 587,
    'username' => '',
    'password' => '',
    'encryption' => 'tls',
    'from_email' => '',
    'from_name' => ''
];

$encryptionOptions = ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Sin encriptación'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <div class="d-flex gap-2">
        <span class="badge bg-danger">Solo Super Usuario</span>
        <a href="configuracion_smtp.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<?php if (isset($comunidad)): ?>
<div class="alert alert-info mb-4">
    <strong>Comunidad:</strong> <?= e($comunidad['nombre']) ?> (<?= e($comunidad['comuna']) ?>)
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="configuracion_smtp.php?action=store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $config['id'] ?>">
                <?php endif; ?>

                <!-- Comunidad -->
                <div class="mb-4">
                    <label class="form-label">Comunidad <span class="text-danger">*</span></label>
                    <?php if (isset($comunidad)): ?>
                        <input type="hidden" name="comunidad_id" value="<?= $comunidad['id'] ?>">
                        <input type="text" class="form-control" value="<?= e($comunidad['nombre']) ?>" disabled>
                    <?php else: ?>
                        <select name="comunidad_id" class="form-select" required>
                            <option value="">Seleccione una comunidad...</option>
                            <?php foreach ($comunidades as $com): ?>
                                <option value="<?= $com['id'] ?>" <?= $config['comunidad_id'] == $com['id'] ? 'selected' : '' ?>>
                                    <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary"><i class="bi bi-hdd-network me-2"></i>Servidor SMTP</h6>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Host SMTP <span class="text-danger">*</span></label>
                        <input type="text" name="host" class="form-control" required
                               value="<?= e($config['host']) ?>" 
                               placeholder="Ej: smtp.gmail.com, mail.tudominio.cl">
                        <div class="form-text">Servidor de correo saliente</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Puerto <span class="text-danger">*</span></label>
                        <input type="number" name="port" class="form-control" required
                               value="<?= $config['port'] ?>" 
                               placeholder="587">
                        <div class="form-text">587 (TLS) o 465 (SSL)</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Encriptación</label>
                    <select name="encryption" class="form-select">
                        <?php foreach ($encryptionOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $config['encryption'] === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary"><i class="bi bi-person-badge me-2"></i>Autenticación</h6>

                <div class="mb-3">
                    <label class="form-label">Usuario SMTP <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= e($config['username']) ?>" 
                           placeholder="Ej: tuemail@gmail.com">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña SMTP <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" required
                               value="<?= e($config['password']) ?>" 
                               placeholder="Contraseña o app password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">
                        Para Gmail, use una "App Password" en lugar de su contraseña regular.
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary"><i class="bi bi-envelope me-2"></i>Remitente</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Remitente <span class="text-danger">*</span></label>
                        <input type="email" name="from_email" class="form-control" required
                               value="<?= e($config['from_email']) ?>" 
                               placeholder="noreply@condominio.cl">
                        <div class="form-text">Email que aparecerá como remitente</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Remitente <span class="text-danger">*</span></label>
                        <input type="text" name="from_name" class="form-control" required
                               value="<?= e($config['from_name']) ?>" 
                               placeholder="Ej: Administración Condominio">
                        <div class="form-text">Nombre que aparecerá en los correos</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="configuracion_smtp.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check-lg me-2"></i>Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Configuración Común</h6>
            
            <div class="alert alert-light border">
                <strong>Gmail:</strong><br>
                Host: smtp.gmail.com<br>
                Puerto: 587<br>
                Encriptación: TLS<br>
                Usuario: tuemail@gmail.com<br>
                <small class="text-muted">Use App Password, no su contraseña regular</small>
            </div>

            <div class="alert alert-light border">
                <strong>Outlook/Hotmail:</strong><br>
                Host: smtp-mail.outlook.com<br>
                Puerto: 587<br>
                Encriptación: TLS
            </div>

            <div class="alert alert-light border">
                <strong>cPanel/Webmail:</strong><br>
                Host: mail.tudominio.cl<br>
                Puerto: 587 o 465<br>
                Encriptación: TLS o SSL
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
