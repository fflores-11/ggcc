<?php
/**
 * Controlador de Propiedades
 * CRUD completo de propiedades
 */

class PropiedadesController {
    private Propiedad $propiedadModel;
    private Comunidad $comunidadModel;
    private Deuda $deudaModel;
    private Mascota $mascotaModel;

    public function __construct() {
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
        $this->deudaModel = new Deuda();
        $this->mascotaModel = new Mascota();
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
        $mascotas = $this->mascotaModel->getByPropiedad($id);
        
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

    /**
     * Agrega una nueva mascota a una propiedad (Admin)
     */
    public function agregarMascota(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('propiedades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('propiedades.php');
        }

        $propiedadId = (int)($_POST['propiedad_id'] ?? 0);
        
        if (!$propiedadId) {
            flash('error', 'ID de propiedad no válido');
            redirect('propiedades.php');
        }

        $data = [
            'propiedad_id' => $propiedadId,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'tipo' => trim($_POST['tipo'] ?? ''),
            'edad' => !empty($_POST['edad']) ? (int)$_POST['edad'] : 0,
            'alimento' => trim($_POST['alimento'] ?? '')
        ];

        // Validar datos
        $validation = $this->mascotaModel->validate($data);
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('propiedades.php?action=show&id=' . $propiedadId);
        }

        // Procesar imagen si se subió
        $imagenPath = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagenPath = $this->procesarImagenMascota($_FILES['imagen']);
            if ($imagenPath !== false) {
                $data['imagen_path'] = $imagenPath;
            }
        }

        $mascotaId = $this->mascotaModel->create($data);
        
        if ($mascotaId) {
            $msg = 'Mascota agregada exitosamente';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE && $imagenPath === false) {
                $msg .= ' (sin imagen)';
            }
            flash('success', $msg);
        } else {
            flash('error', 'Error al agregar la mascota');
        }
        
        redirect('propiedades.php?action=show&id=' . $propiedadId);
    }

    /**
     * Actualiza una mascota existente (Admin)
     */
    public function actualizarMascota(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('propiedades.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('propiedades.php');
        }

        $mascotaId = (int)($_POST['mascota_id'] ?? 0);
        $propiedadId = (int)($_POST['propiedad_id'] ?? 0);
        
        if (!$mascotaId) {
            flash('error', 'ID de mascota no válido');
            redirect('propiedades.php');
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'tipo' => trim($_POST['tipo'] ?? ''),
            'edad' => !empty($_POST['edad']) ? (int)$_POST['edad'] : 0,
            'alimento' => trim($_POST['alimento'] ?? '')
        ];

        // Validar datos
        $validation = $this->mascotaModel->validate($data);
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('propiedades.php?action=show&id=' . $propiedadId);
        }

        // Procesar imagen si se subió una nueva
        if (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
            // Eliminar imagen anterior
            $this->mascotaModel->deleteImagen($mascotaId);
            
            $imagenPath = $this->procesarImagenMascota($_FILES['imagen']);
            if ($imagenPath !== false) {
                $data['imagen_path'] = $imagenPath;
            }
        }

        $success = $this->mascotaModel->update($mascotaId, $data);
        
        if ($success) {
            flash('success', 'Mascota actualizada exitosamente');
        } else {
            flash('error', 'Error al actualizar la mascota');
        }
        
        redirect('propiedades.php?action=show&id=' . $propiedadId);
    }

    /**
     * Elimina (desactiva) una mascota (Admin)
     */
    public function eliminarMascota(): void {
        $mascotaId = (int)($_GET['id'] ?? 0);
        $propiedadId = (int)($_GET['propiedad_id'] ?? 0);
        
        if (!$mascotaId) {
            flash('error', 'ID de mascota no válido');
            redirect('propiedades.php');
        }

        // Eliminar imagen física
        $this->mascotaModel->deleteImagen($mascotaId);

        $success = $this->mascotaModel->delete($mascotaId);
        
        if ($success) {
            flash('success', 'Mascota eliminada exitosamente');
        } else {
            flash('error', 'Error al eliminar la mascota');
        }
        
        redirect('propiedades.php?action=show&id=' . $propiedadId);
    }

    /**
     * Procesa la imagen de una mascota
     * @param array|null $file
     * @return string|false
     */
    private function procesarImagenMascota(?array $file): string|false {
        if (!$file) {
            return false;
        }
        
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
            $errorMsg = $errorMessages[$file['error']] ?? 'Error desconocido al subir el archivo (' . $file['error'] . ')';
            error_log("Error upload mascota: " . $errorMsg);
            return false;
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Tipo de archivo no permitido: ' . $file['type'] . '. Use JPEG, PNG o GIF.');
            return false;
        }

        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            flash('error', 'La imagen no debe superar los 5MB');
            return false;
        }

        // Crear directorio si no existe
        $uploadDir = PUBLIC_PATH . '/assets/images/mascotas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'mascota_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'assets/images/mascotas/' . $filename;
        } else {
            error_log("Error al mover archivo: " . $file['tmp_name'] . " a " . $filepath);
            flash('error', 'Error al guardar la imagen. Verifique los permisos del directorio.');
        }

        return false;
    }
}
