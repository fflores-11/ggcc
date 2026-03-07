<?php
/**
 * Vista: Detalle de Colaborador
 */

$isEmpresa = ($colaborador['tipo_colaborador'] ?? 'personal') === 'empresa';
$tipoLabel = $isEmpresa ? 'Empresa' : 'Personal';
$tipoIcon = $isEmpresa ? 'building' : 'person';
$title = 'Detalle de ' . $tipoLabel . ': ' . $colaborador['nombre'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>
        <span class="badge bg-secondary me-2"><i class="bi bi-<?= $tipoIcon ?>"></i> <?= $tipoLabel ?></span>
        <?= e($colaborador['nombre']) ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="colaboradores.php?action=createPago&colaborador_id=<?= $colaborador['id'] ?>" class="btn btn-success">
            <i class="bi bi-cash-coin me-2"></i>Registrar Pago
        </a>
        <a href="colaboradores.php?action=edit&id=<?= $colaborador['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="colaboradores.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Colaborador -->
    <div class="col-lg-4 mb-4">
        <div class="form-section">
            <?php if ($isEmpresa): ?>
                <!-- Información de Empresa -->
                <h6 class="section-title"><i class="bi bi-building me-2"></i>Información de la Empresa</h6>
                
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Nombre:</td>
                        <td class="fw-bold"><?= e($colaborador['nombre']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">N° Cliente:</td>
                        <td class="fw-bold text-primary"><?= e($colaborador['numero_cliente']) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <!-- Información de Personal -->
                <h6 class="section-title"><i class="bi bi-person me-2"></i>Información Personal</h6>
                
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Nombre:</td>
                        <td class="fw-bold"><?= e($colaborador['nombre']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td><?= e($colaborador['email']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">WhatsApp:</td>
                        <td><?= e($colaborador['whatsapp']) ?></td>
                    </tr>
                </table>

                <hr>
                <h6 class="mb-3">Dirección</h6>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Dirección:</td>
                        <td><?= e($colaborador['direccion']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Región:</td>
                        <td><?= e($colaborador['region']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Comuna:</td>
                        <td><?= e($colaborador['comuna']) ?></td>
                    </tr>
                </table>

                <?php if (!empty($colaborador['banco']) || !empty($colaborador['numero_cuenta'])): ?>
                <hr>
                <h6 class="mb-3"><i class="bi bi-bank me-2"></i>Datos Bancarios</h6>
                <table class="table table-borderless">
                    <?php if (!empty($colaborador['banco'])): ?>
                    <tr>
                        <td class="text-muted" width="40%">Banco:</td>
                        <td><?= e($colaborador['banco']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($colaborador['tipo_cuenta'])): ?>
                    <tr>
                        <td class="text-muted">Tipo de Cuenta:</td>
                        <td><?= $colaborador['tipo_cuenta'] === 'vista' ? 'Cuenta Vista (RUT)' : 'Cuenta Corriente' ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($colaborador['numero_cuenta'])): ?>
                    <tr>
                        <td class="text-muted">Número de Cuenta:</td>
                        <td><?= e($colaborador['numero_cuenta']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Resumen Financiero -->
        <div class="form-section mt-4">
            <h6 class="section-title">Resumen Financiero</h6>
            
            <div class="text-center mb-3">
                <div class="display-6 fw-bold text-primary"><?= formatMoney($totalPagado) ?></div>
                <small class="text-muted">Total Pagado</small>
            </div>

            <div class="d-grid">
                <a href="colaboradores.php?action=createPago&colaborador_id=<?= $colaborador['id'] ?>" class="btn btn-success">
                    <i class="bi bi-plus-lg me-2"></i>Registrar Nuevo Pago
                </a>
            </div>
        </div>
    </div>

    <!-- Historial de Pagos -->
    <div class="col-lg-8 mb-4">
        <div class="table-container">
            <div class="table-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Pagos</h5>
                <span class="badge bg-info"><?= count($pagos) ?> pagos</span>
            </div>
            <div class="table-body">
                <?php if (empty($pagos)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cash-stack display-4 mb-3"></i>
                        <p class="mb-0">No hay pagos registrados</p>
                        <a href="colaboradores.php?action=createPago&colaborador_id=<?= $colaborador['id'] ?>" class="btn btn-sm btn-success mt-3">
                            Registrar Primer Pago
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Detalle</th>
                                <th class="text-end">Monto</th>
                                <th>Registrado por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= formatDate($pago['fecha']) ?></td>
                                    <td><?= e($pago['detalle']) ?></td>
                                    <td class="text-end fw-bold"><?= formatMoney((float)$pago['monto']) ?></td>
                                    <td><?= e($pago['pagado_por_nombre']) ?></td>
                                    <td class="text-center">
                                        <a href="colaboradores.php?action=deletePago&id=<?= $pago['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Eliminar Pago"
                                           onclick="return confirm('¿Está seguro de eliminar este pago?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <td colspan="2" class="text-end fw-bold fs-5">TOTAL PAGADO:</td>
                                <td colspan="3" class="fw-bold fs-4"><?= formatMoney($totalPagado) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
