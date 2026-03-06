<?php
/**
 * Controlador de Comunidades
 * CRUD completo de comunidades
 */

class ComunidadesController {
    private Comunidad $comunidadModel;
    private Propiedad $propiedadModel;

    public function __construct() {
        $this->comunidadModel = new Comunidad();
        $this->propiedadModel = new Propiedad();
    }

    /**
     * Lista todas las comunidades
     */
    public function index(): void {
        $comunidades = $this->comunidadModel->getWithPropertyCount();
        $title = 'Mantenedor de Comunidades';
        require_once VIEWS_PATH . '/comunidades/index.php';
    }

    /**
     * Muestra el detalle de una comunidad con sus propiedades
     */
    public function show(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de comunidad no válido');
            redirect('comunidades.php');
        }

        $comunidad = $this->comunidadModel->findActive($id);
        
        if (!$comunidad) {
            flash('error', 'Comunidad no encontrada');
            redirect('comunidades.php');
        }

        $propiedades = $this->propiedadModel->getByComunidad($id);
        $resumen = $this->comunidadModel->getDebtSummary($id);
        
        $title = 'Detalle de Comunidad';
        require_once VIEWS_PATH . '/comunidades/show.php';
    }

    /**
     * Muestra formulario de creación
     */
    public function create(): void {
        $title = 'Nueva Comunidad';
        require_once VIEWS_PATH . '/comunidades/form.php';
    }

    /**
     * Procesa la creación de comunidad
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('comunidades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('comunidades.php');
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'pais' => trim($_POST['pais'] ?? 'Chile'),
            'region' => trim($_POST['region'] ?? ''),
            'comuna' => trim($_POST['comuna'] ?? ''),
            'nombre_presidente' => trim($_POST['nombre_presidente'] ?? ''),
            'whatsapp_presidente' => trim($_POST['whatsapp_presidente'] ?? ''),
            'email_presidente' => trim($_POST['email_presidente'] ?? ''),
            'activo' => 1
        ];

        $validation = $this->comunidadModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('comunidades.php?action=create');
        }

        $comunidadId = $this->comunidadModel->create($data);
        
        if ($comunidadId) {
            flash('success', 'Comunidad creada exitosamente');
            redirect('comunidades.php');
        } else {
            flash('error', 'Error al crear la comunidad');
            redirect('comunidades.php?action=create');
        }
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de comunidad no válido');
            redirect('comunidades.php');
        }

        $comunidad = $this->comunidadModel->find($id);
        
        if (!$comunidad) {
            flash('error', 'Comunidad no encontrada');
            redirect('comunidades.php');
        }

        $title = 'Editar Comunidad';
        require_once VIEWS_PATH . '/comunidades/form.php';
    }

    /**
     * Procesa la actualización de comunidad
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('comunidades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('comunidades.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de comunidad no válido');
            redirect('comunidades.php');
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'pais' => trim($_POST['pais'] ?? 'Chile'),
            'region' => trim($_POST['region'] ?? ''),
            'comuna' => trim($_POST['comuna'] ?? ''),
            'nombre_presidente' => trim($_POST['nombre_presidente'] ?? ''),
            'whatsapp_presidente' => trim($_POST['whatsapp_presidente'] ?? ''),
            'email_presidente' => trim($_POST['email_presidente'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        $validation = $this->comunidadModel->validate($data, $id);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('comunidades.php?action=edit&id=' . $id);
        }

        $success = $this->comunidadModel->update($id, $data);
        
        if ($success) {
            flash('success', 'Comunidad actualizada exitosamente');
            redirect('comunidades.php');
        } else {
            flash('error', 'Error al actualizar la comunidad');
            redirect('comunidades.php?action=edit&id=' . $id);
        }
    }

    /**
     * Elimina (desactiva) una comunidad
     */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de comunidad no válido');
            redirect('comunidades.php');
        }

        $success = $this->comunidadModel->delete($id);
        
        if ($success) {
            flash('success', 'Comunidad eliminada exitosamente');
        } else {
            flash('error', 'Error al eliminar la comunidad');
        }
        
        redirect('comunidades.php');
    }

    /**
     * Reactiva una comunidad desactivada
     */
    public function restore(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de comunidad no válido');
            redirect('comunidades.php');
        }

        $success = $this->comunidadModel->restore($id);
        
        if ($success) {
            flash('success', 'Comunidad reactivada exitosamente');
        } else {
            flash('error', 'Error al reactivar la comunidad');
        }
        
        redirect('comunidades.php');
    }

    /**
     * API: Obtiene comunidades para select dinámico
     */
    public function apiList(): void {
        header('Content-Type: application/json');
        $comunidades = $this->comunidadModel->getForSelect();
        echo json_encode(['success' => true, 'data' => $comunidades]);
        exit;
    }
}
