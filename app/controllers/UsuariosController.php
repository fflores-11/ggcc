<?php
/**
 * Controlador de Usuarios
 * CRUD completo de usuarios del sistema
 */

class UsuariosController {
    private Usuario $userModel;

    public function __construct() {
        $this->userModel = new Usuario();
    }

    /**
     * Lista todos los usuarios
     */
    public function index(): void {
        // Paginación
        $pagination = getPaginationParams(20);
        
        // Super admin ve todos los usuarios, administradores solo los de su comunidad
        if (getUserRole() === 'admin') {
            $totalRecords = $this->userModel->countByComunidad();
            $usuarios = $this->userModel->getAllWithComunidadPaginated($pagination['offset'], $pagination['perPage']);
        } else {
            $comunidadId = getUserComunidadId();
            if ($comunidadId) {
                $totalRecords = $this->userModel->countByComunidad($comunidadId);
                $usuarios = $this->userModel->getByComunidadPaginated($comunidadId, $pagination['offset'], $pagination['perPage']);
            } else {
                $totalRecords = 0;
                $usuarios = [];
            }
        }
        
        $title = 'Mantenedor de Usuarios';
        $currentPage = $pagination['page'];
        $perPage = $pagination['perPage'];
        require_once VIEWS_PATH . '/usuarios/index.php';
    }

    /**
     * Muestra formulario de creación
     */
    public function create(): void {
        $title = 'Nuevo Usuario';
        require_once VIEWS_PATH . '/usuarios/form.php';
    }

    /**
     * Procesa la creación de usuario
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('usuarios.php');
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'rol' => $_POST['rol'] ?? 'administrador',
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        // Agregar comunidad_id para administradores y presidentes
        if (in_array($data['rol'], ['administrador', 'presidente'])) {
            $data['comunidad_id'] = !empty($_POST['comunidad_id']) ? (int)$_POST['comunidad_id'] : null;
        } else {
            $data['comunidad_id'] = null; // Super admin no tiene comunidad asignada
        }

        $validation = $this->userModel->validate($data);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('usuarios.php?action=create');
        }

        $userId = $this->userModel->create($data);
        
        if ($userId) {
            flash('success', 'Usuario creado exitosamente');
            redirect('usuarios.php');
        } else {
            flash('error', 'Error al crear el usuario');
            redirect('usuarios.php?action=create');
        }
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        $usuario = $this->userModel->find($id);
        
        if (!$usuario) {
            flash('error', 'Usuario no encontrado');
            redirect('usuarios.php');
        }

        $title = 'Editar Usuario';
        require_once VIEWS_PATH . '/usuarios/form.php';
    }

    /**
     * Procesa la actualización de usuario
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('usuarios.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        // No permitir desactivarse a sí mismo
        if ($id === getUserId() && !isset($_POST['activo'])) {
            flash('error', 'No puede desactivar su propia cuenta');
            redirect('usuarios.php?action=edit&id=' . $id);
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'rol' => $_POST['rol'] ?? 'administrador',
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        // Agregar comunidad_id para administradores y presidentes
        if (in_array($data['rol'], ['administrador', 'presidente'])) {
            $data['comunidad_id'] = !empty($_POST['comunidad_id']) ? (int)$_POST['comunidad_id'] : null;
        } else {
            $data['comunidad_id'] = null; // Super admin no tiene comunidad asignada
        }

        // Solo actualizar password si se proporciona
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        $validation = $this->userModel->validate($data, $id);
        
        if (!$validation['valid']) {
            flash('error', implode('<br>', $validation['errors']));
            redirect('usuarios.php?action=edit&id=' . $id);
        }

        $success = $this->userModel->update($id, $data);
        
        if ($success) {
            flash('success', 'Usuario actualizado exitosamente');
            redirect('usuarios.php');
        } else {
            flash('error', 'Error al actualizar el usuario');
            redirect('usuarios.php?action=edit&id=' . $id);
        }
    }

    /**
     * Elimina (desactiva) un usuario
     */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        // No permitir eliminarse a sí mismo
        if ($id === getUserId()) {
            flash('error', 'No puede eliminar su propia cuenta');
            redirect('usuarios.php');
        }

        $success = $this->userModel->delete($id);
        
        if ($success) {
            flash('success', 'Usuario eliminado exitosamente');
        } else {
            flash('error', 'Error al eliminar el usuario');
        }
        
        redirect('usuarios.php');
    }

    /**
     * Reactiva un usuario desactivado
     */
    public function restore(): void {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        $success = $this->userModel->restore($id);
        
        if ($success) {
            flash('success', 'Usuario reactivado exitosamente');
        } else {
            flash('error', 'Error al reactivar el usuario');
        }
        
        redirect('usuarios.php');
    }

    /**
     * Procesa la subida de imagen de firma del usuario
     */
    public function subirFirma(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('usuarios.php');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        
        if (!$userId) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        // Verificar que el usuario existe
        $usuario = $this->userModel->find($userId);
        if (!$usuario) {
            flash('error', 'Usuario no encontrado');
            redirect('usuarios.php');
        }

        // Verificar permisos: solo puede subir firma el mismo usuario o un admin
        $currentUserId = getUserId();
        $currentUserRole = getUserRole();
        
        if ($currentUserId !== $userId && $currentUserRole !== 'admin') {
            flash('error', 'No tiene permisos para subir firma de este usuario');
            redirect('usuarios.php');
        }

        // Verificar si se subió un archivo
        if (!isset($_FILES['firma']) || $_FILES['firma']['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'No se seleccionó ningún archivo');
            redirect('usuarios.php?action=edit&id=' . $userId);
        }

        $file = $_FILES['firma'];

        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Error al subir el archivo: ' . $this->getUploadError($file['error']));
            redirect('usuarios.php?action=edit&id=' . $userId);
        }

        // Validar tipo de archivo (solo imágenes)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            flash('error', 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WEBP)');
            redirect('usuarios.php?action=edit&id=' . $userId);
        }

        // Validar tamaño (máximo 2MB)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            flash('error', 'El archivo es demasiado grande. Máximo 2MB');
            redirect('usuarios.php?action=edit&id=' . $userId);
        }

        // Crear directorio si no existe
        $uploadDir = ROOT_PATH . '/public/assets/images/firmas/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                flash('error', 'Error al crear el directorio de firmas');
                redirect('usuarios.php?action=edit&id=' . $userId);
            }
        }

        // Generar nombre único
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = 'firma_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;

        // Eliminar firma anterior si existe
        $oldFirmaPath = $this->userModel->getFirmaPath($userId);
        if ($oldFirmaPath) {
            $oldFullPath = ROOT_PATH . '/public/' . $oldFirmaPath;
            if (file_exists($oldFullPath) && strpos(basename($oldFullPath), 'firma_') === 0) {
                @unlink($oldFullPath);
            }
        }

        // Mover el archivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $relativePath = 'assets/images/firmas/' . $newFileName;
            
            if ($this->userModel->updateFirmaPath($userId, $relativePath)) {
                flash('success', 'Firma subida exitosamente');
            } else {
                @unlink($uploadPath);
                flash('error', 'Error al guardar la firma en la base de datos');
            }
        } else {
            flash('error', 'Error al mover el archivo de firma');
        }

        redirect('usuarios.php?action=edit&id=' . $userId);
    }

    /**
     * Elimina la firma del usuario
     */
    public function eliminarFirma(): void {
        $userId = (int) ($_GET['id'] ?? 0);
        
        if (!$userId) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios.php');
        }

        // Verificar permisos
        $currentUserId = getUserId();
        $currentUserRole = getUserRole();
        
        if ($currentUserId !== $userId && $currentUserRole !== 'admin') {
            flash('error', 'No tiene permisos para eliminar la firma de este usuario');
            redirect('usuarios.php');
        }

        $firmaPath = $this->userModel->getFirmaPath($userId);
        
        if ($firmaPath) {
            $fullPath = ROOT_PATH . '/public/' . $firmaPath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            
            $this->userModel->updateFirmaPath($userId, '');
            flash('success', 'Firma eliminada exitosamente');
        } else {
            flash('warning', 'No hay firma para eliminar');
        }

        redirect('usuarios.php?action=edit&id=' . $userId);
    }

    /**
     * Obtiene mensaje de error de subida
     */
    private function getUploadError(int $code): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
        ];
        return $errors[$code] ?? 'Error desconocido';
    }
}
