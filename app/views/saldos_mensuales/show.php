<?php
/**
 * Vista: Detalle de Control de Caja Mensual
 */

$title = "Control de Caja - {$comunidad['nombre']} - " . getMonthName($mes) . " $anio";
require_once __DIR__ . '/../partials/header.php';

// Calcular totales
$totalIngresosGC = $saldo['total_ingresos_gc'] ?? 0;
$totalIngresosAjustes = $saldo['ajustes_ingreso'] ?? 0;
$totalIngresos = $totalIngresosGC + $totalIngresosAjustes;

$totalEgresosCol = $saldo['total_egresos_colaboradores'] ?? 0;
$totalEgresosAjustes = $saldo['ajustes_egreso'] ?? 0;
$totalEgresos = $totalEgresosCol + $totalEgresosAjustes;

$saldoAnterior = $saldo['saldo_mes_anterior'] ?? 0;
$saldoCalculado = $saldo['saldo_calculado'] ?? 0;
$saldoFinal = $saldo['saldo_final'] ?? 0;
$diferencia = $saldoFinal - $saldoCalculado;

$periodoCerrado = $saldo['cerrado'] ?? false;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>
        <i class="bi bi-cash-stack me-2"></i>
        Control de Caja: <?= getMonthName($mes) ?> <?= $anio ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="saldos-mensuales.php?action=recalcular&id=<?= $saldo['id'] ?>" 
           class="btn btn-outline-info" 
           onclick="return confirm('¿Desea recalcular los totales automáticos?')">
            <i class="bi bi-calculator me-2"></i>Recalcular
        </a>
        <a href="saldos-mensuales.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<!-- Info Comunidad -->
<div class="alert alert-info mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <i class="bi bi-building me-2"></i>
            <strong><?= e($comunidad['nombre']) ?></strong>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if ($periodoCerrado): ?>
                <span class="badge bg-success fs-6"><i class="bi bi-lock me-2"></i>Período Cerrado</span>
                <small class="text-muted d-block mt-1">
                    Cerrado el <?= formatDate($saldo['fecha_cierre']) ?>
                </small>
            <?php else: ?>
                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-unlock me-2"></i>Período Abierto</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Columna Izquierda: Detalle de Caja -->
    <div class="col-lg-8">
        <!-- Saldo del Mes Anterior -->
        <div class="form-section mb-4">
            <h6 class="section-title"><i class="bi bi-arrow-left-circle me-2"></i>Saldo del Mes Anterior</h6>
            <div class="display-6 fw-bold text-primary"><?= formatMoney($saldoAnterior) ?></div>
            <small class="text-muted">Este monto viene del cierre del mes anterior</small>
        </div>

        <!-- Ingresos -->
        <div class="form-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title mb-0 text-success">
                    <i class="bi bi-arrow-down-circle me-2"></i>Ingresos del Mes
                </h6>
                <?php if (!$periodoCerrado): ?>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarIngreso">
                    <i class="bi bi-plus-lg me-1"></i>Agregar Ingreso
                </button>
                <?php endif; ?>
            </div>
            
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted">Pagos de Gastos Comunes:</td>
                    <td class="text-end fw-bold"><?= formatMoney($totalIngresosGC) ?></td>
                </tr>
                <?php if ($totalIngresosAjustes > 0): ?>
                <tr>
                    <td class="text-muted">Ajustes Manuales:</td>
                    <td class="text-end fw-bold text-success">+ <?= formatMoney($totalIngresosAjustes) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="border-top">
                    <td class="fw-bold">Total Ingresos:</td>
                    <td class="text-end fw-bold fs-5 text-success"><?= formatMoney($totalIngresos) ?></td>
                </tr>
            </table>
        </div>

        <!-- Egresos -->
        <div class="form-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title mb-0 text-danger">
                    <i class="bi bi-arrow-up-circle me-2"></i>Egresos del Mes
                </h6>
                <?php if (!$periodoCerrado): ?>
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso">
                    <i class="bi bi-plus-lg me-1"></i>Agregar Egreso
                </button>
                <?php endif; ?>
            </div>
            
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted">Pagos a Colaboradores:</td>
                    <td class="text-end fw-bold"><?= formatMoney($totalEgresosCol) ?></td>
                </tr>
                <?php if ($totalEgresosAjustes > 0): ?>
                <tr>
                    <td class="text-muted">Ajustes Manuales:</td>
                    <td class="text-end fw-bold text-danger">+ <?= formatMoney($totalEgresosAjustes) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="border-top">
                    <td class="fw-bold">Total Egresos:</td>
                    <td class="text-end fw-bold fs-5 text-danger"><?= formatMoney($totalEgresos) ?></td>
                </tr>
            </table>
        </div>

        <!-- Resumen -->
        <div class="form-section mb-4 bg-light">
            <h6 class="section-title"><i class="bi bi-calculator me-2"></i>Resumen de Caja</h6>
            <table class="table table-borderless">
                <tr>
                    <td>Saldo del Mes Anterior:</td>
                    <td class="text-end"><?= formatMoney($saldoAnterior) ?></td>
                </tr>
                <tr>
                    <td class="text-success">+ Total Ingresos:</td>
                    <td class="text-end text-success">+ <?= formatMoney($totalIngresos) ?></td>
                </tr>
                <tr>
                    <td class="text-danger">- Total Egresos:</td>
                    <td class="text-end text-danger">- <?= formatMoney($totalEgresos) ?></td>
                </tr>
                <tr class="border-top">
                    <td class="fw-bold">Saldo Calculado:</td>
                    <td class="text-end fw-bold fs-4"><?= formatMoney($saldoCalculado) ?></td>
                </tr>
            </table>
        </div>

        <!-- Saldo Final -->
        <div class="form-section mb-4 <?= $diferencia != 0 ? 'border-warning' : 'border-success' ?>">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title mb-0 <?= $diferencia != 0 ? 'text-warning' : 'text-success' ?>">
                    <i class="bi bi-wallet2 me-2"></i>Saldo Final en Caja
                </h6>
                <?php if (!$periodoCerrado): ?>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAjustarSaldo">
                    <i class="bi bi-pencil me-1"></i>Ajustar
                </button>
                <?php endif; ?>
            </div>
            
            <div class="display-5 fw-bold <?= $diferencia != 0 ? 'text-warning' : 'text-success' ?>">
                <?= formatMoney($saldoFinal) ?>
            </div>
            
            <?php if ($diferencia != 0): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Diferencia detectada:</strong> 
                    <?= formatMoney(abs($diferencia)) ?> 
                    <?= $diferencia > 0 ? '(sobrante)' : '(faltante)' ?><br>
                    <small>Saldo calculado: <?= formatMoney($saldoCalculado) ?> | Saldo real: <?= formatMoney($saldoFinal) ?></small>
                </div>
            <?php else: ?>
                <small class="text-muted">El saldo real coincide con el calculado</small>
            <?php endif; ?>
        </div>

        <!-- Descripción de Ajustes -->
        <?php if (!empty($saldo['descripcion_ajustes'])): ?>
        <div class="form-section mb-4">
            <h6 class="section-title"><i class="bi bi-list-ul me-2"></i>Detalle de Ajustes Manuales</h6>
            <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= e($saldo['descripcion_ajustes']) ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <!-- Columna Derecha: Acciones -->
    <div class="col-lg-4">
        <!-- Acciones -->
        <div class="form-section mb-4">
            <h6 class="section-title"><i class="bi bi-gear me-2"></i>Acciones</h6>
            
            <?php if (!$periodoCerrado): ?>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarIngreso">
                        <i class="bi bi-plus-lg me-2"></i>Agregar Ingreso
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso">
                        <i class="bi bi-dash-lg me-2"></i>Agregar Egreso
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAjustarSaldo">
                        <i class="bi bi-pencil me-2"></i>Ajustar Saldo Final
                    </button>
                    <hr>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalCerrarPeriodo">
                        <i class="bi bi-lock me-2"></i>Cerrar Período
                    </button>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Al cerrar el período, el saldo final pasará automáticamente como "Saldo del Mes Anterior" del siguiente mes.</small>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Este período está cerrado. No se pueden realizar más modificaciones.
                </div>
                <div class="d-grid">
                    <a href="saldos-mensuales.php?action=show&comunidad_id=<?= $comunidad['id'] ?>&anio=<?= $mes == 12 ? $anio + 1 : $anio ?>&mes=<?= $mes == 12 ? 1 : $mes + 1 ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-arrow-right me-2"></i>Ver Mes Siguiente
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Información -->
        <div class="form-section">
            <h6 class="section-title"><i class="bi bi-info-circle me-2"></i>Información</h6>
            <ul class="list-unstyled text-muted small">
                <li class="mb-2">
                    <i class="bi bi-check text-success me-1"></i>
                    Los ingresos automáticos se calculan desde los pagos registrados
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-1"></i>
                    Los egresos automáticos se calculan desde pagos a colaboradores
                </li>
                <li class="mb-2">
                    <i class="bi bi-check text-success me-1"></i>
                    Puede agregar ingresos/egresos manuales para ajustes
                </li>
                <li>
                    <i class="bi bi-check text-success me-1"></i>
                    El saldo final puede diferir del calculado (diferencias de caja)
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Agregar Ingreso -->
<div class="modal fade" id="modalAgregarIngreso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="saldos-mensuales.php?action=agregar-ingreso" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="saldo_id" value="<?= $saldo['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="bi bi-plus-lg me-2"></i>Agregar Ingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" class="form-control" required min="1" step="1">
                        </div>
                        <div class="form-text">Ingrese el monto sin puntos ni comas. Ej: 294000</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="3" required
                                  placeholder="Ej: Donación de vecino, Multa por daño, Reembolso, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Agregar Ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar Egreso -->
<div class="modal fade" id="modalAgregarEgreso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="saldos-mensuales.php?action=agregar-egreso" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="saldo_id" value="<?= $saldo['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-dash-lg me-2"></i>Agregar Egreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" class="form-control" required min="1" step="1">
                        </div>
                        <div class="form-text">Ingrese el monto sin puntos ni comas. Ej: 294000</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="3" required
                                  placeholder="Ej: Compra de materiales, Reparación emergencia, Gasto no planificado, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Agregar Egreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajustar Saldo -->
<div class="modal fade" id="modalAjustarSaldo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="saldos-mensuales.php?action=ajustar-saldo" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="saldo_id" value="<?= $saldo['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Ajustar Saldo Final</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Saldo Calculado:</strong> <?= formatMoney($saldoCalculado) ?><br>
                        <small>Use esto cuando el dinero físico en caja difiera del calculado.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Saldo Real en Caja <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="saldo_final" class="form-control" required min="0" step="100"
                                   value="<?= $saldoFinal ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cerrar Período -->
<div class="modal fade" id="modalCerrarPeriodo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="saldos-mensuales.php?action=cerrar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="saldo_id" value="<?= $saldo['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="bi bi-lock me-2"></i>Cerrar Período</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Una vez cerrado el período, no podrá realizar más modificaciones.
                    </div>
                    
                    <p><strong>Resumen del período:</strong></p>
                    <ul>
                        <li>Ingresos: <?= formatMoney($totalIngresos) ?></li>
                        <li>Egresos: <?= formatMoney($totalEgresos) ?></li>
                        <li>Saldo Final: <strong><?= formatMoney($saldoFinal) ?></strong></li>
                    </ul>
                    
                    <p class="text-muted">El saldo final de <strong><?= formatMoney($saldoFinal) ?></strong> pasará automáticamente como "Saldo del Mes Anterior" del siguiente período.</p>
                    
                    <?php if ($diferencia != 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Hay una diferencia de <?= formatMoney(abs($diferencia)) ?>. Se recomienda ajustar el saldo antes de cerrar.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" 
                            onclick="return confirm('¿Está seguro de cerrar este período? No podrá realizar más cambios.')">
                        <i class="bi bi-lock me-2"></i>Cerrar Período
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
