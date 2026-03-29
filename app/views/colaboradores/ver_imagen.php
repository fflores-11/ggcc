<?php
/**
 * Vista: Ver Boleta/Recibo del Pago
 */

$title = 'Boleta/Recibo del Pago #' . $pago['id'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-image me-2"></i><?= $title ?></h4>
    <div class="d-flex gap-2">
        <a href="colaboradores.php?action=pagos" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Pagos
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="form-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Colaborador</h6>
                    <p class="fs-5 fw-bold"><?= e($pago['colaborador_nombre']) ?></p>
                    <p class="text-muted"><?= e($pago['colaborador_email'] ?? 'Sin email') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted mb-2">Monto</h6>
                    <p class="fs-3 fw-bold text-primary"><?= formatMoney((float)$pago['monto']) ?></p>
                    <p class="text-muted"><?= formatDate($pago['fecha']) ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <h6 class="text-muted mb-2">Detalle</h6>
                <p class="p-3 bg-light rounded"><?= e($pago['detalle']) ?></p>
            </div>

            <div class="mb-4">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-image me-2"></i>Boleta/Recibo
                </h6>
                
                <?php if (!empty($pago['imagen_path'])): ?>
                    <?php 
                    $extension = pathinfo($pago['imagen_path'], PATHINFO_EXTENSION);
                    $isPdf = strtolower($extension) === 'pdf';
                    ?>
                    
                    <?php if ($isPdf): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-file-pdf me-2"></i>
                            El documento es un PDF. 
                            <a href="<?= BASE_URL . $pago['imagen_path'] ?>" target="_blank" class="alert-link">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir en nueva pestaña
                            </a>
                        </div>
                        <div class="ratio ratio-16x9">
                            <embed src="<?= BASE_URL . $pago['imagen_path'] ?>" type="application/pdf" class="rounded border">
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="<?= BASE_URL . $pago['imagen_path'] ?>" target="_blank" title="Ver imagen completa">
                                <img src="<?= BASE_URL . $pago['imagen_path'] ?>" 
                                     alt="Boleta/Recibo" 
                                     class="img-fluid rounded border shadow-sm"
                                     style="max-height: 600px;">
                            </a>
                            <p class="text-muted mt-2 small">
                                <i class="bi bi-zoom-in me-1"></i>Haga clic en la imagen para ver en tamaño completo
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay boleta/recibo adjunto a este pago.
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <div class="text-muted small">
                    <i class="bi bi-person me-1"></i>
                    Registrado por: <?= e($pago['pagado_por_nombre']) ?>
                </div>
                <div class="d-flex gap-2">
                    <?php if (!empty($pago['imagen_path'])): ?>
                        <a href="<?= BASE_URL . $pago['imagen_path'] ?>" download 
                           class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i>Descargar
                        </a>
                        <a href="colaboradores.php?action=eliminarImagen&id=<?= $pago['id'] ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('¿Está seguro de eliminar esta imagen?')">
                            <i class="bi bi-trash me-2"></i>Eliminar Imagen
                        </a>
                    <?php endif; ?>
                    <a href="colaboradores.php?action=pagos" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>