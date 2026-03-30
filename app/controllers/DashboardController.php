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
        
        // Calcular ingresos reales desde tabla pagos (todos los pagos recibidos en el mes)
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
        
        // Obtener saldo del mes anterior (igual que en Reportes)
        $resumenMesAnterior = $this->getResumenMesAnteriorDesdeSaldos(date('n'), date('Y'));
        $saldoMesAnterior = $resumenMesAnterior['saldo_actual'];
        
        // Recalcular saldo actual: saldo mes anterior + ingresos - egresos
        $saldoActual = $saldoMesAnterior + $ingresosReales - $egresosReales;
        
        // Preparar array de resumen financiero
        $resumenFinanciero = [
            'total_comunidades' => $this->comunidadModel->countActive(),
            'saldo_anterior' => $saldoMesAnterior,
            'total_ingresos' => $ingresosReales,
            'total_egresos' => $egresosReales,
            'saldo_actual' => $saldoActual
        ];
        
        $title = 'Dashboard';
        require_once VIEWS_PATH . '/dashboard/index.php';
    }
    
    /**
     * Obtiene el resumen del mes anterior desde la tabla saldos_mensuales
     * Usado para calcular el saldo inicial del mes actual
     * @param int $mes Mes actual
     * @param int $anio Año actual
     * @return array ['saldo_actual' => float, 'saldo_anterior' => float, 'total_ingresos' => float, 'total_egresos' => float]
     */
    private function getResumenMesAnteriorDesdeSaldos(int $mes, int $anio): array {
        // Calcular mes y año anterior
        $mesAnterior = $mes - 1;
        $anioAnterior = $anio;
        
        if ($mesAnterior < 1) {
            $mesAnterior = 12;
            $anioAnterior--;
        }
        
        // Obtener resumen del mes anterior desde saldos_mensuales
        $sql = "SELECT 
                    COALESCE(SUM(saldo_final), 0) as saldo_actual,
                    COALESCE(SUM(saldo_mes_anterior), 0) as saldo_anterior,
                    COALESCE(SUM(total_ingresos_gc + ajustes_ingreso), 0) as total_ingresos,
                    COALESCE(SUM(total_egresos_colaboradores + ajustes_egreso), 0) as total_egresos
                FROM saldos_mensuales
                WHERE mes = :mes AND anio = :anio";
        
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([':mes' => $mesAnterior, ':anio' => $anioAnterior]);
        $resumen = $stmt->fetch();
        
        // Si no hay datos en saldos_mensuales, retornar 0
        if (!$resumen || ($resumen['saldo_actual'] == 0 && $resumen['total_ingresos'] == 0 && $resumen['total_egresos'] == 0)) {
            return [
                'saldo_actual' => 0,
                'saldo_anterior' => 0,
                'total_ingresos' => 0,
                'total_egresos' => 0
            ];
        }
        
        return [
            'saldo_actual' => (float) ($resumen['saldo_actual'] ?? 0),
            'saldo_anterior' => (float) ($resumen['saldo_anterior'] ?? 0),
            'total_ingresos' => (float) ($resumen['total_ingresos'] ?? 0),
            'total_egresos' => (float) ($resumen['total_egresos'] ?? 0)
        ];
    }
}
