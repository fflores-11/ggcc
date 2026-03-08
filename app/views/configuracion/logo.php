<?php
/**
 * Vista: Cambiar Logo del Sistema (Dual: Claro y Oscuro)
 * Soporta modo claro y modo oscuro automático
 */

$title = 'Cambiar Logo del Sistema';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-image me-2"></i>Cambiar Logo del Sistema</h4>
    <a href="configuracion.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver a Configuración
    </a>
</div>

<!-- Información sobre modo dual -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Modo Dual:</strong> El sistema detecta automáticamente si el usuario tiene activado el modo oscuro en su sistema operativo y muestra el logo correspondiente.
    <ul class="mb-0 mt-2">
        <li><strong>Logo Claro:</strong> Se muestra en fondos claros (modo normal)</li>
        <li><strong>Logo Oscuro:</strong> Se muestra en fondos oscuros (modo noche)</li>
    </ul>
</div>

<div class="row">
    <!-- Logo para Modo Claro -->
    <div class="col-lg-6 mb-4">
        <div class="form-section h-100">
            <h6 class="section-title">
                <i class="bi bi-sun me-2 text-warning"></i>
                Logo Modo Claro
                <span class="badge bg-light text-dark ms-2">Por defecto</span>
            </h6>
            
            <!-- Vista previa -->
            <div class="bg-light p-3 rounded mb-3 text-center" style="min-height: 150px;">
                <?php if ($logoExists && $logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo Claro" class="img-fluid" 
                         style="max-height: 120px; max-width: 100%;">
                <?php else: ?>
                    <div class="display-1 text-muted">
                        <i class="bi bi-building"></i>
                    </div>
                    <p class="text-muted">Logo por defecto</p>
                <?php endif; ?>
            </div>
            
            <?php if ($logoExists && $logoUrl): ?>
                <p class="text-muted small text-center mb-3">
                    <code><?= basename($logoUrl) ?></code>
                </p>
            <?php endif; ?>
            
            <!-- Formulario subir logo claro -->
            <form action="configuracion.php?action=subir-logo" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="tipo_logo" value="light">
                
                <div class="mb-3">
                    <label class="form-label">Subir Logo para Modo Claro</label>
                    <input type="file" name="logo" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <div class="form-text">
                        Use colores oscuros para que se vean bien sobre fondo blanco/claro
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cloud-upload me-2"></i>Subir Logo Claro
                    </button>
                </div>
            </form>
            
            <?php if ($logoExists): ?>
                <form action="configuracion.php?action=eliminar-logo" method="POST" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="tipo_logo" value="light">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            onclick="return confirm('¿Eliminar logo claro?')">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Logo para Modo Oscuro -->
    <div class="col-lg-6 mb-4">
        <div class="form-section h-100">
            <h6 class="section-title">
                <i class="bi bi-moon me-2 text-primary"></i>
                Logo Modo Oscuro
                <span class="badge bg-dark ms-2">Opcional</span>
            </h6>
            
            <!-- Vista previa -->
            <div class="bg-dark p-3 rounded mb-3 text-center" style="min-height: 150px;">
                <?php if ($logoDarkExists && $logoDarkUrl): ?>
                    <img src="<?= $logoDarkUrl ?>" alt="Logo Oscuro" class="img-fluid" 
                         style="max-height: 120px; max-width: 100%;">
                <?php else: ?>
                    <?php if ($logoExists && $logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="Logo" class="img-fluid opacity-50" 
                             style="max-height: 120px; max-width: 100%;">
                        <p class="text-light small mt-2">Usando logo claro (se ve atenuado)</p>
                    <?php else: ?>
                        <div class="display-1 text-secondary">
                            <i class="bi bi-building"></i>
                        </div>
                        <p class="text-secondary">Sin logo oscuro configurado</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($logoDarkExists && $logoDarkUrl): ?>
                <p class="text-muted small text-center mb-3">
                    <code><?= basename($logoDarkUrl) ?></code>
                </p>
            <?php endif; ?>
            
            <!-- Formulario subir logo oscuro -->
            <form action="configuracion.php?action=subir-logo" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="tipo_logo" value="dark">
                
                <div class="mb-3">
                    <label class="form-label">Subir Logo para Modo Oscuro</label>
                    <input type="file" name="logo_dark" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <div class="form-text">
                        Use colores claros/blancos para que se vean bien sobre fondo oscuro
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cloud-upload me-2"></i>Subir Logo Oscuro
                    </button>
                </div>
            </form>
            
            <?php if ($logoDarkExists): ?>
                <form action="configuracion.php?action=eliminar-logo" method="POST" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="tipo_logo" value="dark">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            onclick="return confirm('¿Eliminar logo oscuro?')">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Vista Previa en Ambos Modos -->
<div class="form-section mb-4">
    <h6 class="section-title"><i class="bi bi-display me-2"></i>Vista Previa - Detección Automática</h6>
    <div class="row">
        <div class="col-md-6">
            <div class="p-4 bg-light rounded text-center mb-3">
                <small class="text-muted d-block mb-2">Vista en Modo Claro</small>
                <picture>
                    <?php if ($logoDarkExists): ?>
                        <source srcset="<?= $logoDarkUrl ?>" media="(prefers-color-scheme: dark)">
                    <?php endif; ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" style="max-height: 100px; max-width: 100%;">
                </picture>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-4 bg-dark rounded text-center mb-3">
                <small class="text-light d-block mb-2">Vista en Modo Oscuro</small>
                <?php if ($logoDarkExists): ?>
                    <img src="<?= $logoDarkUrl ?>" alt="Logo Oscuro" style="max-height: 100px; max-width: 100%;">
                <?php else: ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" class="opacity-50" style="max-height: 100px; max-width: 100%;">
                    <p class="text-light small mt-2">No hay logo oscuro configurado</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="alert alert-warning mt-3">
        <i class="bi bi-info-circle me-2"></i>
        El navegador detecta automáticamente el modo de color preferido del sistema operativo (claro/oscuro) y muestra el logo correspondiente.
    </div>
</div>

<!-- Información adicional -->
<div class="row">
    <div class="col-lg-6">
        <div class="form-section">
            <h6 class="section-title"><i class="bi bi-lightbulb me-2"></i>Consejos para Modo Claro</h6>
            <ul class="text-muted mb-0">
                <li>✓ Use colores oscuros o negros</li>
                <li>✓ Evite fondos transparentes si el logo es claro</li>
                <li>✓ Asegúrese de buen contraste sobre fondo blanco</li>
                <li>✓ Formatos recomendados: PNG con fondo sólido</li>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-section">
            <h6 class="section-title"><i class="bi bi-moon-stars me-2"></i>Consejos para Modo Oscuro</h6>
            <ul class="text-muted mb-0">
                <li>✓ Use colores claros o blancos</li>
                <li>✓ Fondo transparente funciona muy bien</li>
                <li>✓ Asegúrese de buen contraste sobre fondo oscuro</li>
                <li>✓ Evite colores muy oscuros que se pierdan</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
