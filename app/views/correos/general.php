<?php
/**
 * Vista: Envío General de Correo
 */

$title = 'Envío General a Comunidad';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Envío General a Comunidad</h4>
    <a href="correos.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="correos.php?action=enviar-general" method="POST" id="emailForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <h6 class="mb-3 text-primary">Destinatarios</h6>
                
                <div class="mb-4">
                    <label class="form-label">Comunidad <span class="text-danger">*</span></label>
                    <select name="comunidad_id" class="form-select form-select-lg" required id="comunidad_select">
                        <option value="">Seleccione una comunidad...</option>
                        <?php foreach ($comunidades as $com): ?>
                            <option value="<?= $com['id'] ?>">
                                <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        El correo se enviará a <strong>todas las propiedades</strong> activas de la comunidad seleccionada.
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Contenido del Correo</h6>

                <div class="mb-3">
                    <label class="form-label">Asunto <span class="text-danger">*</span></label>
                    <input type="text" name="asunto" class="form-control form-control-lg" required
                           placeholder="Ej: Aviso importante - Mantenimiento programado">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                    <textarea name="body" id="editor" class="form-control" rows="10" required
                              placeholder="Escriba el contenido del correo aquí..."></textarea>
                    <div class="form-text">
                        Puede usar formato HTML para dar estilo al mensaje.
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Vista Previa</h6>
                
                <div class="alert alert-light border">
                    <div id="preview" class="email-preview">
                        <p class="text-muted fst-italic">Seleccione una comunidad y escriba el mensaje para ver la vista previa...</p>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="correos.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom" id="btn_enviar">
                        <i class="bi bi-send me-2"></i>Enviar a toda la comunidad
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Variables Disponibles</h6>
            <p class="text-muted mb-3">
                Puede usar estas variables en el mensaje. Serán reemplazadas por los datos de cada propiedad:
            </p>
            
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><td><code>{nombre_propiedad}</code></td><td class="text-muted">Nombre de la propiedad</td></tr>
                    <tr><td><code>{nombre_dueno}</code></td><td class="text-muted">Nombre del propietario</td></tr>
                    <tr><td><code>{comunidad}</code></td><td class="text-muted">Nombre de la comunidad</td></tr>
                    <tr><td><code>{direccion}</code></td><td class="text-muted">Dirección de la comunidad</td></tr>
                    <tr><td><code>{presidente}</code></td><td class="text-muted">Nombre del presidente</td></tr>
                </tbody>
            </table>

            <hr class="my-3">
            
            <h6 class="mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Nota Importante</h6>
            <p class="text-muted small">
                Este envío llegará a <strong>todas las propiedades</strong> de la comunidad seleccionada. 
                Asegúrese de verificar la vista previa antes de enviar.
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar TinyMCE
    tinymce.init({
        selector: '#editor',
        height: 300,
        menubar: false,
        plugins: 'lists link emoticons',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link emoticons',
        setup: function(editor) {
            editor.on('change', function() {
                actualizarVistaPrevia();
            });
        }
    });

    // Actualizar vista previa
    function actualizarVistaPrevia() {
        const comunidadId = document.getElementById('comunidad_select').value;
        const body = tinymce.get('editor').getContent();
        const preview = document.getElementById('preview');
        
        if (!comunidadId || !body) {
            preview.innerHTML = '<p class="text-muted fst-italic">Seleccione una comunidad y escriba el mensaje para ver la vista previa...</p>';
            return;
        }

        // Enviar petición AJAX para obtener vista previa procesada
        fetch('correos.php?action=preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'comunidad_id=' + comunidadId + '&body=' + encodeURIComponent(body)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                preview.innerHTML = data.preview;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Mostrar vista previa básica sin procesar variables
            preview.innerHTML = body;
        });
    }

    document.getElementById('comunidad_select').addEventListener('change', actualizarVistaPrevia);
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
