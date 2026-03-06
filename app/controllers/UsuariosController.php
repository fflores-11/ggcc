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
        $usuarios = $this->userModel->all('nombre', 'ASC');
        $title = 'Mantenedor de Usuarios';
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
}
