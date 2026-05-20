<?php
/**
 * Vista: Editar Pago a Colaborador
 */

$title = 'Editar Pago #' . $pago['id'];
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
            <form action="colaboradores.php?action=updatePago" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="id" value="<?= $pago['id'] ?>">

                <div class="mb-4">
                    <label class="form-label">Colaborador <span class="text-danger">*</span></label>
                    <select name="colaborador_id" class="form-select form-select-lg" required>
                        <option value="">Seleccione un colaborador...</option>
                        <?php foreach ($colaboradores as $col): ?>
                            <option value="<?= $col['id'] ?>" <?= $col['id'] == $pago['colaborador_id'] ? 'selected' : '' ?>>
                                <?= e($col['nombre']) ?> (<?= e($col['comuna'] ?? 'Sin comuna') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Detalle del Pago <span class="text-danger">*</span></label>
                    <textarea name="detalle" class="form-control" rows="3" required
                              placeholder="Ej: Pago por servicio de mantención de áreas verdes - Marzo 2025"><?= e($pago['detalle']) ?></textarea>
                    <div class="form-text">Describa el motivo o servicio por el que se realiza el pago</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Monto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" class="form-control" required 
                                   min="1" step="0.01"
                                   value="<?= $pago['monto'] ?>">
                        </div>
                        <div class="form-text">Ingrese el monto sin puntos ni comas</div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control form-control-lg" 
                               required value="<?= $pago['fecha'] ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-image me-2"></i>Boleta/Recibo del Pago
                    </label>
                    <?php if (!empty($pago['imagen_path'])): ?>
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-paperclip me-2"></i>
                            Ya existe una boleta adjunta. 
                            <a href="colaboradores.php?action=verImagen&id=<?= $pago['id'] ?>" target="_blank" class="alert-link">
                                Ver imagen actual
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="imagen" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,application/pdf">
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>
                        <?php if (!empty($pago['imagen_path'])): ?>
                            Si selecciona un nuevo archivo, reemplazará la imagen actual. 
                        <?php endif; ?>
                        Formatos permitidos: JPEG, PNG, GIF o PDF (Máx. 5MB)
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="colaboradores.php?action=pagos" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Actualizar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Información del Pago</h6>
            
            <table class="table table-borderless table-sm">
                <tr>
                    <td class="text-muted">ID:</td>
                    <td class="fw-bold">#<?= $pago['id'] ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Registrado por:</td>
                    <td><?= e($pago['pagado_por_nombre']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Fecha registro:</td>
                    <td><?= formatDate($pago['created_at'] ?? '', 'd/m/Y H:i') ?></td>
                </tr>
            </table>

            <hr class="my-3">
            
            <h6 class="mb-2">Datos actuales:</h6>
            <ul class="list-unstyled text-muted">
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Colaborador: <?= e($colaborador['nombre']) ?></li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Monto: <?= formatMoney((float)$pago['monto']) ?></li>
                <li class="mb-1"><i class="bi bi-check text-success me-2"></i>Fecha: <?= formatDate($pago['fecha']) ?></li>
            </ul>
        </div>

        <div class="form-section mt-4">
            <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Acciones</h6>
            <div class="d-grid gap-2">
                <a href="colaboradores.php?action=generarReciboPDF&id=<?= $pago['id'] ?>" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Descargar Recibo PDF
                </a>
                <?php if (!empty($pago['imagen_path'])): ?>
                    <a href="colaboradores.php?action=eliminarImagen&id=<?= $pago['id'] ?>" 
                       class="btn btn-outline-danger"
                       onclick="return confirm('¿Está seguro de eliminar la imagen adjunta?')">
                        <i class="bi bi-trash me-2"></i>Eliminar Imagen
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>