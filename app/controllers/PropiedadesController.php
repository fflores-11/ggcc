<?php
/**
 * Controlador de Propiedades
 * CRUD completo de propiedades
 */

class PropiedadesController {
    private Propiedad $propiedadModel;
    private Comunidad $comunidadModel;
    private Deuda $deudaModel;

    public function __construct() {
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
        $this->deudaModel = new Deuda();
    }

    /**
     * Lista todas las propiedades
     */
    public function index(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        
        if ($comunidadId) {
            $propiedades = $this->propiedadModel->getByComunidad($comunidadId);
            $comunidad = $this->comunidadModel->find($comunidadId);
            $title = 'Propiedades de ' . ($comunidad['nombre'] ?? 'Comunidad');
        } else {
            $propiedades = $this->propiedadModel->getAllWithComunidad();
            $title = 'Mantenedor de Propiedades';
        }
        
        $comunidades = $this->comunidadModel->getForSelect();
        require_once VIEWS_PATH . '/propiedades/index.php';
    }

    /**
     * Muestra el detalle de una propiedad
     */
    public function show(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $propiedad = $this->propiedadModel->getWithDeudas($id);
        
        if (!$propiedad) {
            flash('error', 'Propiedad no encontrada');
            redirect('propiedades.php');
        }

        $pagos = $this->propiedadModel->getPaymentHistory($id, 10);
        
        $title = 'Detalle de Propiedad';
        require_once VIEWS_PATH . '/propiedades/show.php';
    }

    /**
     * Muestra formulario de creación
     */
    public function create(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $comunidades = $this->comunidadModel->getForSelect();
        
        $title = 'Nueva Propiedad';
        require_once VIEWS_PATH . '/propiedades/form.php';
    }

    /**
     * Procesa la creación de propiedad
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('propiedades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('propiedades.php');
        }

        $data = [
            'comunidad_id' => (int) ($_POST['comunidad_id'] ?? 0),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'Casa',
            'precio_gastos_comunes' => (float) ($_POST['precio_gastos_comunes'] ?? 0),
            'nombre_dueno' => trim($_POST['nombre_dueno'] ?? ''),
            'email_dueno' => trim($_POST['email_dueno'] ?? ''),
            'whatsapp_dueno' => trim($_POST['whatsapp_dueno'] ?? ''),
            'nombre_agente' => trim($_POST['nombre_agente'] ?? ''),
            'email_agente' => trim($_POST['email_agente'] ?? ''),
            'whatsapp_agente' => trim($_POST['whatsapp_agente'] ?? ''),
            'activo' => 1
        ];

        $validation = $this->propiedadModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('propiedades.php?action=create&comunidad_id=' . $data['comunidad_id']);
        }

        $propiedadId = $this->propiedadModel->create($data);
        
        if ($propiedadId) {
            // Generar deudas automáticamente para períodos existentes
            $deudasGeneradas = $this->deudaModel->crearDeudasNuevaPropiedad(
                $propiedadId, 
                $data['precio_gastos_comunes'], 
                $data['comunidad_id']
            );
            
            $mensaje = 'Propiedad creada exitosamente';
            if ($deudasGeneradas > 0) {
                $mensaje .= '. Se generaron automáticamente ' . $deudasGeneradas . ' deuda(s) por los períodos existentes de la comunidad.';
            }
            
            flash('success', $mensaje);
            redirect('propiedades.php?comunidad_id=' . $data['comunidad_id']);
        } else {
            flash('error', 'Error al crear la propiedad');
            redirect('propiedades.php?action=create&comunidad_id=' . $data['comunidad_id']);
        }
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $propiedad = $this->propiedadModel->find($id);
        
        if (!$propiedad) {
            flash('error', 'Propiedad no encontrada');
            redirect('propiedades.php');
        }

        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Editar Propiedad';
        require_once VIEWS_PATH . '/propiedades/form.php';
    }

    /**
     * Procesa la actualización de propiedad
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('propiedades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('propiedades.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $data = [
            'comunidad_id' => (int) ($_POST['comunidad_id'] ?? 0),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'Casa',
            'precio_gastos_comunes' => (float) ($_POST['precio_gastos_comunes'] ?? 0),
            'nombre_dueno' => trim($_POST['nombre_dueno'] ?? ''),
            'email_dueno' => trim($_POST['email_dueno'] ?? ''),
            'whatsapp_dueno' => trim($_POST['whatsapp_dueno'] ?? ''),
            'nombre_agente' => trim($_POST['nombre_agente'] ?? ''),
            'email_agente' => trim($_POST['email_agente'] ?? ''),
            'whatsapp_agente' => trim($_POST['whatsapp_agente'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        $validation = $this->propiedadModel->validate($data, $id);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('propiedades.php?action=edit&id=' . $id);
        }

        $success = $this->propiedadModel->update($id, $data);
        
        if ($success) {
            flash('success', 'Propiedad actualizada exitosamente');
            redirect('propiedades.php?comunidad_id=' . $data['comunidad_id']);
        } else {
            flash('error', 'Error al actualizar la propiedad');
            redirect('propiedades.php?action=edit&id=' . $id);
        }
    }

    /**
     * Elimina (desactiva) una propiedad
     */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $success = $this->propiedadModel->delete($id);
        
        if ($success) {
            flash('success', 'Propiedad eliminada exitosamente');
        } else {
            flash('error', 'Error al eliminar la propiedad');
        }
        
        redirect('propiedades.php');
    }

    /**
     * Reactiva una propiedad desactivada
     */
    public function restore(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $success = $this->propiedadModel->restore($id);
        
        if ($success) {
            flash('success', 'Propiedad reactivada exitosamente');
        } else {
            flash('error', 'Error al reactivar la propiedad');
        }
        
        redirect('propiedades.php');
    }

    /**
     * API: Obtiene propiedades por comunidad (para selects dependientes)
     */
    public function apiListByComunidad(): void {
        header('Content-Type: application/json');
        
        $comunidadId = (int) ($_GET['comunidad_id'] ?? 0);
        
        if (!$comunidadId) {
            echo json_encode(['success' => false, 'message' => 'ID de comunidad requerido']);
            exit;
        }
        
        $propiedades = $this->propiedadModel->getForSelect($comunidadId);
        echo json_encode(['success' => true, 'data' => $propiedades]);
        exit;
    }
}
