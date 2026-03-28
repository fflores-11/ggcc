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
    private ConfiguracionSMTP $smtpConfigModel;
    private Usuario $usuarioModel;

    public function __construct() {
        $this->pagoModel = new Pago();
        $this->deudaModel = new Deuda();
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
        $this->smtpConfigModel = new ConfiguracionSMTP();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Lista todos los pagos
     */
    public function index(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        
        // Paginación
        $pagination = getPaginationParams(20);
        
        if ($comunidadId) {
            $totalRecords = $this->pagoModel->countPagos($comunidadId);
            $pagos = $this->pagoModel->getByComunidadPaginated($comunidadId, $pagination['offset'], $pagination['perPage']);
        } else {
            $totalRecords = $this->pagoModel->countPagos();
            $pagos = $this->pagoModel->getAllWithDetailsPaginated($pagination['offset'], $pagination['perPage']);
        }
        
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Listado de Pagos';
        $currentPage = $pagination['page'];
        $perPage = $pagination['perPage'];
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

        // Verificar si se entregó un monto mayor (para generar saldo)
        $montoEntregado = isset($_POST['monto_entregado']) ? (float) $_POST['monto_entregado'] : $montoTotal;
        $usarSaldo = isset($_POST['usar_saldo']) && $_POST['usar_saldo'] == '1';
        
        // Si se quiere usar saldo disponible
        if ($usarSaldo) {
            $saldoDisponible = $this->propiedadModel->getSaldo($propiedadId);
            if ($saldoDisponible > 0) {
                // Aplicar saldo a las deudas
                $resultadoSaldo = $this->deudaModel->intentarPagoConSaldo($propiedadId);
                if ($resultadoSaldo['deudas_pagadas'] > 0) {
                    flash('success', "Se aplicó " . formatMoney($resultadoSaldo['monto_aplicado']) . " del saldo disponible a " . $resultadoSaldo['deudas_pagadas'] . " deuda(s). Saldo restante: " . formatMoney($resultadoSaldo['saldo_restante']));
                    // Recalcular deudas pendientes
                    $deudasRestantes = $this->deudaModel->getPendientesByPropiedad($propiedadId);
                    if (empty($deudasRestantes)) {
                        flash('success', 'Todas las deudas han sido pagadas con el saldo disponible');
                        redirect('propiedades.php?action=show&id=' . $propiedadId);
                    }
                }
            }
        }

        $data = [
            'propiedad_id' => $propiedadId,
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'monto' => $montoTotal,
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        // Si hay monto entregado mayor al total, usar el nuevo método con saldo
        if ($montoEntregado > $montoTotal) {
            $resultado = $this->pagoModel->registrarPagoConSaldo($data, $deudasValidas, $montoEntregado);
            if ($resultado) {
                $mensaje = 'Pago registrado exitosamente. Total deudas: ' . formatMoney($resultado['total_deudas']);
                if ($resultado['saldo_generado'] > 0) {
                    $mensaje .= '. Saldo generado: ' . formatMoney($resultado['saldo_generado']) . ' (disponible para próximas deudas)';
                }
                flash('success', $mensaje);
                redirect('pagos.php?action=recibo&id=' . $resultado['pago_id']);
            } else {
                flash('error', 'Error al registrar el pago');
                redirect('pagos.php?action=create&propiedad_id=' . $propiedadId);
            }
        } else {
            // Pago normal sin saldo
            $pagoId = $this->pagoModel->registrarPago($data, $deudasValidas);
            if ($pagoId) {
                flash('success', 'Pago registrado exitosamente. Total pagado: ' . formatMoney($montoTotal));
                redirect('pagos.php?action=recibo&id=' . $pagoId);
            } else {
                flash('error', 'Error al registrar el pago');
                redirect('pagos.php?action=create&propiedad_id=' . $propiedadId);
            }
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
        $saldoDisponible = $this->propiedadModel->getSaldo($propiedadId);
        
        echo json_encode([
            'success' => true, 
            'data' => $deudas,
            'total_deuda' => $totalDeuda,
            'saldo_disponible' => $saldoDisponible,
            'puede_usar_saldo' => $saldoDisponible > 0 && $totalDeuda > 0
        ]);
        exit;
    }

    /*
     * NOTA: Las funciones de Pago Anticipado han sido deshabilitadas.
     * Use el módulo "Saldos Mensuales" en Operaciones para el control de caja.
     * 
    public function createAnticipado(): void { ... }
    public function storeAnticipado(): void { ... }
    public function apiGetSaldo(): void { ... }
    */

    /**
     * Genera y descarga el recibo en PDF usando Dompdf
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

        // Generar número de recibo
        $pago['numero_recibo'] = $this->pagoModel->generarNumeroRecibo($id);

        // Formatear meses pagados
        $mesesPagados = [];
        foreach ($pago['detalles'] as $detalle) {
            $mesesPagados[] = getMonthName((int)$detalle['mes']) . ' ' . $detalle['anio'];
        }

        // Cargar la firma del usuario actual
        $userId = getUserId();
        $firmaPath = null;
        if ($userId) {
            $firmaPath = $this->usuarioModel->getFirmaPath($userId);
            // Verificar que la firma existe
            if ($firmaPath && !file_exists(PUBLIC_PATH . '/' . $firmaPath)) {
                $firmaPath = null;
            }
        }

        // Cargar Dompdf desde vendor
        require_once ROOT_PATH . '/vendor/autoload.php';

        // Configurar Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', PUBLIC_PATH);

        $dompdf = new \Dompdf\Dompdf($options);

        // Crear contenido HTML del PDF
        $html = $this->generarHtmlPdf($pago, $mesesPagados, $firmaPath);
        
        // DEBUG: Guardar HTML generado para verificación
        // file_put_contents(ROOT_PATH . '/debug_pdf.html', $html);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Descargar el PDF
        $filename = 'Recibo_' . $pago['numero_recibo'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Genera el HTML para el PDF del recibo
     */
    private function generarHtmlPdf(array $pago, array $mesesPagados, ?string $firmaPath = null): string {
        $logo = APP_NAME;
        $fecha = formatDate($pago['fecha']);
        $total = formatMoney((float)$pago['monto']);
        
        // Preparar firma si existe - usar URL completa para Dompdf
        $firmaHtml = '';
        $fullPath = $firmaPath ? PUBLIC_PATH . '/' . $firmaPath : null;
        if ($firmaPath && file_exists($fullPath)) {
            // Usar URL completa para que Dompdf pueda cargar la imagen
            $firmaUrl = BASE_URL_FULL . $firmaPath;
            $firmaHtml = "<div style='margin-top: 10px; margin-bottom: 5px;'><img src='" . $firmaUrl . "' alt='Firma' style='max-height: 150px; max-width: 400px;'></div>";
        }
        
        $filas = '';
        foreach ($pago['detalles'] as $detalle) {
            $monto = formatMoney((float)$detalle['monto_pagado']);
            $filas .= "
                <tr>
                    <td>" . getMonthName((int)$detalle['mes']) . ' ' . $detalle['anio'] . "</td>
                    <td style='text-align: right;'>$monto</td>
                </tr>
            ";
        }

        $observaciones = '';
        if ($pago['observaciones']) {
            $observaciones = "
                <div style='margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;'>
                    <strong>Observaciones:</strong><br>
                    " . nl2br(htmlspecialchars($pago['observaciones'])) . "
                </div>
            ";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recibo de Pago</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 3px solid #667eea; padding-bottom: 15px; margin-bottom: 15px; }
                .header h1 { color: #667eea; margin: 0; font-size: 28px; }
                .header h2 { color: #333; margin: 10px 0 0 0; font-size: 18px; }
                .info-box { background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 5px; }
                .info-row { margin: 8px 0; }
                .label { font-weight: bold; color: #555; display: inline-block; width: 120px; }
                .value { color: #333; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th { background: #667eea; color: white; padding: 8px; text-align: left; }
                td { padding: 6px; border-bottom: 1px solid #ddd; }
                .total { font-size: 18px; font-weight: bold; color: #667eea; text-align: right; margin-top: 10px; }
                .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
                .signature { margin-top: 20px; text-align: center; }
                .signature-line { border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 5px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>RECIBO DE PAGO</h1>
                <h2>{$pago['numero_recibo']}</h2>
                <p style='color: #666; margin: 5px 0;'>Sistema $logo</p>
            </div>

            <div class='info-box'>
                <div class='info-row'>
                    <span class='label'>Comunidad:</span>
                    <span class='value'>" . htmlspecialchars($pago['comunidad_nombre']) . "</span>
                </div>
                <div class='info-row'>
                    <span class='label'>Dirección:</span>
                    <span class='value'>" . htmlspecialchars($pago['comunidad_direccion']) . "</span>
                </div>
                <div class='info-row'>
                    <span class='label'>Fecha:</span>
                    <span class='value'>$fecha</span>
                </div>
            </div>

            <div class='info-box'>
                <div class='info-row'>
                    <span class='label'>Propiedad:</span>
                    <span class='value' style='font-size: 16px;'><strong>" . htmlspecialchars($pago['propiedad_nombre']) . "</strong></span>
                </div>
                <div class='info-row'>
                    <span class='label'>Propietario:</span>
                    <span class='value'>" . htmlspecialchars($pago['nombre_dueno']) . "</span>
                </div>
                <div class='info-row'>
                    <span class='label'>Email:</span>
                    <span class='value'>" . htmlspecialchars($pago['email_dueno']) . "</span>
                </div>
            </div>

            <h3 style='color: #667eea; margin-top: 30px;'>Detalle de Pago</h3>
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
                        <th style='text-align: right;'>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    $filas
                </tbody>
            </table>

            <div class='total'>
                TOTAL PAGADO: $total
            </div>

            $observaciones

            <div class='signature'>
                $firmaHtml
                <div class='signature-line'>Firma y Sello</div>
                <p style='margin-top: 5px; color: #666;'>Administración de Gastos Comunes</p>
            </div>

            <div class='footer'>
                <p>Este documento es un comprobante de pago válido.</p>
                <p>Conserve este recibo para futuras consultas.</p>
                <p>Generado el " . date('d/m/Y H:i:s') . "</p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Genera el HTML para el cuerpo del email
     */
    private function generarEmailHtml(array $pago, array $mesesPagados, string $destinatario): string {
        $numeroRecibo = $this->pagoModel->generarNumeroRecibo($pago['id']);
        $fecha = formatDate($pago['fecha']);
        $total = formatMoney((float)$pago['monto']);
        
        $listaMeses = '<ul>';
        foreach ($mesesPagados as $mes) {
            $listaMeses .= "<li>$mes</li>";
        }
        $listaMeses .= '</ul>';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #667eea; }
                .total { font-size: 24px; font-weight: bold; color: #667eea; text-align: center; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                ul { padding-left: 20px; }
                li { margin: 5px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
<h1>RECIBO DE PAGO</h1>
                    <h2>$numeroRecibo</h2>
                </div>
                
                <div class='content'>
                    <p>Estimado/a <strong>$destinatario</strong>,</p>
                    
                    <p>Le informamos que hemos recibido el pago correspondiente a los gastos comunes de su propiedad <strong>" . htmlspecialchars($pago['propiedad_nombre']) . "</strong>.</p>
                    
                    <div class='info-box'>
                        <p><span class='label'>Comunidad:</span> " . htmlspecialchars($pago['comunidad_nombre']) . "</p>
                        <p><span class='label'>Fecha de pago:</span> $fecha</p>
                        <p><span class='label'>Períodos pagados:</span></p>
                        $listaMeses
                    </div>
                    
                    <div class='total'>
                        TOTAL PAGADO: $total
                    </div>
                    
                    <p>Agradecemos su puntualidad en los pagos.</p>
                    
                    <div class='footer'>
                        <p>Este es un correo automático del sistema de administración de gastos comunes.</p>
                        <p>Si tiene dudas, contacte a la administración.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Envía recibo por email usando configuración SMTP de la comunidad
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

        // Obtener configuración SMTP de la comunidad
        $config = $this->smtpConfigModel->getByComunidad($pago['comunidad_id']);
        
        if (!$config) {
            flash('error', 'No hay configuración SMTP para esta comunidad. Contacte al administrador.');
            redirect('pagos.php?action=recibo&id=' . $id);
        }

        // Determinar destinatario: prioridad al agente, si no al dueño
        $toEmail = !empty($pago['email_agente']) ? $pago['email_agente'] : $pago['email_dueno'];
        $toName = !empty($pago['nombre_agente']) ? $pago['nombre_agente'] : $pago['nombre_dueno'];
        
        if (empty($toEmail)) {
            flash('error', 'No hay email configurado ni para el agente ni para el dueño de la propiedad');
            redirect('pagos.php?action=recibo&id=' . $id);
        }

        // Preparar contenido del email
        $asunto = 'Recibo de Pago #' . $this->pagoModel->generarNumeroRecibo($id);
        $mesesPagados = [];
        foreach ($pago['detalles'] as $detalle) {
            $mesesPagados[] = getMonthName((int)$detalle['mes']) . ' ' . $detalle['anio'];
        }
        
        // Crear cuerpo HTML del email
        $bodyHtml = $this->generarEmailHtml($pago, $mesesPagados, $toName);
        
        try {
            // Cargar SwiftMailer
            require_once ROOT_PATH . '/vendor/autoload.php';
            
            // Crear transporte
            $encryption = $config['encryption'] === 'none' ? null : $config['encryption'];
            $transport = (new Swift_SmtpTransport($config['host'], $config['port'], $encryption))
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            $mailer = new Swift_Mailer($transport);
            
            // Crear mensaje
            $message = (new Swift_Message($asunto))
                ->setFrom([$config['from_email'] => $config['from_name']])
                ->setTo([$toEmail => $toName])
                ->setBody($bodyHtml, 'text/html')
                ->addPart(strip_tags($bodyHtml), 'text/plain');

            // Enviar
            $result = $mailer->send($message);
            
            if ($result > 0) {
                flash('success', 'Recibo enviado exitosamente a: ' . $toEmail);
            } else {
                flash('error', 'No se pudo enviar el correo. Verifique la configuración SMTP.');
            }
            
        } catch (Exception $e) {
            error_log('Error al enviar email: ' . $e->getMessage());
            flash('error', 'Error al enviar email: ' . $e->getMessage());
        }
        
        redirect('pagos.php?action=recibo&id=' . $id);
    }

    /**
     * Generar deudas mensuales para una comunidad
     */
    public function generarDeudas(): void {
        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        $mes = (int) ($_POST['mes'] ?? 0);
        $anio = (int) ($_POST['anio'] ?? 0);
        $aplicarSaldos = isset($_POST['aplicar_saldos']) && $_POST['aplicar_saldos'] == '1';
        
        if (!$comunidadId || !$mes || !$anio) {
            flash('error', 'Datos incompletos');
            redirect('pagos.php');
        }

        if ($aplicarSaldos) {
            // Generar deudas y aplicar saldos automáticamente
            $resultado = $this->deudaModel->generarDeudasMesConSaldo($comunidadId, $mes, $anio, true);
            $cantidad = $resultado['deudas_generadas'];
            $saldosAplicados = count($resultado['saldos_aplicados']);
            
            if ($cantidad > 0) {
                $mensaje = "Se generaron {$cantidad} deudas para " . getMonthName($mes) . " {$anio}";
                if ($saldosAplicados > 0) {
                    $mensaje .= ". Se aplicaron saldos automáticamente en {$saldosAplicados} propiedad(es)";
                }
                flash('success', $mensaje);
            } else {
                flash('warning', 'No se generaron nuevas deudas (puede que ya existan para este período)');
            }
        } else {
            // Generar deudas sin aplicar saldos
            $cantidad = $this->deudaModel->generarDeudasMes($comunidadId, $mes, $anio);
            
            if ($cantidad > 0) {
                flash('success', "Se generaron {$cantidad} deudas para " . getMonthName($mes) . " {$anio}");
            } else {
                flash('warning', 'No se generaron nuevas deudas (puede que ya existan para este período)');
            }
        }
        
        redirect('pagos.php');
    }

    /**
     * Muestra el formulario para editar un pago existente
     */
    public function edit(): void {
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

        // Obtener todas las deudas pendientes de la propiedad
        $deudasPendientes = $this->deudaModel->getPendientesByPropiedad($pago['propiedad_id']);
        
        // Obtener deudas ya pagadas en este pago
        $deudasPagadas = $pago['detalles'] ?? [];
        
        // Combinar para mostrar en el formulario
        $deudas = array_merge($deudasPagadas, $deudasPendientes);
        
        $title = 'Editar Pago #' . $pago['id'];
        require_once VIEWS_PATH . '/pagos/edit.php';
    }

    /**
     * Procesa la actualización de un pago
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('pagos.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('pagos.php');
        }

        $pagoId = (int) ($_POST['pago_id'] ?? 0);
        
        if (!$pagoId) {
            flash('error', 'ID de pago no válido');
            redirect('pagos.php');
        }

        // Obtener el pago actual
        $pagoActual = $this->pagoModel->getWithDetails($pagoId);
        if (!$pagoActual) {
            flash('error', 'Pago no encontrado');
            redirect('pagos.php');
        }

        $nuevasDeudasIds = $_POST['deudas'] ?? [];
        
        // Validar que las nuevas deudas existan y estén pendientes
        $deudasValidas = [];
        $montoTotal = 0;
        
        foreach ($nuevasDeudasIds as $deudaId) {
            $deuda = $this->deudaModel->getWithPropiedad((int) $deudaId);
            // Permitir deudas que ya estaban en este pago o que están pendientes
            $yaEstabaEnPago = false;
            foreach ($pagoActual['detalles'] as $detalle) {
                if ($detalle['deuda_id'] == $deudaId) {
                    $yaEstabaEnPago = true;
                    break;
                }
            }
            
            if ($deuda && ($deuda['estado'] === 'Pendiente' || $yaEstabaEnPago) && $deuda['propiedad_id'] == $pagoActual['propiedad_id']) {
                $deudasValidas[] = (int) $deudaId;
                $montoTotal += (float) $deuda['monto'];
            }
        }

        if (empty($deudasValidas)) {
            flash('error', 'No se encontraron deudas válidas');
            redirect('pagos.php?action=edit&id=' . $pagoId);
        }

        // Preparar datos actualizados
        $data = [
            'fecha' => $_POST['fecha'] ?? $pagoActual['fecha'],
            'monto' => $montoTotal,
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        // Actualizar el pago usando el modelo
        $actualizado = $this->pagoModel->update($pagoId, $data);
        
        if ($actualizado) {
            flash('success', 'Pago actualizado exitosamente. Total: ' . formatMoney($montoTotal));
            redirect('pagos.php?action=recibo&id=' . $pagoId);
        } else {
            flash('error', 'Error al actualizar el pago');
            redirect('pagos.php?action=edit&id=' . $pagoId);
        }
    }

    /**
     * Elimina un pago y revierte las deudas a estado Pendiente
     */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('pagos.php');
        }

        if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('pagos.php');
        }

        $pago = $this->pagoModel->getWithDetails($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('pagos.php');
        }

        $propiedadId = $pago['propiedad_id'];
        $db = getDB();

        try {
            $db->beginTransaction();

            // 1. Revertir las deudas a estado Pendiente
            foreach ($pago['detalles'] as $detalle) {
                $sql = "UPDATE deudas SET estado = 'Pendiente' WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':id' => $detalle['deuda_id']]);
            }

            // 2. Eliminar los detalles del pago
            $sql = "DELETE FROM pagos_detalle WHERE pago_id = :pago_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':pago_id' => $id]);

            // 3. Eliminar el pago
            $sql = "DELETE FROM pagos WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $db->commit();
            
            flash('success', 'Pago eliminado exitosamente. Las deudas asociadas han vuelto a estado Pendiente.');
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error al eliminar pago: ' . $e->getMessage());
            flash('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }

        redirect('pagos.php');
    }
}
