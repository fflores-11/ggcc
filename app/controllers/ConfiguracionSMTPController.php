<?php
/**
 * Controlador de Configuración SMTP
 * Solo accesible para super usuario (rol: admin)
 */

class ConfiguracionSMTPController {
    private ConfiguracionSMTP $smtpModel;
    private Comunidad $comunidadModel;

    public function __construct() {
        $this->smtpModel = new ConfiguracionSMTP();
        $this->comunidadModel = new Comunidad();
    }

    /**
     * Verifica que el usuario sea admin
     */
    private function requireAdmin(): void {
        if (!hasRole('admin')) {
            flash('error', 'Acceso denegado. Solo super usuarios pueden configurar SMTP.');
            redirect('index.php');
        }
    }

    /**
     * Lista todas las configuraciones SMTP
     */
    public function index(): void {
        $this->requireAdmin();
        
        $configuraciones = $this->smtpModel->getAllWithComunidad();
        $comunidadesSinConfig = $this->smtpModel->getComunidadesSinConfig();
        
        $title = 'Configuración SMTP';
        require_once VIEWS_PATH . '/configuracion_smtp/index.php';
    }

    /**
     * Muestra formulario para crear/editar configuración
     */
    public function create(): void {
        $this->requireAdmin();
        
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        
        $comunidades = $this->comunidadModel->getForSelect();
        $config = null;
        $comunidad = null;
        
        if ($comunidadId) {
            $config = $this->smtpModel->getByComunidad($comunidadId);
            $comunidad = $this->comunidadModel->find($comunidadId);
        }
        
        $title = $config ? 'Editar Configuración SMTP' : 'Nueva Configuración SMTP';
        require_once VIEWS_PATH . '/configuracion_smtp/form.php';
    }

    /**
     * Guarda configuración SMTP
     */
    public function store(): void {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion_smtp.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('configuracion_smtp.php');
        }

        $data = [
            'comunidad_id' => (int) ($_POST['comunidad_id'] ?? 0),
            'host' => trim($_POST['host'] ?? ''),
            'port' => (int) ($_POST['port'] ?? 587),
            'username' => trim($_POST['username'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'encryption' => $_POST['encryption'] ?? 'tls',
            'from_email' => trim($_POST['from_email'] ?? ''),
            'from_name' => trim($_POST['from_name'] ?? ''),
            'activo' => 1
        ];

        $validation = $this->smtpModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('configuracion_smtp.php?action=create&comunidad_id=' . $data['comunidad_id']);
        }

        $configId = $this->smtpModel->save($data);
        
        if ($configId) {
            flash('success', 'Configuración SMTP guardada exitosamente');
            redirect('configuracion_smtp.php');
        } else {
            flash('error', 'Error al guardar la configuración SMTP');
            redirect('configuracion_smtp.php?action=create&comunidad_id=' . $data['comunidad_id']);
        }
    }

    /**
     * Elimina configuración SMTP (desactiva)
     */
    public function delete(): void {
        $this->requireAdmin();
        
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de configuración no válido');
            redirect('configuracion_smtp.php');
        }

        $success = $this->smtpModel->delete($id);
        
        if ($success) {
            flash('success', 'Configuración SMTP eliminada exitosamente');
        } else {
            flash('error', 'Error al eliminar la configuración SMTP');
        }
        
        redirect('configuracion_smtp.php');
    }

    /**
     * Prueba conexión SMTP
     */
    public function test(): void {
        $this->requireAdmin();
        
        header('Content-Type: application/json');
        
        $comunidadId = (int) ($_POST['comunidad_id'] ?? 0);
        
        if (!$comunidadId) {
            echo json_encode(['success' => false, 'message' => 'ID de comunidad requerido']);
            exit;
        }

        $config = $this->smtpModel->getByComunidad($comunidadId);
        
        if (!$config) {
            echo json_encode(['success' => false, 'message' => 'No hay configuración SMTP para esta comunidad']);
            exit;
        }

        // Aquí iría la prueba real de conexión SMTP
        // Por ahora simulamos éxito
        echo json_encode([
            'success' => true, 
            'message' => 'Configuración SMTP válida (prueba de conexión pendiente de implementación con SwiftMailer)'
        ]);
        exit;
    }
}
