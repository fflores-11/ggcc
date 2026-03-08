<?php
/**
 * Vista: Página de Reportes
 */

$title = 'Reportes';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Reportes del Sistema</h4>
</div>

<div class="row">
    <!-- Fila 1: 3 reportes -->
    <!-- Reporte de Morosidad -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-danger text-white rounded-circle p-3 me-3">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Morosidad</h5>
                </div>
                <p class="card-text text-muted">
                    Identifica propiedades con deudas pendientes. Filtra por cantidad mínima de meses adeudados.
                </p>
                <a href="reportes.php?action=morosidad" class="btn btn-outline-danger w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Pagos -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-success text-white rounded-circle p-3 me-3">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Pagos</h5>
                </div>
                <p class="card-text text-muted">
                    Detalle de pagos recibidos por período. Muestra propiedades que pagaron en un mes específico.
                </p>
                <a href="reportes.php?action=pagos" class="btn btn-outline-success w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Deudas -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-warning text-dark rounded-circle p-3 me-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Deudas</h5>
                </div>
                <p class="card-text text-muted">
                    Lista de deudas pendientes y pagadas por período. Útil para conciliación mensual.
                </p>
                <a href="reportes.php?action=deudas" class="btn btn-outline-warning w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Fila 2: 3 reportes -->
    <!-- Reporte de Egresos -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-dark text-white rounded-circle p-3 me-3">
                        <i class="bi bi-arrow-up-circle fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Egresos</h5>
                </div>
                <p class="card-text text-muted">
                    Detalle de pagos efectuados a colaboradores. Formato tipo libro de caja mensual.
                </p>
                <a href="reportes.php?action=egresos" class="btn btn-outline-dark w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Consolidados -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-grid-3x3 fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Consolidado</h5>
                </div>
                <p class="card-text text-muted">
                    Matriz completa de pagos por propiedad y mes. Visualiza el estado de pagos de toda la comunidad.
                </p>
                <a href="consolidados.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Consolidado
                </a>
            </div>
        </div>
    </div>

    <!-- Exportación -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-info text-white rounded-circle p-3 me-3">
                        <i class="bi bi-download fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Exportación</h5>
                </div>
                <p class="card-text text-muted">
                    Exporta datos a Excel para análisis externo. Disponible desde el consolidado de pagos.
                </p>
                <a href="consolidados.php" class="btn btn-outline-info w-100">
                    <i class="bi bi-file-earmark-excel me-2"></i>Ir a Exportar
                </a>
            </div>
        </div>
    </div>
</div>
                    <h5 class="card-title mb-0">Morosidad</h5>
                </div>
                <p class="card-text text-muted">
                    Identifica propiedades con deudas pendientes. Filtra por cantidad mínima de meses adeudados.
                </p>
                <a href="reportes.php?action=morosidad" class="btn btn-outline-danger w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Pagos -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-success text-white rounded-circle p-3 me-3">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Pagos</h5>
                </div>
                <p class="card-text text-muted">
                    Detalle de pagos recibidos por período. Muestra propiedades que pagaron en un mes específico.
                </p>
                <a href="reportes.php?action=pagos" class="btn btn-outline-success w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte de Deudas -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-warning text-dark rounded-circle p-3 me-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Deudas</h5>
                </div>
                <p class="card-text text-muted">
                    Lista de deudas pendientes y pagadas por período. Útil para conciliación mensual.
                </p>
                <a href="reportes.php?action=deudas" class="btn btn-outline-warning w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    <!-- Reporte de Egresos -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-dark text-white rounded-circle p-3 me-3">
                        <i class="bi bi-arrow-up-circle fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Egresos</h5>
                </div>
                <p class="card-text text-muted">
                    Detalle de pagos efectuados a colaboradores. Formato tipo libro de caja mensual.
                </p>
                <a href="reportes.php?action=egresos" class="btn btn-outline-dark w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Reporte de Consolidados -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-grid-3x3 fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Consolidado</h5>
                </div>
                <p class="card-text text-muted">
                    Matriz completa de pagos por propiedad y mes. Visualiza el estado de pagos de toda la comunidad.
                </p>
                <a href="consolidados.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-arrow-right me-2"></i>Ver Consolidado
                </a>
            </div>
        </div>
    </div>

    <!-- Exportación -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon bg-info text-white rounded-circle p-3 me-3">
                        <i class="bi bi-download fs-4"></i>
                    </div>
                    <h5 class="card-title mb-0">Exportación</h5>
                </div>
                <p class="card-text text-muted">
                    Exporta datos a Excel para análisis externo. Disponible desde el consolidado de pagos.
                </p>
                <a href="consolidados.php" class="btn btn-outline-info w-100">
                    <i class="bi bi-file-earmark-excel me-2"></i>Ir a Exportar
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
