<?php
/**
 * Controlador de Saldos Mensuales
 * Gestiona el control de caja mensual de las comunidades
 */

class SaldosMensualesController {
    private SaldoMensual $saldoModel;
    private Comunidad $comunidadModel;

    public function __construct() {
        $this->saldoModel = new SaldoMensual();
        $this->comunidadModel = new Comunidad();
    }

    /**
     * Lista todos los saldos mensuales
     * Si se pasa comunidad_id, mes y anio, muestra el detalle específico
     */
    public function index(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : date('n');
        
        // Si se especificó comunidad, mes y año, mostrar el detalle de ese período
        if ($comunidadId && $mes && $anio) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            
            if (!$comunidad) {
                flash('error', 'Comunidad no encontrada');
                redirect('saldos-mensuales.php');
            }
            
            // Obtener o crear el saldo del período consultado
            $saldoConsultado = $this->saldoModel->getOrCreate($comunidadId, $anio, $mes);
            
            // Calcular mes y año anterior
            $mesAnterior = $mes - 1;
            $anioAnterior = $anio;
            if ($mesAnterior == 0) {
                $mesAnterior = 12;
                $anioAnterior = $anio - 1;
            }
            
            // Obtener saldo del mes anterior
            $saldoMesAnterior = $this->saldoModel->getSaldoPeriodo($comunidadId, $anioAnterior, $mesAnterior) ?? 0;
            
            // Pasar variables a la vista
            $mesAnteriorConsulta = $mesAnterior;
            $anioAnteriorConsulta = $anioAnterior;
        } else {
            $comunidad = null;
            $saldoConsultado = null;
            $saldoMesAnterior = 0;
            $mesAnteriorConsulta = null;
            $anioAnteriorConsulta = null;
        }
        
        // Obtener histórico
        if ($comunidadId) {
            $saldos = $this->saldoModel->getByComunidad($comunidadId);
        } else {
            $saldos = $this->saldoModel->getAllResumen();
        }
        
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Saldos Mensuales - Control de Caja';
        require_once VIEWS_PATH . '/saldos_mensuales/index.php';
    }

    /**
     * Trae el saldo del mes anterior al período actual
     */
    public function traerSaldoAnterior(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('saldos-mensuales.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('saldos-mensuales.php');
        }

        $saldoId = (int) ($_POST['saldo_id'] ?? 0);
        $monto = (float) ($_POST['monto'] ?? 0);
        
        if (!$saldoId || $monto <= 0) {
            flash('error', 'Datos inválidos');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        if ($saldo['cerrado']) {
            flash('error', 'No se puede modificar un período cerrado');
            redirect('saldos-mensuales.php');
        }

        // Actualizar el saldo_mes_anterior
        $data = [
            'saldo_mes_anterior' => $monto,
            'descripcion_ajustes' => ($saldo['descripcion_ajustes'] ? $saldo['descripcion_ajustes'] . "\n" : '') . 
                "Saldo traído desde mes anterior: " . formatMoney($monto)
        ];
        
        if ($this->saldoModel->update($saldoId, $data)) {
            // Recalcular totales
            $this->saldoModel->actualizarTotales($saldoId);
            
            flash('success', 'Saldo del mes anterior (' . formatMoney($monto) . ') agregado exitosamente. Se ha recalculado el saldo del período.');
        } else {
            flash('error', 'Error al traer el saldo del mes anterior');
        }

        redirect("saldos-mensuales.php?comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }

    /**
     * Muestra el detalle de un período específico
     */
    public function show(): void {
        $comunidadId = (int) ($_GET['comunidad_id'] ?? 0);
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        
        if (!$comunidadId) {
            flash('error', 'Debe seleccionar una comunidad');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->getOrCreate($comunidadId, $anio, $mes);
        $comunidad = $this->comunidadModel->find($comunidadId);
        
        // Recargar saldo con totales actualizados
        $this->saldoModel->actualizarTotales($saldo['id']);
        $saldo = $this->saldoModel->find($saldo['id']);
        
        // Calcular totales para mostrar
        $totalIngresos = $saldo['total_ingresos_gc'] + $saldo['ajustes_ingreso'];
        $totalEgresos = $saldo['total_egresos_colaboradores'] + $saldo['ajustes_egreso'];
        $saldoTeorico = ($saldo['saldo_mes_anterior'] + $totalIngresos) - $totalEgresos;
        $diferencia = $saldo['saldo_final'] - $saldoTeorico;
        
        $title = "Control de Caja - {$comunidad['nombre']} - " . getMonthName($mes) . " $anio";
        require_once VIEWS_PATH . '/saldos_mensuales/show.php';
    }

    /**
     * Procesa la consulta de un período (formulario GET)
     */
    public function consultar(): void {
        $comunidadId = (int) ($_POST['comunidad_id'] ?? $_GET['comunidad_id'] ?? 0);
        $anio = (int) ($_POST['anio'] ?? $_GET['anio'] ?? date('Y'));
        $mes = (int) ($_POST['mes'] ?? $_GET['mes'] ?? date('n'));
        
        if (!$comunidadId) {
            flash('error', 'Debe seleccionar una comunidad');
            redirect('saldos-mensuales.php');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id=$comunidadId&anio=$anio&mes=$mes");
    }

    /**
     * Agrega un ajuste manual de ingreso
     */
    public function agregarIngreso(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('saldos-mensuales.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('saldos-mensuales.php');
        }

        $saldoId = (int) ($_POST['saldo_id'] ?? 0);
        $monto = (float) ($_POST['monto'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (!$saldoId || $monto <= 0) {
            flash('error', 'Debe ingresar un monto válido');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        if ($this->saldoModel->agregarAjusteIngreso($saldoId, $monto, $descripcion)) {
            flash('success', 'Ingreso agregado exitosamente: ' . formatMoney($monto));
        } else {
            flash('error', 'Error al agregar el ingreso');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }

    /**
     * Agrega un ajuste manual de egreso
     */
    public function agregarEgreso(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('saldos-mensuales.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('saldos-mensuales.php');
        }

        $saldoId = (int) ($_POST['saldo_id'] ?? 0);
        $monto = (float) ($_POST['monto'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (!$saldoId || $monto <= 0) {
            flash('error', 'Debe ingresar un monto válido');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        if ($this->saldoModel->agregarAjusteEgreso($saldoId, $monto, $descripcion)) {
            flash('success', 'Egreso agregado exitosamente: ' . formatMoney($monto));
        } else {
            flash('error', 'Error al agregar el egreso');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }

    /**
     * Establece saldo final manual (para ajustes de caja)
     */
    public function ajustarSaldo(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('saldos-mensuales.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('saldos-mensuales.php');
        }

        $saldoId = (int) ($_POST['saldo_id'] ?? 0);
        $saldoFinal = (float) ($_POST['saldo_final'] ?? 0);
        
        if (!$saldoId) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        if ($this->saldoModel->establecerSaldoFinal($saldoId, $saldoFinal)) {
            // Calcular diferencia
            $totalIngresos = $saldo['total_ingresos_gc'] + $saldo['ajustes_ingreso'];
            $totalEgresos = $saldo['total_egresos_colaboradores'] + $saldo['ajustes_egreso'];
            $saldoTeorico = ($saldo['saldo_mes_anterior'] + $totalIngresos) - $totalEgresos;
            $diferencia = $saldoFinal - $saldoTeorico;
            
            if ($diferencia != 0) {
                flash('warning', 'Se registró una diferencia de ' . formatMoney(abs($diferencia)) . ' entre el saldo calculado y el saldo real');
            } else {
                flash('success', 'Saldo ajustado exitosamente a: ' . formatMoney($saldoFinal));
            }
        } else {
            flash('error', 'Error al ajustar el saldo');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }

    /**
     * Cierra el período mensual
     */
    public function cerrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('saldos-mensuales.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('saldos-mensuales.php');
        }

        $saldoId = (int) ($_POST['saldo_id'] ?? 0);
        
        if (!$saldoId) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        $usuarioId = getUserId();

        if ($this->saldoModel->cerrarPeriodo($saldoId, $usuarioId)) {
            flash('success', 'Período cerrado exitosamente. El saldo de ' . formatMoney($saldo['saldo_final']) . ' pasará al mes siguiente.');
        } else {
            flash('error', 'Error al cerrar el período');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }

    /**
     * Recalcula los totales de un período
     */
    public function recalcular(): void {
        $saldoId = (int) ($_GET['id'] ?? 0);
        
        if (!$saldoId) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        $saldo = $this->saldoModel->find($saldoId);
        if (!$saldo) {
            flash('error', 'Período no encontrado');
            redirect('saldos-mensuales.php');
        }

        if ($this->saldoModel->actualizarTotales($saldoId)) {
            flash('success', 'Totales recalculados exitosamente');
        } else {
            flash('error', 'Error al recalcular los totales');
        }

        redirect("saldos-mensuales.php?action=show&comunidad_id={$saldo['comunidad_id']}&anio={$saldo['anio']}&mes={$saldo['mes']}");
    }
}
