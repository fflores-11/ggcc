<?php
/**
 * Controlador de Correos
 * Gestiona el envío de correos masivos
 */

class CorreosController {
    private EnvioCorreo $envioModel;
    private Comunidad $comunidadModel;
    private Propiedad $propiedadModel;
    private Deuda $deudaModel;
    private MailerHelper $mailerHelper;
    private ConfiguracionSMTP $smtpConfigModel;

    public function __construct() {
        $this->envioModel = new EnvioCorreo();
        $this->comunidadModel = new Comunidad();
        $this->propiedadModel = new Propiedad();
        $this->deudaModel = new Deuda();
        $this->mailerHelper = new MailerHelper();
        $this->smtpConfigModel = new ConfiguracionSMTP();
    }

    /**
     * Lista todos los envíos de correo
     */
    public function index(): void {
        $envios = $this->envioModel->getAllWithDetails();
        $comunidades = $this->comunidadModel->getForSelect();
        
        $title = 'Envío de Correos';
        require_once VIEWS_PATH . '/correos/index.php';
    }

    /**
     * Muestra formulario de envío general
     */
    public function general(): void {
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Envío General a Comunidad';
        require_once VIEWS_PATH . '/correos/general.php';
    }

    /**
     * Muestra formulario de envío de cobranzas
     */
    public function cobranza(): void {
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Envío de Cobranzas';
        require_once VIEWS_PATH . '/correos/cobranza.php';
    }

    /**
     * Procesa el envío de correos generales
     */
    public function enviarGeneral(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('correos.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('correos.php');
        }

        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        $asunto = trim($_POST['asunto'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if (!$comunidadId || empty($asunto) || empty($body)) {
            flash('error', 'Todos los campos son obligatorios');
            redirect('correos.php?action=general');
        }

        // Obtener propiedades activas de la comunidad
        $propiedades = $this->propiedadModel->getByComunidad($comunidadId);
        
        if (empty($propiedades)) {
            flash('error', 'La comunidad seleccionada no tiene propiedades registradas');
            redirect('correos.php?action=general');
        }

        // Verificar configuración SMTP
        if (!$this->smtpConfigModel->getByComunidad($comunidadId)) {
            flash('error', 'No hay configuración SMTP para esta comunidad. Contacte al administrador.');
            redirect('correos.php?action=general');
        }

        $propiedadesIds = array_column($propiedades, 'id');

        $data = [
            'comunidad_id' => $comunidadId,
            'tipo' => 'general',
            'asunto' => $asunto,
            'body' => $body,
            'enviado_por' => getUserId()
        ];

        $envioId = $this->envioModel->crearEnvio($data, $propiedadesIds);

        if ($envioId) {
            // Simular envío (en producción, aquí se enviarían los emails reales)
            $this->simularEnvio($envioId, $propiedades, $asunto, $body);
            
            flash('success', 'Envío general programado exitosamente a ' . count($propiedades) . ' propiedades');
            redirect('correos.php?action=resultado&id=' . $envioId);
        } else {
            flash('error', 'Error al crear el envío');
            redirect('correos.php?action=general');
        }
    }

    /**
     * Procesa el envío de cobranzas
     */
    public function enviarCobranza(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('correos.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('correos.php');
        }

        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        $mes = (int) ($_POST['mes'] ?? 0);
        $anio = (int) ($_POST['anio'] ?? 0);
        $asunto = trim($_POST['asunto'] ?? '');
        $bodyTemplate = trim($_POST['body'] ?? '');

        if (!$comunidadId || !$mes || !$anio || empty($asunto) || empty($bodyTemplate)) {
            flash('error', 'Todos los campos son obligatorios');
            redirect('correos.php?action=cobranza');
        }

        // Obtener deudas pendientes del mes/año para esa comunidad
        $deudas = $this->deudaModel->getPendientesByComunidad($comunidadId);
        $deudasFiltradas = array_filter($deudas, function($d) use ($mes, $anio) {
            return $d['mes'] == $mes && $d['anio'] == $anio;
        });

        if (empty($deudasFiltradas)) {
            flash('warning', 'No hay deudas pendientes para ' . getMonthName($mes) . ' ' . $anio . ' en esta comunidad');
            redirect('correos.php?action=cobranza');
        }

        // Verificar configuración SMTP
        if (!$this->smtpConfigModel->getByComunidad($comunidadId)) {
            flash('error', 'No hay configuración SMTP para esta comunidad. Contacte al administrador.');
            redirect('correos.php?action=cobranza');
        }

        // Agrupar deudas por propiedad
        $propiedadesIds = array_unique(array_column($deudasFiltradas, 'propiedad_id'));

        $data = [
            'comunidad_id' => $comunidadId,
            'tipo' => 'cobranza',
            'mes' => $mes,
            'anio' => $anio,
            'asunto' => $asunto,
            'body' => $bodyTemplate,
            'enviado_por' => getUserId()
        ];

        $envioId = $this->envioModel->crearEnvio($data, $propiedadesIds);

        if ($envioId) {
            // Simular envío personalizado para cada propiedad con sus deudas
            $this->simularEnvioCobranza($envioId, $deudasFiltradas, $asunto, $bodyTemplate);
            
            flash('success', 'Cobranzas enviadas exitosamente a ' . count($propiedadesIds) . ' propiedades');
            redirect('correos.php?action=resultado&id=' . $envioId);
        } else {
            flash('error', 'Error al crear el envío de cobranzas');
            redirect('correos.php?action=cobranza');
        }
    }

    /**
     * Muestra el resultado de un envío
     */
    public function resultado(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de envío no válido');
            redirect('correos.php');
        }

        $envio = $this->envioModel->getWithDetails($id);
        
        if (!$envio) {
            flash('error', 'Envío no encontrado');
            redirect('correos.php');
        }

        $title = 'Resultado de Envío #' . $id;
        require_once VIEWS_PATH . '/correos/resultado.php';
    }

    /**
     * Simula el envío de emails (placeholder para implementación real)
     */
    private function simularEnvio(int $envioId, array $propiedades, string $asunto, string $body): void {
        $exitosos = count($propiedades);
        $fallidos = 0;
        
        // Aquí iría la lógica real de envío de emails
        // Por ahora, simulamos que todos fueron exitosos
        
        $this->envioModel->actualizarContadores($envioId, $exitosos, $fallidos);
    }

    /**
     * Simula el envío de cobranzas personalizadas
     */
    private function simularEnvioCobranza(int $envioId, array $deudas, string $asunto, string $template): void {
        // Agrupar deudas por propiedad
        $deudasPorPropiedad = [];
        foreach ($deudas as $deuda) {
            $propId = $deuda['propiedad_id'];
            if (!isset($deudasPorPropiedad[$propId])) {
                $deudasPorPropiedad[$propId] = [
                    'propiedad' => $deuda,
                    'deudas' => [],
                    'total' => 0
                ];
            }
            $deudasPorPropiedad[$propId]['deudas'][] = $deuda;
            $deudasPorPropiedad[$propId]['total'] += $deuda['monto'];
        }

        $exitosos = count($deudasPorPropiedad);
        $fallidos = 0;
        
        $this->envioModel->actualizarContadores($envioId, $exitosos, $fallidos);
    }

    /**
     * Muestra el formulario de reenvío
     */
    public function reenviar(): void {
        $detalleId = (int) ($_GET['detalle_id'] ?? 0);
        
        if (!$detalleId) {
            flash('error', 'ID de detalle no válido');
            redirect('correos.php');
        }

        // Reenviar email
        $success = $this->envioModel->reenviar($detalleId);
        
        if ($success) {
            flash('success', 'Correo reenviado exitosamente');
        } else {
            flash('error', 'Error al reenviar el correo');
        }
        
        // Redirigir de vuelta al resultado
        $envioId = $_GET['envio_id'] ?? 0;
        redirect('correos.php?action=resultado&id=' . $envioId);
    }

    /**
     * Vista previa del email con variables procesadas
     */
    public function preview(): void {
        header('Content-Type: application/json');
        
        $template = $_POST['body'] ?? '';
        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        
        // Datos de ejemplo para la vista previa
        $sampleData = [
            'propiedad_nombre' => 'Ejemplo: Casa A-101',
            'nombre_dueno' => 'Juan Pérez',
            'monto_deuda' => 85000,
            'mes' => date('n'),
            'anio' => date('Y'),
            'comunidad_nombre' => 'Condominio Los Robles',
            'comunidad_direccion' => 'Av. Las Flores 123, Las Condes',
            'nombre_presidente' => 'María González'
        ];
        
        // Si se proporciona comunidad, obtener datos reales
        if ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            if ($comunidad) {
                $sampleData['comunidad_nombre'] = $comunidad['nombre'];
                $sampleData['comunidad_direccion'] = $comunidad['direccion'];
                $sampleData['nombre_presidente'] = $comunidad['nombre_presidente'];
            }
        }
        
        $processed = $this->envioModel->procesarVariables($template, $sampleData);
        
        echo json_encode([
            'success' => true,
            'preview' => $processed
        ]);
        exit;
    }
}
