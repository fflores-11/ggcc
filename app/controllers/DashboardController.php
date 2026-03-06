<?php
/**
 * Controlador del Dashboard
 * Muestra métricas y estadísticas principales
 */

class DashboardController {
    private Dashboard $dashboardModel;
    private Comunidad $comunidadModel;

    public function __construct() {
        $this->dashboardModel = new Dashboard();
        $this->comunidadModel = new Comunidad();
    }

    /**
     * Muestra el dashboard principal
     */
    public function index(): void {
        // Obtener todas las métricas
        $metricas = $this->dashboardModel->getMetricas();
        $comunidadesDeuda = $this->dashboardModel->getComunidadesConMayorDeuda(5);
        $ultimasActividades = $this->dashboardModel->getUltimasActividades(5);
        $propiedadesMorosas = $this->dashboardModel->getPropiedadesMorosas(5);
        $resumenComunidades = $this->dashboardModel->getResumenPorComunidad();
        $estadisticasMensuales = $this->dashboardModel->getEstadisticasMensuales(6);
        
        // Preparar datos para gráficos
        $labelsGrafico = array_column($estadisticasMensuales, 'mes_nombre');
        $datosPagos = array_column($estadisticasMensuales, 'pagos');
        $datosDeudas = array_column($estadisticasMensuales, 'deudas');
        
        $title = 'Dashboard';
        require_once VIEWS_PATH . '/dashboard/index.php';
    }
}
