<?php
/**
 * Vista Dashboard
 * Página principal con métricas y estadísticas
 */

$title = 'Dashboard';
require_once __DIR__ . '/../partials/header.php';
?>

<!-- Métricas Principales -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">Resumen General</h4>
    </div>
    
    <!-- Card 1: Total Comunidades -->
    <div class="col-md-3 mb-4">
        <div class="stat-card primary">
            <div class="icon"><i class="bi bi-building"></i></div>
            <div class="number"><?= $metricas['total_comunidades'] ?></div>
            <div class="label">Comunidades Activas</div>
    </div>
</div>

<!-- Resumen Financiero - Mes Actual -->
<?php if (isset($resumenFinanciero) && !empty($resumenFinanciero)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Resumen Financiero - <?= getMonthName(date('n')) ?> <?= date('Y') ?></h4>
            <a href="saldos-mensuales.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i>Ver Detalle
            </a>
        </div>
    </div>
    
    <!-- Total de Propiedades -->
    <div class="col-md-2 mb-4">
        <div class="stat-card primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="text-center mb-2">
                <?php 
                $totalProp = $metricas['total_propiedades'] ?? 0;
                $totalComunidades = $resumenFinanciero['total_comunidades'] ?? 1;
                $pctProp = $totalComunidades > 0 ? round(($totalProp / $totalComunidades) * 100, 1) : 0;
                ?>
                <span class="badge bg-light text-dark"><?= $pctProp ?>%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="bi bi-house-door"></i></div>
            <div class="number"><?= number_format($totalProp) ?></div>
            <div class="label" style="color: rgba(255,255,255,0.9);">Total Propiedades</div>
        </div>
    </div>
    
    <!-- Total Deudas -->
    <div class="col-md-2 mb-4">
        <div class="stat-card warning" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="text-center mb-2">
                <?php 
                $totalDeuda = $metricas['total_deuda'] ?? 0;
                $totalRecaudado = $resumenFinanciero['total_ingresos'] ?? 0;
                $totalDisponible = $totalRecaudado + $totalDeuda;
                $pctDeuda = $totalDisponible > 0 ? round(($totalDeuda / $totalDisponible) * 100, 1) : 0;
                ?>
                <span class="badge bg-light text-danger"><?= $pctDeuda ?>%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="number"><?= formatMoney($totalDeuda) ?></div>
            <div class="label" style="color: rgba(255,255,255,0.9);">Total Deudas</div>
        </div>
    </div>
    
    <!-- Total Recaudado -->
    <div class="col-md-2 mb-4">
        <div class="stat-card success" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <div class="text-center mb-2">
                <span class="badge bg-light text-success">100%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="bi bi-cash-coin"></i></div>
            <div class="number"><?= formatMoney($totalRecaudado) ?></div>
            <div class="label" style="color: rgba(255,255,255,0.9);">Total Recaudado</div>
        </div>
    </div>
    
    <!-- Saldo Actual -->
    <div class="col-md-2 mb-4">
        <div class="stat-card info" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="text-center mb-2">
                <?php 
                $saldoActual = $resumenFinanciero['saldo_actual'] ?? 0;
                $pctSaldo = $totalRecaudado > 0 ? round(($saldoActual / $totalRecaudado) * 100, 1) : 0;
                ?>
                <span class="badge bg-light text-info"><?= $pctSaldo ?>%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="bi bi-wallet2"></i></div>
            <div class="number"><?= formatMoney($saldoActual) ?></div>
            <div class="label" style="color: rgba(255,255,255,0.9);">Saldo Actual</div>
        </div>
    </div>
    
    <!-- Egresos Mes -->
    <div class="col-md-2 mb-4">
        <div class="stat-card danger" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
            <div class="text-center mb-2">
                <?php 
                $egresosMes = $resumenFinanciero['total_egresos'] ?? 0;
                $pctEgresos = $totalRecaudado > 0 ? round(($egresosMes / $totalRecaudado) * 100, 1) : 0;
                ?>
                <span class="badge bg-light text-warning"><?= $pctEgresos ?>%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="number"><?= formatMoney($egresosMes) ?></div>
            <div class="label" style="color: rgba(255,255,255,0.9);">Egresos Mes</div>
        </div>
    </div>
    
    <!-- Comunidades -->
    <div class="col-md-2 mb-4">
        <div class="stat-card secondary" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
            <div class="text-center mb-2">
                <span class="badge bg-light text-dark">100%</span>
            </div>
            <div class="icon" style="background: rgba(255,255,255,0.5); color: #333;"><i class="bi bi-building"></i></div>
            <div class="number"><?= $totalComunidades ?></div>
            <div class="label" style="color: #555;">Comunidades</div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Accesos Rápidos -->
    <div class="col-md-4 mb-4">
        <div class="form-section">
            <div class="section-title">Accesos Rápidos</div>
            <div class="list-group">
                <a href="comunidades.php?action=create" class="list-group-item list-group-item-action">
                    <i class="bi bi-plus-circle text-primary me-2"></i> Nueva Comunidad
                </a>
                <a href="propiedades.php?action=create" class="list-group-item list-group-item-action">
                    <i class="bi bi-plus-circle text-success me-2"></i> Nueva Propiedad
                </a>
                <a href="pagos.php?action=create" class="list-group-item list-group-item-action">
                    <i class="bi bi-plus-circle text-warning me-2"></i> Registrar Pago
                </a>
                <a href="pagos.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-cash-coin text-info me-2"></i> Generar Deudas
                </a>
                <a href="correos.php?action=cobranza" class="list-group-item list-group-item-action">
                    <i class="bi bi-envelope-exclamation text-danger me-2"></i> Enviar Cobranzas
                </a>
                <a href="consolidados.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-grid-3x3 text-secondary me-2"></i> Ver Consolidados
                </a>
            </div>
        </div>
        
        <!-- Propiedades Morosas -->
        <div class="form-section mt-4">
            <div class="section-title d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Propiedades Morosas</span>
                <a href="reportes.php?tipo=morosidad" class="btn btn-sm btn-outline-warning">Ver Todo</a>
            </div>
            <?php if (empty($propiedadesMorosas)): ?>
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle display-6 text-success"></i>
                    <p class="mb-0 mt-2">No hay propiedades morosas</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($propiedadesMorosas, 0, 5) as $morosa): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= e($morosa['propiedad_nombre']) ?></strong><br>
                                    <small class="text-muted"><?= e($morosa['comunidad_nombre']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger"><?= $morosa['meses_adeudados'] ?> meses</span><br>
                                    <small class="text-danger"><?= formatMoney((float)$morosa['total_adeudado']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Gráfico y Actividades -->
    <div class="col-md-8 mb-4">
        <!-- Gráfico de Tendencias -->
        <div class="form-section mb-4">
            <div class="section-title">Tendencias de Pagos (Últimos 6 meses)</div>
            <canvas id="tendenciasChart" height="150"></canvas>
        </div>

        <!-- Últimas Actividades -->
        <div class="table-container">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Últimas Actividades</h5>
                <a href="pagos.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="table-body">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Comunidad</th>
                            <th>Propiedad</th>
                            <th>Meses</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimasActividades)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay actividades recientes
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimasActividades as $actividad): ?>
                                <tr>
                                    <td><?= formatDate($actividad['fecha']) ?></td>
                                    <td><?= e($actividad['comunidad_nombre']) ?></td>
                                    <td>
                                        <?= e($actividad['propiedad_nombre']) ?><br>
                                        <small class="text-muted"><?= e($actividad['nombre_dueno']) ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $actividad['meses_pagados'] ? str_replace('-', '/', $actividad['meses_pagados']) : 'N/A' ?>
                                        </small>
                                    </td>
                                    <td class="text-end fw-bold"><?= formatMoney((float)$actividad['monto']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Comunidades con Mayor Deuda -->
    <div class="col-md-6 mb-4">
        <div class="table-container">
            <div class="table-header">
                <h5 class="mb-0">Comunidades con Mayor Deuda</h5>
            </div>
            <div class="table-body">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Comunidad</th>
                            <th class="text-center">Propiedades</th>
                            <th class="text-end">Deuda Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comunidadesDeuda)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No hay deudas pendientes
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($comunidadesDeuda as $comunidad): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($comunidad['nombre']) ?></strong><br>
                                        <small class="text-muted"><?= e($comunidad['comuna']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $comunidad['total_propiedades'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger fw-bold"><?= formatMoney((float)$comunidad['total_deuda']) ?></span><br>
                                        <small class="text-muted"><?= $comunidad['deudas_pendientes'] ?> cuotas</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Resumen por Comunidad -->
    <div class="col-md-6 mb-4">
        <div class="table-container">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resumen por Comunidad</h5>
                <span class="badge bg-info"><?= count($resumenComunidades) ?> activas</span>
            </div>
            <div class="table-body" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Comunidad</th>
                            <th class="text-end">% Cobranza</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resumenComunidades)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">
                                    No hay comunidades registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($resumenComunidades, 0, 10) as $resumen): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($resumen['nombre']) ?></strong><br>
                                        <small class="text-muted">
                                            <?= $resumen['total_propiedades'] ?> propiedades | 
                                            <?= formatMoney((float)$resumen['total_pagado']) ?> recaudado
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <?php 
                                        $porcentaje = (float)($resumen['porcentaje_cobranza'] ?? 0);
                                        $badgeClass = $porcentaje >= 80 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?> fs-6"><?= number_format($porcentaje, 1) ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script para el gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('tendenciasChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labelsGrafico) ?>,
            datasets: [
                {
                    label: 'Pagos Recibidos',
                    data: <?= json_encode($datosPagos) ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Deudas Generadas',
                    data: <?= json_encode($datosDeudas) ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-CL');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw.toLocaleString('es-CL');
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
