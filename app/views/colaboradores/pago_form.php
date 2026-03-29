<?php
/**
 * Vista: Formulario de Pago a Colaborador
 */

$title = 'Registrar Pago a Colaborador';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= $title ?></h4>
    <a href="colaboradores.php?action=pagos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver a Pagos
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <form action="colaboradores.php?action=storePago" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="mb-4">
                    <label class="form-label">Colaborador <span class="text-danger">*</span></label>
                    <?php if (isset($colaborador) && $colaborador): ?>
                        <input type="hidden" name="colaborador_id" value="<?= $colaborador['id'] ?>">
                        <input type="text" class="form-control form-control-lg" value="<?= e($colaborador['nombre']) ?>" disabled>
                        <div class="form-text">
                            <?= e($colaborador['email']) ?> | <?= e($colaborador['comuna']) ?>
                        </div>
                    <?php else: ?>
                        <select name="colaborador_id" class="form-select form-select-lg" required>
                            <option value="">Seleccione un colaborador...</option>
                            <?php foreach ($colaboradores as $col): ?>
                                <option value="<?= $col['id'] ?>">
                                    <?= e($col['nombre']) ?> (<?= e($col['comuna']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label">Detalle del Pago <span class="text-danger">*</span></label>
                    <textarea name="detalle" class="form-control" rows="3" required
                              placeholder="Ej: Pago por servicio de mantención de áreas verdes - Marzo 2025"></textarea>
                    <div class="form-text">Describa el motivo o servicio por el que se realiza el pago</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Monto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" class="form-control" required 
                                   min="1" step="0.01"
                                   placeholder="Ej: 25000">
                        </div>
                        <div class="form-text">Ingrese el monto sin puntos ni comas</div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control form-control-lg" 
                               required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-image me-2"></i>Boleta/Recibo del Pago
                    </label>
                    <input type="file" name="imagen" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,application/pdf">
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>
                        Adjunte una imagen o PDF de la boleta/recibo (JPEG, PNG, GIF o PDF - Máx. 5MB)
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Importante:</strong> Este pago se registrará como egreso del sistema. 
                    Asegúrese de que hay fondos suficientes disponibles.
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="colaboradores.php?action=pagos" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <p class="text-muted mb-3">
                Registre aquí los pagos realizados a colaboradores o proveedores por servicios prestados a las comunidades.
            </p>
            <h6 class="mb-2">Campos obligatorios:</h6>
            <ul class="list-unstyled text-muted">
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Colaborador</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Detalle del pago</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Monto mayor a 0</li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Fecha</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
