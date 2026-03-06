<?php
/**
 * Vista: Formulario de Pago
 */

$title = 'Registrar Pago';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Registrar Nuevo Pago</h4>
    <a href="pagos.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al Listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form id="pagoForm" action="pagos.php?action=store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <h6 class="mb-3 text-primary">Selección de Propiedad</h6>
                
                <!-- Paso 1: Seleccionar Comunidad -->
                <div class="mb-4">
                    <label class="form-label">Comunidad <span class="text-danger">*</span></label>
                    <select id="comunidad_select" class="form-select form-select-lg" required>
                        <option value="">Seleccione una comunidad...</option>
                        <?php foreach ($comunidades as $com): ?>
                            <option value="<?= $com['id'] ?>" 
                                <?= (isset($comunidad) && $comunidad['id'] == $com['id']) ? 'selected' : '' ?>>
                                <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Paso 2: Seleccionar Propiedad -->
                <div class="mb-4">
                    <label class="form-label">Propiedad <span class="text-danger">*</span></label>
                    <select id="propiedad_select" name="propiedad_id" class="form-select form-select-lg" required disabled>
                        <option value="">Primero seleccione una comunidad...</option>
                    </select>
                    <div id="propiedad_loading" class="form-text d-none">
                        <i class="bi bi-hourglass-split me-1"></i>Cargando propiedades...
                    </div>
                </div>

                <!-- Paso 3: Mostrar Deudas -->
                <div id="deudas_section" class="mb-4 d-none">
                    <h6 class="mb-3 text-primary">Deudas Pendientes</h6>
                    <div id="deudas_loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Cargando deudas...</p>
                    </div>
                    <div id="deudas_container"></div>
                    <div id="no_deudas" class="alert alert-warning d-none">
                        <i class="bi bi-check-circle me-2"></i>
                        Esta propiedad no tiene deudas pendientes.
                    </div>
                </div>

                <!-- Totales -->
                <div id="totales_section" class="mb-4 d-none">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Total a Pagar:</span>
                            <span id="total_pagar" class="fs-4 fw-bold">$0</span>
                        </div>
                        <input type="hidden" name="monto" id="monto_input" value="0">
                    </div>
                </div>

                <!-- Detalles del Pago -->
                <div class="mb-4">
                    <h6 class="mb-3 text-primary">Detalles del Pago</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control" required 
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" 
                                  placeholder="Observaciones adicionales (opcional)"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="pagos.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" id="btn_submit" class="btn btn-primary-custom" disabled>
                        <i class="bi bi-check-lg me-2"></i>Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Instrucciones</h6>
            <ol class="text-muted">
                <li class="mb-2">Seleccione la <strong>comunidad</strong> a la que pertenece la propiedad.</li>
                <li class="mb-2">Seleccione la <strong>propiedad</strong> que realizará el pago.</li>
                <li class="mb-2">Marque las <strong>deudas</strong> que desea pagar (puede pagar una o varias).</li>
                <li class="mb-2">Verifique el <strong>monto total</strong> calculado automáticamente.</li>
                <li>Confirme la fecha y presione "Registrar Pago".</li>
            </ol>
            
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Una vez registrado, el pago no se puede modificar. Verifique bien antes de confirmar.
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const comunidadSelect = document.getElementById('comunidad_select');
    const propiedadSelect = document.getElementById('propiedad_select');
    const propiedadLoading = document.getElementById('propiedad_loading');
    const deudasSection = document.getElementById('deudas_section');
    const deudasContainer = document.getElementById('deudas_container');
    const deudasLoading = document.getElementById('deudas_loading');
    const noDeudas = document.getElementById('no_deudas');
    const totalesSection = document.getElementById('totales_section');
    const totalPagar = document.getElementById('total_pagar');
    const montoInput = document.getElementById('monto_input');
    const btnSubmit = document.getElementById('btn_submit');

    let propiedadesData = [];

    // Cargar propiedades al cambiar comunidad
    comunidadSelect.addEventListener('change', function() {
        const comunidadId = this.value;
        
        // Reset
        propiedadSelect.innerHTML = '<option value="">Cargando propiedades...</option>';
        propiedadSelect.disabled = true;
        deudasSection.classList.add('d-none');
        totalesSection.classList.add('d-none');
        btnSubmit.disabled = true;
        
        if (!comunidadId) {
            propiedadSelect.innerHTML = '<option value="">Primero seleccione una comunidad...</option>';
            return;
        }

        propiedadLoading.classList.remove('d-none');

        // Llamada AJAX para obtener propiedades
        fetch(`propiedades.php?action=api-list&comunidad_id=${comunidadId}`)
            .then(response => response.json())
            .then(data => {
                propiedadLoading.classList.add('d-none');
                
                if (data.success && data.data.length > 0) {
                    propiedadesData = data.data;
                    let options = '<option value="">Seleccione una propiedad...</option>';
                    data.data.forEach(prop => {
                        options += `<option value="${prop.id}">${prop.nombre} - ${prop.nombre_dueno}</option>`;
                    });
                    propiedadSelect.innerHTML = options;
                    propiedadSelect.disabled = false;
                } else {
                    propiedadSelect.innerHTML = '<option value="">No hay propiedades en esta comunidad</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                propiedadLoading.classList.add('d-none');
                propiedadSelect.innerHTML = '<option value="">Error al cargar propiedades</option>';
            });
    });

    // Cargar deudas al cambiar propiedad
    propiedadSelect.addEventListener('change', function() {
        const propiedadId = this.value;
        
        // Reset
        deudasContainer.innerHTML = '';
        deudasSection.classList.add('d-none');
        totalesSection.classList.add('d-none');
        btnSubmit.disabled = true;
        
        if (!propiedadId) {
            return;
        }

        deudasSection.classList.remove('d-none');
        deudasLoading.classList.remove('d-none');
        noDeudas.classList.add('d-none');

        // Llamada AJAX para obtener deudas
        fetch(`pagos.php?action=api-deudas&propiedad_id=${propiedadId}`)
            .then(response => response.json())
            .then(data => {
                deudasLoading.classList.add('d-none');
                
                if (data.success && data.data.length > 0) {
                    let html = '<div class="list-group">';
                    data.data.forEach(deuda => {
                        const mesNombre = new Date(2000, deuda.mes - 1, 1).toLocaleString('es-ES', { month: 'long' });
                        html += `
                            <label class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input deuda-check" type="checkbox" 
                                           name="deudas[]" value="${deuda.id}" 
                                           data-monto="${deuda.monto}" onchange="actualizarTotal()">
                                    <span class="ms-2">
                                        <strong>${mesNombre.charAt(0).toUpperCase() + mesNombre.slice(1)} ${deuda.anio}</strong>
                                    </span>
                                </div>
                                <span class="badge bg-danger">$${parseFloat(deuda.monto).toLocaleString('es-CL')}</span>
                            </label>
                        `;
                    });
                    html += '</div>';
                    
                    deudasContainer.innerHTML = html;
                    totalesSection.classList.remove('d-none');
                    btnSubmit.disabled = false;
                } else {
                    noDeudas.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                deudasLoading.classList.add('d-none');
                deudasContainer.innerHTML = '<div class="alert alert-danger">Error al cargar deudas</div>';
            });
    });

    // Función global para actualizar el total
    window.actualizarTotal = function() {
        let total = 0;
        document.querySelectorAll('.deuda-check:checked').forEach(function(checkbox) {
            total += parseFloat(checkbox.dataset.monto);
        });
        
        totalPagar.textContent = '$' + total.toLocaleString('es-CL');
        montoInput.value = total;
        
        // Deshabilitar botón si no hay deudas seleccionadas
        btnSubmit.disabled = total === 0;
    };
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
