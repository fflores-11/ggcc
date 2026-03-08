<?php
/**
 * Vista: Listado de Saldos Mensuales - Control de Caja
 */

$title = 'Saldos Mensuales - Control de Caja';
require_once __DIR__ . '/../partials/header.php';

// Año y mes actuales por defecto
$anioActual = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mesActual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$comunidadIdSeleccionada = isset($_GET['comunidad_id']) ? (int)$_GET['comunidad_id'] : null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-cash-stack me-2"></i>Saldos Mensuales - Control de Caja</h4>
    <a href="saldos-mensuales.php?action=show" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Período
    </a>
</div>

<!-- Filtros -->
<div class="form-section mb-4">
    <form method="GET" action="saldos-mensuales.php" class="row align-items-end">
        <div class="col-md-3">
            <label class="form-label">Comunidad</label>
            <select name="comunidad_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($comunidades as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= ($comunidadIdSeleccionada == $com['id']) ? 'selected' : '' ?>>
                        <?= e($com['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= ($mesActual == $i) ? 'selected' : '' ?>>
                        <?= getMonthName($i) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Año</label>
            <select name="anio" class="form-select">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= ($anioActual == $y) ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-2"></i>Consultar
            </button>
        </div>
    </form>
</div>

<?php if (isset($comunidad) && isset($saldoConsultado)): ?>
<!-- Resultado de la consulta -->
<div class="form-section mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-calendar3 me-2"></i>
            <?= getMonthName($saldoConsultado['mes']) ?> <?= $saldoConsultado['anio'] ?> - 
            <span class="text-muted"><?= e($comunidad['nombre']) ?></span>
        </h5>
        <?php if ($saldoConsultado['cerrado']): ?>
            <span class="badge bg-success"><i class="bi bi-lock me-1"></i>Cerrado</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark"><i class="bi bi-unlock me-1"></i>Abierto</span>
        <?php endif; ?>
    </div>
    
    <!-- Desglose del cálculo -->
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-light border">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="text-muted small">Saldo Mes Anterior</div>
                        <div class="fs-4 fw-bold text-primary"><?= formatMoney($saldoConsultado['saldo_mes_anterior']) ?></div>
                    </div>
                    <div class="col-md-1">
                        <div class="fs-3">+</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Recibido GC</div>
                        <div class="fs-4 fw-bold text-success"><?= formatMoney($saldoConsultado['total_ingresos_gc'] + $saldoConsultado['ajustes_ingreso']) ?></div>
                    </div>
                    <div class="col-md-1">
                        <div class="fs-3">-</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Pago Colaboradores</div>
                        <div class="fs-4 fw-bold text-danger"><?= formatMoney($saldoConsultado['total_egresos_colaboradores'] + $saldoConsultado['ajustes_egreso']) ?></div>
                    </div>
                    <div class="col-md-1">
                        <div class="fs-3">=</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Saldo del Mes</div>
                        <div class="fs-2 fw-bold text-primary"><?= formatMoney($saldoConsultado['saldo_final']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botón para traer saldo del mes anterior -->
    <?php if (($saldoConsultado['saldo_mes_anterior'] ?? 0) == 0 && !$saldoConsultado['cerrado']): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>No hay saldo del mes anterior.</strong> 
            <?php if (isset($saldoMesAnterior) && $saldoMesAnterior > 0): ?>
                El mes anterior (<?= getMonthName($mesAnteriorConsulta) ?> <?= $anioAnteriorConsulta ?>) tiene un saldo de <?= formatMoney($saldoMesAnterior) ?>.<br>
                <form action="saldos-mensuales.php?action=traer-saldo-anterior" method="POST" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="saldo_id" value="<?= $saldoConsultado['id'] ?>">
                    <input type="hidden" name="monto" value="<?= $saldoMesAnterior ?>">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-left-circle me-2"></i>Traer Saldo de <?= getMonthName($mesAnteriorConsulta) ?> <?= $anioAnteriorConsulta ?>
                    </button>
                </form>
            <?php else: ?>
                No se encontró saldo cerrado del mes anterior.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Acciones -->
    <div class="d-flex gap-2">
        <a href="saldos-mensuales.php?action=show&comunidad_id=<?= $comunidad['id'] ?>&anio=<?= $saldoConsultado['anio'] ?>&mes=<?= $saldoConsultado['mes'] ?>" 
           class="btn btn-outline-primary">
            <i class="bi bi-eye me-2"></i>Ver Detalle
        </a>
        <?php if (!$saldoConsultado['cerrado']): ?>
            <button class="btn btn-success" onclick="alert('Use el botón desde la vista de detalle')">
                <i class="bi bi-plus-lg me-2"></i>Agregar Ingreso
            </button>
            <button class="btn btn-danger" onclick="alert('Use el botón desde la vista de detalle')">
                <i class="bi bi-plus-lg me-2"></i>Agregar Egreso
            </button>
        <?php endif; ?>
    </div>
</div>
<?php elseif (isset($comunidad)): ?>
<!-- No existe el período, ofrecer crearlo -->
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    No existe registro de caja para <strong><?= getMonthName($mesActual) ?> <?= $anioActual ?></strong> en <strong><?= e($comunidad['nombre']) ?></strong>.<br>
    <a href="saldos-mensuales.php?action=consultar&comunidad_id=<?= $comunidadIdSeleccionada ?>&anio=<?= $anioActual ?>&mes=<?= $mesActual ?>" 
       class="btn btn-primary mt-2">
        <i class="bi bi-plus-lg me-2"></i>Crear Registro de Caja
    </a>
</div>
<?php endif; ?>

<!-- Tabla de histórico -->
<div class="table-container mt-4">
    <div class="table-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Histórico de Caja</h5>
    </div>
    <div class="table-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Período</th>
                    <th>Comunidad</th>
                    <th class="text-end">Saldo Anterior</th>
                    <th class="text-end text-success">Ingresos</th>
                    <th class="text-end text-danger">Egresos</th>
                    <th class="text-end">Saldo Final</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($saldos)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-cash-stack display-4 d-block mb-3"></i>
                            No hay registros de caja mensual
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($saldos as $saldo): 
                        $periodoCerrado = $saldo['cerrado'] ?? false;
                        $diferencia = ($saldo['saldo_final'] ?? 0) - ($saldo['saldo_calculado'] ?? 0);
                    ?>
                        <tr class="<?= $periodoCerrado ? 'table-secondary' : '' ?>">
                            <td>
                                <strong><?= getMonthName($saldo['mes']) ?> <?= $saldo['anio'] ?></strong>
                            </td>
                            <td><?= e($saldo['comunidad_nombre'] ?? 'N/A') ?></td>
                            <td class="text-end"><?= formatMoney($saldo['saldo_mes_anterior'] ?? 0) ?></td>
                            <td class="text-end text-success"><?= formatMoney(($saldo['total_ingresos_gc'] ?? 0) + ($saldo['ajustes_ingreso'] ?? 0)) ?></td>
                            <td class="text-end text-danger"><?= formatMoney(($saldo['total_egresos_colaboradores'] ?? 0) + ($saldo['ajustes_egreso'] ?? 0)) ?></td>
                            <td class="text-end <?= $diferencia != 0 ? 'text-warning' : 'fw-bold' ?>">
                                <?= formatMoney($saldo['saldo_final'] ?? 0) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($periodoCerrado): ?>
                                    <span class="badge bg-success"><i class="bi bi-lock me-1"></i>Cerrado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-unlock me-1"></i>Abierto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="saldos-mensuales.php?action=show&comunidad_id=<?= $saldo['comunidad_id'] ?>&anio=<?= $saldo['anio'] ?>&mes=<?= $saldo['mes'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (!$periodoCerrado): ?>
                                        <form action="saldos-mensuales.php?action=eliminar" method="POST" 
                                              onsubmit="return confirm('¿Está seguro de eliminar este registro de caja de <?= getMonthName($saldo['mes']) ?> <?= $saldo['anio'] ?>? Esta acción no se puede deshacer.');"
                                              class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="saldo_id" value="<?= $saldo['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Registro">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
