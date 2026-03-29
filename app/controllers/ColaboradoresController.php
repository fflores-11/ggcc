<?php
/**
 * Controlador de Colaboradores
 * Gestiona colaboradores y pagos a colaboradores
 */

class ColaboradoresController {
    private Colaborador $colaboradorModel;
    private PagoColaborador $pagoColaboradorModel;
    private Usuario $usuarioModel;

    public function __construct() {
        $this->colaboradorModel = new Colaborador();
        $this->pagoColaboradorModel = new PagoColaborador();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Lista todos los colaboradores
     */
    public function index(): void {
        // Paginación
        $pagination = getPaginationParams(20);
        $totalRecords = $this->colaboradorModel->countActive();
        $colaboradores = $this->colaboradorModel->getAllWithPagosCountPaginated($pagination['offset'], $pagination['perPage']);
        
        $title = 'Mantenedor de Colaboradores';
        $currentPage = $pagination['page'];
        $perPage = $pagination['perPage'];
        require_once VIEWS_PATH . '/colaboradores/index.php';
    }

    /**
     * Muestra formulario de creación
     */
    public function create(): void {
        // Crear colaborador vacío con valores por defecto
        $colaborador = [
            'id' => '',
            'tipo_colaborador' => $_GET['tipo'] ?? 'personal',
            'nombre' => '',
            'email' => '',
            'whatsapp' => '',
            'direccion' => '',
            'region' => '',
            'comuna' => '',
            'banco' => '',
            'tipo_cuenta' => 'vista',
            'numero_cuenta' => '',
            'numero_cliente' => '',
            'activo' => 1
        ];
        
        $title = 'Nuevo Colaborador';
        require_once VIEWS_PATH . '/colaboradores/form.php';
    }

    /**
     * Procesa la creación de colaborador
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('colaboradores.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('colaboradores.php');
        }

        $tipo = $_POST['tipo_colaborador'] ?? 'personal';
        
        if ($tipo === 'empresa') {
            // Datos para empresa
            $data = [
                'tipo_colaborador' => 'empresa',
                'nombre' => trim($_POST['nombre'] ?? ''),
                'numero_cliente' => trim($_POST['numero_cliente'] ?? ''),
                'email' => '',
                'whatsapp' => '',
                'direccion' => '',
                'region' => '',
                'comuna' => '',
                'banco' => '',
                'tipo_cuenta' => 'vista',
                'numero_cuenta' => '',
                'activo' => 1
            ];
        } else {
            // Datos para personal
            $data = [
                'tipo_colaborador' => 'personal',
                'nombre' => trim($_POST['nombre_personal'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'region' => trim($_POST['region'] ?? ''),
                'comuna' => trim($_POST['comuna'] ?? ''),
                'banco' => trim($_POST['banco'] ?? ''),
                'tipo_cuenta' => trim($_POST['tipo_cuenta'] ?? 'vista'),
                'numero_cuenta' => trim($_POST['numero_cuenta'] ?? ''),
                'numero_cliente' => null,
                'activo' => 1
            ];
        }

        $validation = $this->colaboradorModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('colaboradores.php?action=create');
        }

        $colaboradorId = $this->colaboradorModel->create($data);
        
        if ($colaboradorId) {
            $tipoMsg = $tipo === 'empresa' ? 'Empresa' : 'Colaborador';
            flash('success', $tipoMsg . ' creado exitosamente');
            redirect('colaboradores.php');
        } else {
            flash('error', 'Error al crear el colaborador');
            redirect('colaboradores.php?action=create');
        }
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de colaborador no válido');
            redirect('colaboradores.php');
        }

        $colaborador = $this->colaboradorModel->find($id);
        
        if (!$colaborador) {
            flash('error', 'Colaborador no encontrado');
            redirect('colaboradores.php');
        }

        // Si se pasa el parámetro tipo en la URL, cambiar el tipo del colaborador temporalmente
        $tipo = $_GET['tipo'] ?? null;
        if ($tipo && in_array($tipo, ['personal', 'empresa'])) {
            $colaborador['tipo_colaborador'] = $tipo;
            // Limpiar datos que no aplican al nuevo tipo
            if ($tipo === 'empresa') {
                $colaborador['email'] = '';
                $colaborador['whatsapp'] = '';
                $colaborador['direccion'] = '';
                $colaborador['region'] = '';
                $colaborador['comuna'] = '';
                $colaborador['banco'] = '';
                $colaborador['numero_cuenta'] = '';
            } else {
                $colaborador['numero_cliente'] = '';
            }
        }

        $title = 'Editar Colaborador';
        require_once VIEWS_PATH . '/colaboradores/form.php';
    }

    /**
     * Procesa la actualización
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('colaboradores.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('colaboradores.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de colaborador no válido');
            redirect('colaboradores.php');
        }

        $tipo = $_POST['tipo_colaborador'] ?? 'personal';
        
        if ($tipo === 'empresa') {
            // Datos para empresa
            $data = [
                'tipo_colaborador' => 'empresa',
                'nombre' => trim($_POST['nombre'] ?? ''),
                'numero_cliente' => trim($_POST['numero_cliente'] ?? ''),
                'email' => '',
                'whatsapp' => '',
                'direccion' => '',
                'region' => '',
                'comuna' => '',
                'banco' => '',
                'tipo_cuenta' => 'vista',
                'numero_cuenta' => '',
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
        } else {
            // Datos para personal
            $data = [
                'tipo_colaborador' => 'personal',
                'nombre' => trim($_POST['nombre_personal'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'region' => trim($_POST['region'] ?? ''),
                'comuna' => trim($_POST['comuna'] ?? ''),
                'banco' => trim($_POST['banco'] ?? ''),
                'tipo_cuenta' => trim($_POST['tipo_cuenta'] ?? 'vista'),
                'numero_cuenta' => trim($_POST['numero_cuenta'] ?? ''),
                'numero_cliente' => null,
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
        }

        $validation = $this->colaboradorModel->validate($data, $id);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('colaboradores.php?action=edit&id=' . $id);
        }

        $success = $this->colaboradorModel->update($id, $data);
        
        if ($success) {
            flash('success', 'Colaborador actualizado exitosamente');
            redirect('colaboradores.php');
        } else {
            flash('error', 'Error al actualizar el colaborador');
            redirect('colaboradores.php?action=edit&id=' . $id);
        }
    }

    /**
     * Elimina (desactiva) un colaborador
     */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de colaborador no válido');
            redirect('colaboradores.php');
        }

        // Verificar si tiene pagos
        $totalPagado = $this->pagoColaboradorModel->getTotalPagado($id);
        if ($totalPagado > 0) {
            flash('warning', 'No se puede eliminar el colaborador porque tiene pagos registrados. Se desactivará en su lugar.');
            $this->colaboradorModel->delete($id);
        } else {
            $success = $this->colaboradorModel->hardDelete($id);
            if ($success) {
                flash('success', 'Colaborador eliminado exitosamente');
            } else {
                flash('error', 'Error al eliminar el colaborador');
            }
        }
        
        redirect('colaboradores.php');
    }

    /**
     * Muestra el detalle de un colaborador con sus pagos
     */
    public function show(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de colaborador no válido');
            redirect('colaboradores.php');
        }

        $colaborador = $this->colaboradorModel->find($id);
        
        if (!$colaborador) {
            flash('error', 'Colaborador no encontrado');
            redirect('colaboradores.php');
        }

        $pagos = $this->pagoColaboradorModel->getByColaborador($id);
        $totalPagado = $this->pagoColaboradorModel->getTotalPagado($id);
        
        $title = 'Detalle de Colaborador';
        require_once VIEWS_PATH . '/colaboradores/show.php';
    }

    /**
     * Lista todos los pagos a colaboradores
     */
    public function pagos(): void {
        // Paginación
        $pagination = getPaginationParams(20);
        $totalRecords = $this->pagoColaboradorModel->countPagos();
        $pagos = $this->pagoColaboradorModel->getAllWithDetailsPaginated($pagination['offset'], $pagination['perPage']);
        $totalMesActual = $this->pagoColaboradorModel->getTotalMesActual();
        $colaboradores = $this->colaboradorModel->getForSelect();
        
        $title = 'Pagos a Colaboradores';
        $currentPage = $pagination['page'];
        $perPage = $pagination['perPage'];
        require_once VIEWS_PATH . '/colaboradores/pagos.php';
    }

    /**
     * Muestra formulario para registrar pago
     */
    public function createPago(): void {
        $colaboradorId = isset($_GET['colaborador_id']) ? (int) $_GET['colaborador_id'] : null;
        
        $colaboradores = $this->colaboradorModel->getForSelect();
        $colaborador = null;
        
        if ($colaboradorId) {
            $colaborador = $this->colaboradorModel->find($colaboradorId);
        }
        
        $title = 'Registrar Pago a Colaborador';
        require_once VIEWS_PATH . '/colaboradores/pago_form.php';
    }

    /**
     * Procesa el registro de pago
     */
    public function storePago(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('colaboradores.php?action=pagos');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('colaboradores.php?action=pagos');
        }

        $data = [
            'colaborador_id' => (int) ($_POST['colaborador_id'] ?? 0),
            'detalle' => trim($_POST['detalle'] ?? ''),
            'monto' => (float) ($_POST['monto'] ?? 0),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
            'pagado_por' => getUserId()
        ];

        $validation = $this->pagoColaboradorModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('colaboradores.php?action=createPago&colaborador_id=' . $data['colaborador_id']);
        }

        $pagoId = $this->pagoColaboradorModel->create($data);
        
        if ($pagoId) {
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imagenPath = $this->procesarImagenPago($_FILES['imagen']);
                if ($imagenPath) {
                    $this->pagoColaboradorModel->updateImagenPath($pagoId, $imagenPath);
                }
            }
            
            flash('success', 'Pago registrado exitosamente: ' . formatMoney($data['monto']));
            redirect('colaboradores.php?action=pagos');
        } else {
            flash('error', 'Error al registrar el pago');
            redirect('colaboradores.php?action=createPago&colaborador_id=' . $data['colaborador_id']);
        }
    }

    /**
     * Elimina un pago
     */
    public function deletePago(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        // Eliminar imagen física si existe
        $imagenPath = $this->pagoColaboradorModel->getImagenPath($id);
        if ($imagenPath && file_exists(PUBLIC_PATH . '/' . $imagenPath)) {
            @unlink(PUBLIC_PATH . '/' . $imagenPath);
        }

        $success = $this->pagoColaboradorModel->hardDelete($id);
        
        if ($success) {
            flash('success', 'Pago eliminado exitosamente');
        } else {
            flash('error', 'Error al eliminar el pago');
        }
        
        redirect('colaboradores.php?action=pagos');
    }

    /**
     * Muestra formulario para editar un pago
     */
    public function editPago(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        $pago = $this->pagoColaboradorModel->getWithImagen($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('colaboradores.php?action=pagos');
        }

        $colaborador = $this->colaboradorModel->find($pago['colaborador_id']);
        $colaboradores = $this->colaboradorModel->getForSelect();
        
        $title = 'Editar Pago #' . $id;
        require_once VIEWS_PATH . '/colaboradores/edit_pago.php';
    }

    /**
     * Procesa la actualización de un pago
     */
    public function updatePago(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('colaboradores.php?action=pagos');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('colaboradores.php?action=pagos');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        $pago = $this->pagoColaboradorModel->getWithImagen($id);
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('colaboradores.php?action=pagos');
        }

        $data = [
            'colaborador_id' => (int) ($_POST['colaborador_id'] ?? 0),
            'detalle' => trim($_POST['detalle'] ?? ''),
            'monto' => (float) ($_POST['monto'] ?? 0),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d')
        ];

        $validation = $this->pagoColaboradorModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('colaboradores.php?action=editPago&id=' . $id);
        }

        // Procesar nueva imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Eliminar imagen anterior si existe
            if (!empty($pago['imagen_path']) && file_exists(PUBLIC_PATH . '/' . $pago['imagen_path'])) {
                @unlink(PUBLIC_PATH . '/' . $pago['imagen_path']);
            }
            
            $imagenPath = $this->procesarImagenPago($_FILES['imagen']);
            if ($imagenPath) {
                $data['imagen_path'] = $imagenPath;
            }
        }

        $success = $this->pagoColaboradorModel->update($id, $data);
        
        if ($success) {
            flash('success', 'Pago actualizado exitosamente');
            redirect('colaboradores.php?action=pagos');
        } else {
            flash('error', 'Error al actualizar el pago');
            redirect('colaboradores.php?action=editPago&id=' . $id);
        }
    }

    /**
     * Genera PDF del recibo de pago
     */
    public function generarReciboPDF(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        $pago = $this->pagoColaboradorModel->getWithImagen($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('colaboradores.php?action=pagos');
        }

        // Obtener información adicional del colaborador
        $colaborador = $this->colaboradorModel->find($pago['colaborador_id']);
        
        // Obtener firma del administrador que registró el pago
        $firmaPath = null;
        if ($pago['pagado_por']) {
            $firmaPath = $this->usuarioModel->getFirmaPath($pago['pagado_por']);
            // Verificar que la firma existe físicamente
            if ($firmaPath && !file_exists(PUBLIC_PATH . '/' . $firmaPath)) {
                $firmaPath = null;
            }
        }
        
        // Generar número de recibo
        $numeroRecibo = 'RCB-' . str_pad($id, 6, '0', STR_PAD_LEFT) . '-' . date('Y');
        
        // Generar token de acceso para el PDF
        $token = hash('sha256', 'recibo_' . $id . '_ggcc_2024');
        
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
        
        // Crear HTML del recibo
        $html = $this->generarHTMLRecibo($pago, $colaborador, $numeroRecibo, $firmaPath, $token);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Descargar el PDF
        $filename = 'Recibo_' . $numeroRecibo . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Genera el HTML para el recibo
     * @param array $pago
     * @param array $colaborador
     * @param string $numeroRecibo
     * @param string|null $firmaPath
     * @param string $token
     * @return string
     */
    private function generarHTMLRecibo(array $pago, array $colaborador, string $numeroRecibo, ?string $firmaPath, string $token): string {
        $fecha = formatDate($pago['fecha']);
        $monto = formatMoney((float)$pago['monto']);
        
        // Determinar tipo de colaborador
        $isEmpresa = ($colaborador['tipo_colaborador'] ?? 'personal') === 'empresa';
        $tipoColaborador = $isEmpresa ? 'Empresa' : 'Personal';
        
        // Datos adicionales según tipo
        $datosExtra = '';
        if ($isEmpresa && !empty($colaborador['numero_cliente'])) {
            $datosExtra = '<tr>
                <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;" width="30%"><strong>N° Cliente:</strong></td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($colaborador['numero_cliente']) . '</td>
            </tr>';
        } elseif (!$isEmpresa) {
            if (!empty($colaborador['email'])) {
                $datosExtra .= '<tr>
                    <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;" width="30%"><strong>Email:</strong></td>
                    <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($colaborador['email']) . '</td>
                </tr>';
            }
            if (!empty($colaborador['whatsapp'])) {
                $datosExtra .= '<tr>
                    <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;" width="30%"><strong>Teléfono:</strong></td>
                    <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($colaborador['whatsapp']) . '</td>
                </tr>';
            }
        }
        
        // Preparar firma del administrador
        $firmaHtml = '';
        if ($firmaPath && file_exists(PUBLIC_PATH . '/' . $firmaPath)) {
            $firmaUrl = BASE_URL_FULL . $firmaPath;
            $firmaHtml = '<img src="' . $firmaUrl . '" alt="Firma Administrador" style="width: 300px; height: auto; object-fit: contain; margin-top: 80px; margin-bottom: 5px;">';
        }
        
        // Generar URL del PDF del recibo para el QR (con token de acceso)
        $reciboUrl = BASE_URL_FULL . 'colaboradores.php?action=generarReciboPDF&id=' . $pago['id'] . '&token=' . $token;
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($reciboUrl);
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .recibo { border: 2px solid #333; padding: 20px; position: relative; page-break-inside: avoid; }
        .qr-code { position: absolute; top: 15px; left: 15px; }
        .qr-code img { width: 80px; height: 80px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; margin-top: 10px; }
        .numero { font-size: 20px; font-weight: bold; color: #667eea; }
        .empresa { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .tipo-badge { display: inline-block; background-color: #667eea; color: white; padding: 4px 12px; border-radius: 15px; font-size: 11px; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
        td { padding: 6px; border: 1px solid #ddd; }
        .bg-light { background-color: #f8f9fa; }
        .monto { font-size: 24px; font-weight: bold; text-align: center; margin: 25px 0; padding: 15px; background-color: #f8f9fa; border: 2px solid #667eea; color: #667eea; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
        .firmas-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .firmas-table td { width: 50%; text-align: center; padding: 5px; vertical-align: bottom; border: none; }
        .firma-line { border-top: 1px solid #333; padding-top: 8px; margin-top: 8px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="recibo">
        <div class="qr-code">
            <img src="' . $qrCodeUrl . '" alt="QR Recibo">
        </div>
        
        <div class="header">
            <div class="empresa">' . APP_NAME . '</div>
            <div style="font-size: 12px; color: #666; margin-bottom: 8px;">COMPROBANTE DE PAGO A COLABORADOR</div>
            <div class="numero">RECIBO N° ' . $numeroRecibo . '</div>
            <div class="tipo-badge">' . $tipoColaborador . '</div>
        </div>
        
        <table>
            <tr>
                <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;" width="30%"><strong>Colaborador:</strong></td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($colaborador['nombre']) . '</td>
            </tr>
            ' . $datosExtra . '
            <tr>
                <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;"><strong>Fecha de Pago:</strong></td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . $fecha . '</td>
            </tr>
            <tr>
                <td style="padding: 6px; border: 1px solid #ddd; background-color: #f8f9fa;"><strong>Concepto:</strong></td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($pago['detalle']) . '</td>
            </tr>
        </table>
        
        <div class="monto">
            TOTAL: ' . $monto . '
        </div>
        
        <table class="firmas-table">
            <tr>
                <td style="padding-top: 40px;">
                    <div class="firma-line">Firma Colaborador</div>
                </td>
                <td style="padding-top: 10px;">
                    ' . $firmaHtml . '
                    <div class="firma-line">Firma y Sello Administración</div>
                </td>
            </tr>
        </table>
        
        <div class="footer">
            <p>Este documento es un comprobante de pago generado por el ' . APP_NAME . '.</p>
            <p>Fecha de emisión: ' . date('d/m/Y H:i:s') . ' | Registrado por: ' . htmlspecialchars($pago['pagado_por_nombre'] ?? 'Sistema') . '</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Procesa la imagen de un pago
     * @param array $file
     * @return string|false
     */
    private function procesarImagenPago(array $file): string|false {
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
            ];
            $errorMsg = $errorMessages[$file['error']] ?? 'Error desconocido al subir el archivo';
            error_log("Error upload pago colaborador: " . $errorMsg);
            return false;
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Tipo de archivo no permitido: ' . $file['type'] . '. Use JPEG, PNG, GIF o PDF.');
            return false;
        }

        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            flash('error', 'El archivo no debe superar los 5MB');
            return false;
        }

        // Crear directorio si no existe
        $uploadDir = PUBLIC_PATH . '/assets/images/pagos_colaboradores/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'pago_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'assets/images/pagos_colaboradores/' . $filename;
        } else {
            error_log("Error al mover archivo: " . $file['tmp_name'] . " a " . $filepath);
            flash('error', 'Error al guardar el archivo. Verifique los permisos del directorio.');
        }

        return false;
    }

    /**
     * Muestra la imagen/boleta de un pago
     */
    public function verImagen(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        $pago = $this->pagoColaboradorModel->getWithImagen($id);
        
        if (!$pago) {
            flash('error', 'Pago no encontrado');
            redirect('colaboradores.php?action=pagos');
        }

        $title = 'Boleta/Recibo del Pago #' . $id;
        require_once VIEWS_PATH . '/colaboradores/ver_imagen.php';
    }

    /**
     * Elimina la imagen de un pago
     */
    public function eliminarImagen(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de pago no válido');
            redirect('colaboradores.php?action=pagos');
        }

        $imagenPath = $this->pagoColaboradorModel->getImagenPath($id);
        
        if ($imagenPath && file_exists(PUBLIC_PATH . '/' . $imagenPath)) {
            @unlink(PUBLIC_PATH . '/' . $imagenPath);
        }
        
        $this->pagoColaboradorModel->deleteImagen($id);
        flash('success', 'Imagen eliminada exitosamente');
        
        redirect('colaboradores.php?action=pagos');
    }
}
