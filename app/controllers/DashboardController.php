<?php
/**
 * Controlador del Dashboard
 * Muestra métricas y estadísticas principales
 */

class DashboardController {
    private Dashboard $dashboardModel;
    private Comunidad $comunidadModel;
    private ConfiguracionSistema $configModel;
    private SaldoMensual $saldoModel;

    public function __construct() {
        $this->dashboardModel = new Dashboard();
        $this->comunidadModel = new Comunidad();
        $this->configModel = new ConfiguracionSistema();
        $this->saldoModel = new SaldoMensual();
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
        
        // Obtener resumen financiero del mes actual
        $resumenFinanciero = $this->saldoModel->getResumenFinancieroMensual(date('n'), date('Y'));
        
        $title = 'Dashboard';
        require_once VIEWS_PATH . '/dashboard/index.php';
    }
}
