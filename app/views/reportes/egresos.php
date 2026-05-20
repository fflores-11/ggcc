<?php
/**
 * Vista: Reporte de Egresos (Pagos a Colaboradores)
 * Formato similar a libro de caja
 */

$title = 'Reporte de Egresos';
require_once __DIR__ . '/../partials/header.php';

// Agrupar egresos por fecha
$egresosPorFecha = [];
foreach ($egresos as $egreso) {
    $fecha = $egreso['fecha'];
    if (!isset($egresosPorFecha[$fecha])) {
        $egresosPorFecha[$fecha] = [];
    }
    $egresosPorFecha[$fecha][] = $egreso;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-file-earmark-text me-2"></i>DETALLE DE PAGOS EFECTUADOS</h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
        <a href="reportes.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="form-section mb-4 no-print">
    <form method="GET" action="reportes.php" class="row align-items-end">
        <input type="hidden" name="action" value="egresos">
        <div class="col-md-3">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= (isset($_GET['mes']) && $_GET['mes'] == $i) || (!isset($_GET['mes']) && date('n') == $i) ? 'selected' : '' ?>>
                        <?= getMonthName($i) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Año</label>
            <select name="anio" class="form-select">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= (isset($_GET['anio']) && $_GET['anio'] == $y) || (!isset($_GET['anio']) && date('Y') == $y) ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-2"></i>Generar
            </button>
        </div>
    </form>
</div>

<!-- Reporte tipo libro de caja -->
<div class="reporte-libro-caja">
    <!-- Título del período -->
    <div class="text-center mb-4">
        <h5 class="text-uppercase fw-bold">
            EGRESOS <?= strtoupper(getMonthName(isset($_GET['mes']) ? $_GET['mes'] : date('n'))) ?> 
            <?= isset($_GET['anio']) ? $_GET['anio'] : date('Y') ?>
        </h5>
    </div>

    <?php if (empty($egresos)): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            No hay pagos a colaboradores registrados en este período.
        </div>
    <?php else: ?>
        <!-- Tabla de egresos -->
        <table class="table table-borderless">
            <tbody>
                <?php 
                $totalEgresos = 0;
                foreach ($egresosPorFecha as $fecha => $pagosDia): 
                    $dia = date('j', strtotime($fecha));
                    $first = true;
                    foreach ($pagosDia as $pago):
                        $totalEgresos += $pago['monto'];
                ?>
                    <tr>
                        <?php if ($first): ?>
                            <td width="5%" class="fw-bold align-top"><?= $dia ?></td>
                        <?php else: ?>
                            <td width="5%"></td>
                        <?php endif; ?>
                        <td width="65%">
                            <?= strtoupper(e($pago['detalle'])) ?>
                            <?php if ($pago['colaborador_nombre']): ?>
                                <br><small class="text-muted">
                                    <?php if ($pago['tipo_colaborador'] == 'empresa'): ?>
                                        Empresa: <?= e($pago['colaborador_nombre']) ?> 
                                        (N° Cliente: <?= e($pago['numero_cliente']) ?>)
                                    <?php else: ?>
                                        Colaborador: <?= e($pago['colaborador_nombre']) ?>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td width="30%" class="text-end fw-bold">
                            $ <?= number_format($pago['monto'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php 
                        $first = false;
                    endforeach;
                endforeach; 
                ?>
                
                <!-- Línea separadora antes del total -->
                <tr>
                    <td colspan="3"><hr class="border-2 border-dark"></td>
                </tr>
                
                <!-- Total de egresos -->
                <tr class="table-active">
                    <td colspan="2" class="fw-bold text-uppercase">
                        TOTAL EGRESOS <?= strtoupper(getMonthName(isset($_GET['mes']) ? $_GET['mes'] : date('n'))) ?> 
                        <?= isset($_GET['anio']) ? $_GET['anio'] : date('Y') ?>
                    </td>
                    <td class="text-end fw-bold fs-5">
                        $ <?= number_format($totalEgresos, 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Saldo / Cuadre -->
        <div class="mt-5 pt-3 border-top">
            <table class="table table-borderless">
                <tr>
                    <td width="70%" class="fw-bold text-uppercase">
                        PAGO GASTOS COMUNES (Ingresos)
                    </td>
                    <td width="30%" class="text-end fw-bold">
                        $ <?= number_format($totalIngresosGC, 0, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold text-uppercase">
                        SALDO MES ANTERIOR
                    </td>
                    <td class="text-end fw-bold">
                        $ <?= number_format($saldoMesAnterior, 0, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold text-uppercase text-danger">
                        (-) PAGO COLABORADORES (Egresos)
                    </td>
                    <td class="text-end fw-bold text-danger">
                        $ <?= number_format($totalEgresos, 0, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr class="border-2 border-dark"></td>
                </tr>
                <tr class="table-active">
                    <td class="fw-bold fs-5 text-uppercase">
                        SALDO FINAL
                    </td>
                    <td class="text-end fw-bold fs-5">
                        $ <?= number_format($saldo, 0, ',', '.') ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Firmas -->
        <div class="mt-5 pt-5 no-print">
            <div class="row">
                <div class="col-md-6 text-center">
                    <div style="border-top: 1px solid #000; width: 70%; margin: 0 auto;"></div>
                    <p class="mt-2">Elaborado por</p>
                </div>
                <div class="col-md-6 text-center">
                    <div style="border-top: 1px solid #000; width: 70%; margin: 0 auto;"></div>
                    <p class="mt-2">Revisado por</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .reporte-libro-caja {
        font-family: 'Courier New', monospace;
        font-size: 12pt;
    }
    
    .table {
        width: 100%;
    }
    
    .table td {
        padding: 4px 8px;
    }
    
    h5 {
        font-size: 14pt;
        font-weight: bold;
    }
    
    .fw-bold {
        font-weight: bold !important;
    }
    
    .text-uppercase {
        text-transform: uppercase;
    }
}

.reporte-libro-caja {
    max-width: 800px;
    margin: 0 auto;
}

.reporte-libro-caja .table td {
    vertical-align: top;
}

.reporte-libro-caja hr {
    margin: 10px 0;
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
