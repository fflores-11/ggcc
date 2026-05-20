<?php
/**
 * Vista: Envío de Cobranzas
 */

$title = 'Envío de Cobranzas';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Envío de Cobranzas Mensuales</h4>
    <a href="correos.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="correos.php?action=enviar-cobranza" method="POST" id="cobranzaForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <h6 class="mb-3 text-primary">Selección de Período</h6>
                
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
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Mes <span class="text-danger">*</span></label>
                        <select name="mes" class="form-select" required>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                    <?= getMonthName($i) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año <span class="text-danger">*</span></label>
                        <select name="anio" class="form-select" required>
                            <?php foreach (getYearList(1, 1) as $year): ?>
                                <option value="<?= $year ?>" <?= $year == date('Y') ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Contenido de la Cobranza</h6>

                <div class="mb-3">
                    <label class="form-label">Asunto <span class="text-danger">*</span></label>
                    <input type="text" name="asunto" class="form-control form-control-lg" required
                           value="Cobranza Gastos Comunes - "
                           placeholder="Ej: Cobranza Gastos Comunes - Enero 2025">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                    <textarea name="body" id="editor" class="form-control" rows="12" required>
<p>Estimado/a {nombre_dueno}:</p>

<p>Le recordamos que tiene una deuda pendiente correspondiente a <strong>{mes} {anio}</strong> por concepto de Gastos Comunes de su propiedad <strong>{nombre_propiedad}</strong>.</p>

<p><strong>Monto adeudado: {monto_deuda}</strong></p>

<p>Por favor, regularice su situación a la brevedad. Si ya realizó el pago, por favor ignore este mensaje.</p>

<p>Atentamente,<br>
{presidente}<br>
Presidente de la Comunidad {comunidad}</p>

<p><small>Este es un mensaje automático del sistema de administración de gastos comunes.</small></p>
                    </textarea>
                </div>

                <hr class="my-4">
                <h6 class="mb-3 text-primary">Vista Previa</h6>
                
                <div class="alert alert-light border">
                    <div id="preview" class="email-preview">
                        <p class="text-muted fst-italic">El mensaje se personalizará para cada propiedad con su deuda específica.</p>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Importante:</strong> Este envío solo llegará a las propiedades que tengan deudas pendientes del período seleccionado.
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="correos.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning" id="btn_enviar">
                        <i class="bi bi-send me-2"></i>Enviar Cobranzas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Variables Disponibles</h6>
            <p class="text-muted mb-3">
                Para cobranzas, además de las variables básicas, puede usar:
            </p>
            
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><td><code>{nombre_propiedad}</code></td><td class="text-muted">Nombre de la propiedad</td></tr>
                    <tr><td><code>{nombre_dueno}</code></td><td class="text-muted">Nombre del propietario</td></tr>
                    <tr><td><code>{monto_deuda}</code></td><td class="text-muted">Monto total adeudado</td></tr>
                    <tr><td><code>{mes}</code></td><td class="text-muted">Mes de la deuda</td></tr>
                    <tr><td><code>{anio}</code></td><td class="text-muted">Año de la deuda</td></tr>
                    <tr><td><code>{comunidad}</code></td><td class="text-muted">Nombre de la comunidad</td></tr>
                    <tr><td><code>{presidente}</code></td><td class="text-muted">Nombre del presidente</td></tr>
                </tbody>
            </table>

            <hr class="my-3">
            
            <h6 class="mb-2"><i class="bi bi-lightbulb me-2"></i>Consejo</h6>
            <p class="text-muted small">
                El monto de deuda se calcula automáticamente según las cuotas pendientes de cada propiedad 
                para el período seleccionado.
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#editor',
        height: 350,
        menubar: false,
        plugins: 'lists link emoticons',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link emoticons'
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
