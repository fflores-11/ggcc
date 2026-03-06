<?php
/**
 * Vista: Listado de Configuraciones SMTP
 * Solo para super usuarios (admin)
 */

$title = 'Configuración SMTP por Comunidad';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-envelope-gear me-2"></i>Configuración SMTP</h4>
    <span class="badge bg-danger">Solo Super Usuario</span>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Configure el servidor SMTP para cada comunidad. Los envíos de correo masivo utilizarán la configuración específica de cada comunidad.
</div>

<!-- Configuraciones existentes -->
<div class="table-container mb-4">
    <div class="table-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Configuraciones Activas</h5>
    </div>
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Comunidad</th>
                    <th>Host SMTP</th>
                    <th>Puerto</th>
                    <th>Usuario</th>
                    <th>Email Remitente</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($configuraciones)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-envelope-slash display-4 d-block mb-3"></i>
                            No hay configuraciones SMTP registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($configuraciones as $config): ?>
                        <tr>
                            <td>
                                <strong><?= e($config['comunidad_nombre']) ?></strong><br>
                                <small class="text-muted"><?= e($config['comuna']) ?></small>
                            </td>
                            <td><?= e($config['host']) ?></td>
                            <td><?= $config['port'] ?> (<?= e($config['encryption']) ?>)</td>
                            <td><?= e($config['username']) ?></td>
                            <td>
                                <?= e($config['from_email']) ?><br>
                                <small class="text-muted"><?= e($config['from_name']) ?></small>
                            </td>
                            <td class="text-center">
                                <a href="configuracion_smtp.php?action=create&comunidad_id=<?= $config['comunidad_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button onclick="testSMTP(<?= $config['comunidad_id'] ?>)" 
                                        class="btn btn-sm btn-outline-info me-1" title="Probar conexión">
                                    <i class="bi bi-lightning-charge"></i>
                                </button>
                                <a href="configuracion_smtp.php?action=delete&id=<?= $config['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar esta configuración?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Comunidades sin configuración -->
<?php if (!empty($comunidadesSinConfig)): ?>
<div class="form-section">
    <div class="section-title">Comunidades sin Configuración SMTP</div>
    <p class="text-muted mb-3">Seleccione una comunidad para configurar su servidor SMTP:</p>
    
    <div class="row">
        <?php foreach ($comunidadesSinConfig as $com): ?>
            <div class="col-md-4 mb-3">
                <a href="configuracion_smtp.php?action=create&comunidad_id=<?= $com['id'] ?>" 
                   class="card text-decoration-none border-primary h-100">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="bi bi-plus-circle me-2"></i><?= e($com['nombre']) ?>
                        </h6>
                        <p class="card-text text-muted small"><?= e($com['comuna']) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function testSMTP(comunidadId) {
    fetch('configuracion_smtp.php?action=test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'comunidad_id=' + comunidadId + '&csrf_token=<?= generateCSRFToken() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error al probar conexión: ' + error);
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
