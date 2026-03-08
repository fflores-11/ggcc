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
        
        // Obtener resumen financiero del mes actual desde saldos_mensuales
        $resumenFinanciero = $this->saldoModel->getResumenFinancieroMensual(date('n'), date('Y'));
        
        // Calcular ingresos reales desde tabla pagos (más confiable que saldos_mensuales)
        $db = getDB();
        $sqlIngresos = "SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $stmt = $db->prepare($sqlIngresos);
        $stmt->execute([':mes' => date('n'), ':anio' => date('Y')]);
        $ingresosReales = (float) $stmt->fetch()['total'];
        
        // Calcular egresos reales desde tabla pagos_colaboradores
        $sqlEgresos = "SELECT COALESCE(SUM(monto), 0) as total FROM pagos_colaboradores WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $stmt = $db->prepare($sqlEgresos);
        $stmt->execute([':mes' => date('n'), ':anio' => date('Y')]);
        $egresosReales = (float) $stmt->fetch()['total'];
        
        // Actualizar el resumen con los valores reales
        $resumenFinanciero['total_ingresos'] = $ingresosReales;
        $resumenFinanciero['total_egresos'] = $egresosReales;
        
        // Recalcular saldo actual: saldo anterior + ingresos - egresos
        $resumenFinanciero['saldo_actual'] = $resumenFinanciero['saldo_anterior'] + $ingresosReales - $egresosReales;
        
        $title = 'Dashboard';
        require_once VIEWS_PATH . '/dashboard/index.php';
    }
}
