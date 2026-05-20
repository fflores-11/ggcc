<?php
/**
 * Vista: Reporte de Morosidad - Versión Profesional para Imprimir
 */

$title = 'Reporte de Morosidad';
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    /* Estilos generales del reporte */
    .reporte-morosidad {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        padding: 0;
        overflow: hidden;
    }
    
    /* Encabezado del documento */
    .reporte-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 30px 35px;
        position: relative;
    }
    
    .reporte-header::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 0;
        right: 0;
        height: 20px;
        background: linear-gradient(to bottom right, transparent 49%, #fff 50%);
    }
    
    .reporte-header h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .reporte-header .subtitle {
        opacity: 0.9;
        margin-top: 5px;
        font-size: 0.95rem;
    }
    
    .reporte-header .fecha-generacion {
        position: absolute;
        top: 20px;
        right: 35px;
        font-size: 0.8rem;
        opacity: 0.8;
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
    }
    
    /* Resumen ejecutivo */
    .resumen-ejecutivo {
        padding: 30px 35px;
        background: #fff;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .resumen-cards {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .resumen-card {
        flex: 1;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .resumen-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    
    .resumen-card.total-morosos::before { background: #dc3545; }
    .resumen-card.total-morosos { background: #fff5f5; }
    
    .resumen-card.total-adeudado::before { background: #fd7e14; }
    .resumen-card.total-adeudado { background: #fff8f0; }
    
    .resumen-card.mes-referencia::before { background: #6c757d; }
    .resumen-card.mes-referencia { background: #f8f9fa; }
    
    .resumen-card .numero {
        font-size: 2.2rem;
        font-weight: 700;
        color: #333;
        margin: 10px 0;
    }
    
    .resumen-card .etiqueta {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Tabla profesional */
    .tabla-reporte {
        padding: 0 35px 35px;
    }
    
    .tabla-reporte table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .tabla-reporte thead th {
        background: #343a40;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        padding: 14px 16px;
        border: none;
    }
    
    .tabla-reporte thead th:first-child {
        border-radius: 8px 0 0 0;
    }
    
    .tabla-reporte thead th:last-child {
        border-radius: 0 8px 0 0;
    }
    
    .tabla-reporte tbody tr {
        transition: background 0.2s;
    }
    
    .tabla-reporte tbody tr:nth-child(even) {
        background: #fafbfc;
    }
    
    .tabla-reporte tbody tr:hover {
        background: #f0f4f8;
    }
    
    .tabla-reporte tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .tabla-reporte tbody tr:last-child td {
        border-bottom: 2px solid #343a40;
    }
    
    /* Badges de meses */
    .meses-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #dc3545;
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    /* Períodos */
    .periodos-lista {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .periodo-tag {
        background: #fff3cd;
        color: #856404;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        border: 1px solid #ffc107;
    }
    
    .periodo-tag.atrasado {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    
    /* Monto */
    .monto-adeudado {
        font-size: 1.1rem;
        font-weight: 700;
        color: #dc3545;
    }
    
    /* Info de propiedad */
    .propiedad-info .nombre {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }
    
    .propiedad-info .dueno {
        color: #666;
        font-size: 0.85rem;
        margin-top: 2px;
    }
    
    .contacto-info {
        font-size: 0.85rem;
        color: #666;
    }
    
    .contacto-info i {
        color: #adb5bd;
        margin-right: 4px;
    }
    
    /* Footer del reporte */
    .reporte-footer {
        padding: 20px 35px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .reporte-footer .logo-area {
        font-weight: 600;
        color: #495057;
    }
    
    /* Estado vacío */
    .estado-excelente {
        text-align: center;
        padding: 60px 35px;
    }
    
    .estado-excelente .icono {
        font-size: 4rem;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .estado-excelente h3 {
        color: #28a745;
        font-weight: 600;
    }
    
    /* Botones de acción */
    .acciones-reporte {
        padding: 20px 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e9ecef;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .resumen-cards {
            flex-direction: column;
        }
        .tabla-reporte {
            padding: 0 15px 15px;
            overflow-x: auto;
        }
        .reporte-header {
            padding: 20px;
        }
        .resumen-ejecutivo {
            padding: 20px;
        }
    }
    
    /* ESTILOS PARA IMPRESIÓN */
    @media print {
        body { background: white !important; }
        .sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .reporte-morosidad { box-shadow: none !important; }
        .acciones-reporte { display: none !important; }
        .reporte-footer { background: white !important; border-top: 2px solid #333 !important; }
        .tabla-reporte tbody tr:hover { background: inherit !important; }
        .btn { display: none !important; }
        .form-section { display: none !important; }
        .table-container .table-header { display: none !important; }
        .d-flex.justify-content-between.align-items-center.mb-4:first-child { display: none !important; }
        
        .tabla-reporte table { font-size: 10pt !important; }
        .tabla-reporte thead th { background: #333 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .meses-badge { background: #dc3545 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .monto-adeudado { color: #dc3545 !important; }
        .periodo-tag { background: #fff3cd !important; border: 1px solid #ffc107 !important; }
        .reporte-header { background: #dc3545 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .resumen-card { border: 1px solid #dee2e6 !important; }
        .resumen-card::before { display: none !important; }
        .resumen-card.total-morosos { border-top: 4px solid #dc3545 !important; }
        .resumen-card.total-adeudado { border-top: 4px solid #fd7e14 !important; }
        
        /* Forzar saltos de página elegantes */
        .tabla-reporte tbody tr { page-break-inside: avoid; }
        .resumen-ejecutivo { page-break-inside: avoid; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-file-earmark-text me-2"></i>Reporte de Morosidad</h4>
    <div>
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="bi bi-printer me-2"></i>Imprimir / PDF
        </button>
        <a href="reportes.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="reportes.php" class="row align-items-end">
        <input type="hidden" name="action" value="morosidad">
        
        <div class="col-md-4">
            <label class="form-label">Comunidad <span class="text-danger">*</span></label>
            <select name="comunidad_id" class="form-select" required onchange="this.form.submit()">
                <option value="">Seleccione...</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= ($comunidadId ?? 0) == $com['id'] ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?> (<?= e($com['comuna']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Mínimo de meses adeudados</label>
            <select name="minimo_meses" class="form-select" onchange="this.form.submit()">
                <option value="1" <?= ($minimoMeses ?? 1) == 1 ? 'selected' : '' ?>>1 mes o más</option>
                <option value="2" <?= ($minimoMeses ?? 1) == 2 ? 'selected' : '' ?>>2 meses o más</option>
                <option value="3" <?= ($minimoMeses ?? 1) == 3 ? 'selected' : '' ?>>3 meses o más</option>
                <option value="6" <?= ($minimoMeses ?? 1) == 6 ? 'selected' : '' ?>>6 meses o más</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-danger w-100">
                <i class="bi bi-search me-2"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<?php if ($comunidadId && !empty($morosos)): ?>

<div class="reporte-morosidad">
    <!-- Encabezado del documento -->
    <div class="reporte-header">
        <div class="fecha-generacion">
            <i class="bi bi-calendar3 me-1"></i> Generado: <?= date('d/m/Y H:i') ?>
        </div>
        <h4><i class="bi bi-exclamation-triangle-fill me-2"></i>REPORTE DE MOROSIDAD</h4>
        <div class="subtitle">
            <?= e($comunidad['nombre']) ?> — <?= e($comunidad['direccion']) ?>, <?= e($comunidad['comuna']) ?>
        </div>
    </div>
    
    <!-- Resumen Ejecutivo -->
    <div class="resumen-ejecutivo">
        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
            <i class="bi bi-clipboard-data me-2"></i>Resumen Ejecutivo
        </h6>
        <div class="resumen-cards">
            <div class="resumen-card total-morosos">
                <div class="etiqueta">Propiedades Morosas</div>
                <div class="numero"><?= count($morosos) ?></div>
                <small class="text-muted">Con <?= $minimoMeses ?> o más meses</small>
            </div>
            <div class="resumen-card total-adeudado">
                <div class="etiqueta">Total Adeudado</div>
                <div class="numero"><?= formatMoney(array_sum(array_column($morosos, 'total_adeudado'))) ?></div>
                <small class="text-muted">Deuda acumulada</small>
            </div>
            <div class="resumen-card mes-referencia">
                <div class="etiqueta">Período de Análisis</div>
                <div class="numero" style="font-size: 1.3rem;"><?= getMonthName(date('n')) ?> <?= date('Y') ?></div>
                <small class="text-muted">Mes de referencia</small>
            </div>
        </div>
    </div>
    
    <!-- Acciones -->
    <div class="acciones-reporte no-print">
        <div>
            <span class="badge bg-danger me-2"><?= count($morosos) ?> morosos</span>
            <span class="text-muted">Filtro: <?= $minimoMeses ?> o más meses de deuda</span>
        </div>
        <a href="correos.php?action=cobranza&comunidad_id=<?= $comunidadId ?>" class="btn btn-warning btn-sm">
            <i class="bi bi-envelope me-2"></i>Enviar Cobranzas Masivas
        </a>
    </div>
    
    <!-- Tabla de Morosos -->
    <div class="tabla-reporte">
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Propiedad / Dueño</th>
                    <th style="width: 20%;">Contacto</th>
                    <th class="text-center" style="width: 10%;">Meses</th>
                    <th style="width: 20%;">Períodos Adeudados</th>
                    <th class="text-end" style="width: 15%;">Monto</th>
                    <th class="text-center no-print" style="width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($morosos as $index => $moroso): ?>
                    <tr>
                        <td>
                            <div class="propiedad-info">
                                <div class="nombre"><?= e($moroso['propiedad_nombre']) ?></div>
                                <div class="dueno"><i class="bi bi-person me-1"></i><?= e($moroso['nombre_dueno']) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="contacto-info">
                                <?php if (!empty($moroso['email_dueno'])): ?>
                                    <div><i class="bi bi-envelope"></i> <?= e($moroso['email_dueno']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($moroso['whatsapp_dueno'])): ?>
                                    <div><i class="bi bi-whatsapp"></i> <?= e($moroso['whatsapp_dueno']) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="meses-badge"><?= $moroso['meses_adeudados'] ?></span>
                        </td>
                        <td>
                            <div class="periodos-lista">
                                <?php 
                                $periodos = explode(',', $moroso['periodos_adeudados']);
                                foreach ($periodos as $periodo): 
                                    $partes = explode('-', trim($periodo));
                                    if (count($partes) == 2):
                                        $nombreMes = getMonthName((int)$partes[0]);
                                ?>
                                    <span class="periodo-tag atrasado"><?= $nombreMes ?> <?= $partes[1] ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="monto-adeudado"><?= formatMoney((float)$moroso['total_adeudado']) ?></span>
                        </td>
                        <td class="text-center no-print">
                            <a href="propiedades.php?action=show&id=<?= $moroso['id'] ?>" 
                               class="btn btn-sm btn-outline-primary" title="Ver Propiedad">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Footer del documento -->
    <div class="reporte-footer">
        <div class="logo-area">
            <i class="bi bi-building me-2"></i><?= APP_NAME ?>
        </div>
        <div>
            <span class="me-3">Página 1 de 1</span>
            <span>Generado por: <?= e($_SESSION['user_name'] ?? 'Sistema') ?></span>
        </div>
    </div>
</div>

<?php elseif ($comunidadId): ?>
    <div class="reporte-morosidad">
        <div class="estado-excelente">
            <div class="icono"><i class="bi bi-check-circle-fill"></i></div>
            <h3>¡Excelente!</h3>
            <p class="text-muted mb-4">No se encontraron propiedades morosas en <strong><?= e($comunidad['nombre']) ?></strong><br>
            (con <?= $minimoMeses ?> o más meses de deuda).</p>
            <div class="alert alert-success d-inline-block">
                <i class="bi bi-trophy me-2"></i>¡Esta comunidad está al día con sus pagos!
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Seleccione una comunidad para ver el reporte de morosidad.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
