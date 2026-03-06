<?php
/**
 * Controlador de Pagos
 * Gestiona el registro de pagos y generación de recibos
 */

class PagosController {
    private Pago $pagoModel;
    private Deuda $deudaModel;
    private Propiedad $propiedadModel;
    private Comunidad $comunidadModel;

    public function __construct() {
        $this->pagoModel = new Pago();
        $this->deudaModel = new Deuda();
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
    }

    /**
     * Lista todos los pagos
     */
    public function index(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        
        if ($comunidadId) {
            $pagos = $this->pagoModel->getByComunidad($comunidadId);
        } else {
            $pagos = $this->pagoModel->getAllWithDetails();
        }
        
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Listado de Pagos';
        require_once VIEWS_PATH . '/pagos/index.php';
    }

    /**
     * Muestra el formulario para registrar un nuevo pago
     */
    public function create(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $propiedadId = isset($_GET['propiedad_id']) ? (int) $_GET['propiedad_id'] : null;
        
        $comunidades = $this->comunidadModel->getForSelect();
        $comunidad = null;
        $propiedad = null;
        $deudas = [];
        
        if ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
        }
        
        if ($propiedadId) {
            $propiedad = $this->propiedadModel->getWithDeudas($propiedadId);
            if ($propiedad) {
                $deudas = $propiedad['deudas'];
            }
        }
        
        $title = 'Registrar Pago';
        require_once VIEWS_PATH . '/pagos/create.php';
    }

    /**
     * Procesa el registro de un nuevo pago
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pagos.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('pagos.php');
        }

        $propiedadId = (int) ($_POST['propiedad_id'] ?? 0);
        $deudaIds = $_POST['deudas'] ?? [];
        
        if (!$propiedadId || empty($deudaIds)) {
            flash('error', 'Debe seleccionar una propiedad y al menos una deuda a pagar');
            redirect('pagos.php?action=create');
        }

        // Validar que las deudas existan y estén pendientes
        $montoTotal = 0;
        $deudasValidas = [];
        
        foreach ($deudaIds as $deudaId) {
            $deuda = $this->deudaModel->getWithPropiedad((int) $deudaId);
            if ($deuda && $deuda['estado'] === 'Pendiente' && $deuda['propiedad_id'] == $propiedadId) {
                $deudasValidas[] = (int) $deudaId;
                $montoTotal += (float) $deuda['monto'];
            }
        }

        if (empty($deudasValidas)) {
            flash('error', 'No se encontraron deudas válidas para pagar');
            redirect('pagos.php?action=create&propiedad_id=' . $propiedadId);
        }

        $data = [
            'propiedad_id' => $propiedadId,
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'monto' => $montoTotal,
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        $pagoId = $this->pagoModel->registrarPago($data, $deudasValidas);

        if ($pagoId) {
            flash('success', 'Pago registrado exitosamente. Total pagado: ' . formatMoney($montoTotal));
            redirect('pagos.php?action=recibo&id=' . $pagoId);
        } else {
            flash('error', 'Error al registrar el pago');
            redirect('pagos.php?action=create&propiedad_id=' . $propiedadId);
        }
    }

    /**
     * Muestra el recibo de un pago
     */
    public function recibo(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('pagos.php');
        }

        $pago = $this->pagoModel->getWithDetails($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('pagos.php');
        }

        $pago['numero_recibo'] = $this->pagoModel->generarNumeroRecibo($id);
        
        $title = 'Recibo de Pago #' . $pago['numero_recibo'];
        require_once VIEWS_PATH . '/pagos/recibo.php';
    }

    /**
     * API: Obtiene deudas pendientes de una propiedad (para AJAX)
     */
    public function apiGetDeudas(): void {
        header('Content-Type: application/json');
        
        $propiedadId = (int) ($_GET['propiedad_id'] ?? 0);
        
        if (!$propiedadId) {
            echo json_encode(['success' => false, 'message' => 'ID de propiedad requerido']);
            exit;
        }

        $deudas = $this->deudaModel->getPendientesByPropiedad($propiedadId);
        $totalDeuda = $this->deudaModel->getTotalDeudaPropiedad($propiedadId);
        
        echo json_encode([
            'success' => true, 
            'data' => $deudas,
            'total_deuda' => $totalDeuda
        ]);
        exit;
    }

    /**
     * Genera PDF del recibo (placeholder para futura implementación)
     */
    public function pdf(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('pagos.php');
        }

        $pago = $this->pagoModel->getWithDetails($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('pagos.php');
        }

        // Por ahora, solo marca como generado y redirige al recibo
        $this->pagoModel->marcarReciboGenerado($id, 'recibo_' . $id . '.pdf');
        
        flash('success', 'Recibo marcado como generado. Implementación de PDF pendiente.');
        redirect('pagos.php?action=recibo&id=' . $id);
    }

    /**
     * Envía recibo por email (placeholder para futura implementación)
     */
    public function enviarEmail(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('pagos.php');
        }

        $pago = $this->pagoModel->getWithDetails($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('pagos.php');
        }

        // Placeholder - en producción, enviaría el email
        flash('success', 'Recibo enviado por email a: ' . $pago['email_dueno']);
        redirect('pagos.php?action=recibo&id=' . $id);
    }

    /**
     * Generar deudas mensuales para una comunidad
     */
    public function generarDeudas(): void {
        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        $mes = (int) ($_POST['mes'] ?? 0);
        $anio = (int) ($_POST['anio'] ?? 0);
        
        if (!$comunidadId || !$mes || !$anio) {
            flash('error', 'Datos incompletos');
            redirect('pagos.php');
        }

        $cantidad = $this->deudaModel->generarDeudasMes($comunidadId, $mes, $anio);
        
        if ($cantidad > 0) {
            flash('success', "Se generaron {$cantidad} deudas para " . getMonthName($mes) . " {$anio}");
        } else {
            flash('warning', 'No se generaron nuevas deudas (puede que ya existan para este período)');
        }
        
        redirect('pagos.php');
    }
}
