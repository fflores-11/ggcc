<?php
/**
 * Controlador de Colaboradores
 * Gestiona colaboradores y pagos a colaboradores
 */

class ColaboradoresController {
    private Colaborador $colaboradorModel;
    private PagoColaborador $pagoColaboradorModel;

    public function __construct() {
        $this->colaboradorModel = new Colaborador();
        $this->pagoColaboradorModel = new PagoColaborador();
    }

    /**
     * Lista todos los colaboradores
     */
    public function index(): void {
        $colaboradores = $this->colaboradorModel->getAllWithPagosCount();
        $title = 'Mantenedor de Colaboradores';
        require_once VIEWS_PATH . '/colaboradores/index.php';
    }

    /**
     * Muestra formulario de creación de colaborador
     */
    public function create(): void {
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
        $pagos = $this->pagoColaboradorModel->getAllWithDetails();
        $totalMesActual = $this->pagoColaboradorModel->getTotalMesActual();
        $colaboradores = $this->colaboradorModel->getForSelect();
        
        $title = 'Pagos a Colaboradores';
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

        $success = $this->pagoColaboradorModel->hardDelete($id);
        
        if ($success) {
            flash('success', 'Pago eliminado exitosamente');
        } else {
            flash('error', 'Error al eliminar el pago');
        }
        
        redirect('colaboradores.php?action=pagos');
    }
}
