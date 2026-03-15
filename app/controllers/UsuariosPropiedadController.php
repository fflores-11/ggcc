<?php
/**
 * Controlador de Usuarios por Propiedad
 * Gestiona la creación y administración de usuarios asociados a propiedades
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Propiedad.php';
require_once __DIR__ . '/../models/Comunidad.php';

class UsuariosPropiedadController {
    private Usuario $userModel;
    private Propiedad $propiedadModel;
    private Comunidad $comunidadModel;
    private PDO $db;

    public function __construct() {
        $this->userModel = new Usuario();
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
        $this->db = getDB();
    }

    /**
     * Lista todos los usuarios propietarios
     */
    public function index(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para acceder a esta sección');
            redirect('dashboard.php');
        }

        // Obtener usuarios según rol
        if (getUserRole() === 'admin') {
            $usuarios = $this->userModel->getUsuariosPropietarios();
        } else {
            $comunidadId = getUserComunidadId();
            $usuarios = $this->userModel->getUsuariosPropietarios($comunidadId);
        }

        $title = 'Usuarios por Propiedad';
        require_once VIEWS_PATH . '/usuarios_propiedad/index.php';
    }

    /**
     * Muestra formulario de creación
     */
    public function create(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para crear usuarios');
            redirect('dashboard.php');
        }

        // Obtener comunidades según rol
        if (getUserRole() === 'admin') {
            $comunidades = $this->comunidadModel->getForSelect();
        } else {
            $comunidadId = getUserComunidadId();
            $comunidades = $comunidadId ? [$this->comunidadModel->find($comunidadId)] : [];
        }

        // Generar contraseña automática
        $passwordGenerada = $this->userModel->generarPassword(10);

        $title = 'Nuevo Usuario por Propiedad';
        require_once VIEWS_PATH . '/usuarios_propiedad/form.php';
    }

    /**
     * Procesa la creación de usuario propietario
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios_propiedad.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para crear usuarios');
            redirect('dashboard.php');
        }

        $data = [
            'comunidad_id' => !empty($_POST['comunidad_id']) ? (int)$_POST['comunidad_id'] : null,
            'propiedad_id' => !empty($_POST['propiedad_id']) ? (int)$_POST['propiedad_id'] : null,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        // Validar campos requeridos
        $errors = [];

        if (empty($data['comunidad_id'])) {
            $errors[] = 'Debe seleccionar una comunidad';
        }

        if (empty($data['propiedad_id'])) {
            $errors[] = 'Debe seleccionar una propiedad';
        }

        // Si no se proporcionó nombre, generarlo automáticamente desde la propiedad
        if (empty($data['nombre']) || $data['nombre'] === 'Se generará automáticamente') {
            if (!empty($data['propiedad_id'])) {
                $propiedad = $this->propiedadModel->find($data['propiedad_id']);
                if ($propiedad) {
                    $data['nombre'] = $propiedad['nombre'];
                } else {
                    $errors[] = 'No se pudo obtener el nombre de la propiedad';
                }
            }
        }

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre de usuario es obligatorio';
        }

        if (empty($data['email'])) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }

        if (empty($data['password'])) {
            $errors[] = 'La contraseña es obligatoria';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        // Verificar que la propiedad pertenezca a la comunidad seleccionada
        if (!empty($data['propiedad_id']) && !empty($data['comunidad_id'])) {
            $propiedad = $this->propiedadModel->find($data['propiedad_id']);
            if (!$propiedad || (int)$propiedad['comunidad_id'] !== $data['comunidad_id']) {
                $errors[] = 'La propiedad seleccionada no pertenece a la comunidad indicada';
            }
        }

        // Verificar que la propiedad no tenga ya un usuario
        if (!empty($data['propiedad_id']) && $this->userModel->propiedadHasUsuario($data['propiedad_id'])) {
            $errors[] = 'Esta propiedad ya tiene un usuario asignado';
        }

        // Verificar permisos de comunidad para administradores
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ($data['comunidad_id'] !== $comunidadId) {
                $errors[] = 'No tiene permisos para crear usuarios en esta comunidad';
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
            redirect('usuarios_propiedad.php?action=create');
        }

        // Crear usuario propietario
        $userId = $this->userModel->createPropietario($data);

        if ($userId) {
            flash('success', 'Usuario propietario creado exitosamente. Contraseña: ' . $data['password']);
            redirect('usuarios_propiedad.php');
        } else {
            flash('error', 'Error al crear el usuario propietario');
            redirect('usuarios_propiedad.php?action=create');
        }
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para editar usuarios');
            redirect('dashboard.php');
        }

        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios_propiedad.php');
        }

        $usuario = $this->userModel->getUsuarioPropietario($id);
        
        if (!$usuario) {
            flash('error', 'Usuario propietario no encontrado');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos de comunidad
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ((int)$usuario['comunidad_id'] !== $comunidadId) {
                flash('error', 'No tiene permisos para editar este usuario');
                redirect('usuarios_propiedad.php');
            }
        }

        // Obtener comunidades según rol
        if (getUserRole() === 'admin') {
            $comunidades = $this->comunidadModel->getForSelect();
        } else {
            $comunidadId = getUserComunidadId();
            $comunidades = $comunidadId ? [$this->comunidadModel->find($comunidadId)] : [];
        }

        $title = 'Editar Usuario por Propiedad';
        require_once VIEWS_PATH . '/usuarios_propiedad/form.php';
    }

    /**
     * Procesa la actualización de usuario propietario
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios_propiedad.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para editar usuarios');
            redirect('dashboard.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios_propiedad.php');
        }

        // Obtener usuario actual
        $usuario = $this->userModel->getUsuarioPropietario($id);
        if (!$usuario) {
            flash('error', 'Usuario propietario no encontrado');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos de comunidad
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ((int)$usuario['comunidad_id'] !== $comunidadId) {
                flash('error', 'No tiene permisos para editar este usuario');
                redirect('usuarios_propiedad.php');
            }
        }

        $data = [
            'email' => trim($_POST['email'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? '')
        ];

        // Validar email
        $errors = [];
        
        if (empty($data['email'])) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($data['email'], $id)) {
            $errors[] = 'El email ya está registrado por otro usuario';
        }

        // Si hay password, agregarlo
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                $data['password'] = $_POST['password'];
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
            redirect('usuarios_propiedad.php?action=edit&id=' . $id);
        }

        $success = $this->userModel->updatePerfilPropietario($id, $data);
        
        if ($success) {
            flash('success', 'Usuario propietario actualizado exitosamente');
            redirect('usuarios_propiedad.php');
        } else {
            flash('error', 'Error al actualizar el usuario propietario');
            redirect('usuarios_propiedad.php?action=edit&id=' . $id);
        }
    }

    /**
     * Elimina (desactiva) un usuario propietario
     */
    public function delete(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para eliminar usuarios');
            redirect('dashboard.php');
        }

        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios_propiedad.php');
        }

        // Obtener usuario
        $usuario = $this->userModel->getUsuarioPropietario($id);
        if (!$usuario) {
            flash('error', 'Usuario propietario no encontrado');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos de comunidad
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ((int)$usuario['comunidad_id'] !== $comunidadId) {
                flash('error', 'No tiene permisos para eliminar este usuario');
                redirect('usuarios_propiedad.php');
            }
        }

        $success = $this->userModel->delete($id);
        
        if ($success) {
            flash('success', 'Usuario propietario eliminado exitosamente');
        } else {
            flash('error', 'Error al eliminar el usuario propietario');
        }
        
        redirect('usuarios_propiedad.php');
    }

    /**
     * Reactiva un usuario propietario desactivado
     */
    public function restore(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para reactivar usuarios');
            redirect('dashboard.php');
        }

        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios_propiedad.php');
        }

        // Obtener usuario
        $usuario = $this->userModel->find($id);
        if (!$usuario || !$usuario['es_propietario']) {
            flash('error', 'Usuario propietario no encontrado');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos de comunidad
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ((int)$usuario['comunidad_id'] !== $comunidadId) {
                flash('error', 'No tiene permisos para reactivar este usuario');
                redirect('usuarios_propiedad.php');
            }
        }

        $success = $this->userModel->restore($id);
        
        if ($success) {
            flash('success', 'Usuario propietario reactivado exitosamente');
        } else {
            flash('error', 'Error al reactivar el usuario propietario');
        }
        
        redirect('usuarios_propiedad.php');
    }

    /**
     * Genera una nueva contraseña para el usuario
     */
    public function generarNuevaPassword(): void {
        // Verificar permisos
        if (!in_array(getUserRole(), ['admin', 'administrador'])) {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            flash('error', 'ID de usuario no válido');
            redirect('usuarios_propiedad.php');
        }

        // Obtener usuario
        $usuario = $this->userModel->getUsuarioPropietario($id);
        if (!$usuario) {
            flash('error', 'Usuario propietario no encontrado');
            redirect('usuarios_propiedad.php');
        }

        // Verificar permisos de comunidad
        if (getUserRole() === 'administrador') {
            $comunidadId = getUserComunidadId();
            if ((int)$usuario['comunidad_id'] !== $comunidadId) {
                flash('error', 'No tiene permisos para modificar este usuario');
                redirect('usuarios_propiedad.php');
            }
        }

        // Generar nueva contraseña
        $nuevaPassword = $this->userModel->generarPassword(10);
        $success = $this->userModel->changePassword($id, $nuevaPassword);

        if ($success) {
            flash('success', 'Contraseña regenerada exitosamente. Nueva contraseña: ' . $nuevaPassword);
        } else {
            flash('error', 'Error al regenerar la contraseña');
        }

        redirect('usuarios_propiedad.php');
    }

    /**
     * Obtiene propiedades por comunidad (AJAX)
     */
    public function getPropiedadesByComunidad(): void {
        header('Content-Type: application/json');
        
        $comunidadId = (int) ($_GET['comunidad_id'] ?? 0);
        $excludeUserId = isset($_GET['exclude_user_id']) ? (int) $_GET['exclude_user_id'] : null;
        
        if (!$comunidadId) {
            echo json_encode([]);
            return;
        }

        // Verificar permisos
        if (getUserRole() === 'administrador') {
            $userComunidadId = getUserComunidadId();
            if ($comunidadId !== $userComunidadId) {
                echo json_encode([]);
                return;
            }
        } elseif (getUserRole() !== 'admin') {
            echo json_encode([]);
            return;
        }

        // Obtener todas las propiedades de la comunidad
        $propiedades = $this->propiedadModel->getByComunidad($comunidadId);
        
        // Obtener IDs de propiedades que ya tienen usuario asignado
        $sql = "SELECT propiedad_id FROM usuarios WHERE es_propietario = 1 AND activo = 1";
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = [];
        if ($excludeUserId) {
            $params[':exclude_id'] = $excludeUserId;
        }
        $stmt->execute($params);
        $propiedadesConUsuario = array_column($stmt->fetchAll(), 'propiedad_id');
        
        // Filtrar solo propiedades sin usuario asignado
        $propiedadesDisponibles = array_filter($propiedades, function($propiedad) use ($propiedadesConUsuario) {
            return !in_array($propiedad['id'], $propiedadesConUsuario);
        });
        
        // Reindexar array
        $propiedadesDisponibles = array_values($propiedadesDisponibles);
        
        echo json_encode($propiedadesDisponibles);
    }

    /**
     * Muestra el perfil del usuario propietario logueado
     */
    public function perfil(): void {
        // Solo propietarios pueden ver su propio perfil aquí
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para acceder a esta sección');
            redirect('dashboard.php');
        }

        $userId = getUserId();
        $usuario = $this->userModel->getUsuarioPropietario($userId);
        
        if (!$usuario) {
            flash('error', 'Error al cargar su perfil');
            redirect('dashboard.php');
        }

        $title = 'Mi Perfil';
        require_once VIEWS_PATH . '/usuarios_propiedad/perfil.php';
    }

    /**
     * Actualiza el perfil del usuario propietario logueado
     */
    public function updatePerfil(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('perfil.php');
        }

        // Solo propietarios pueden actualizar su propio perfil
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        $userId = getUserId();
        
        $data = [
            'email' => trim($_POST['email'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? '')
        ];

        // Validar email
        $errors = [];
        
        if (empty($data['email'])) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($data['email'], $userId)) {
            $errors[] = 'El email ya está registrado por otro usuario';
        }

        // Si hay password actual y nuevo password
        if (!empty($_POST['password_actual']) || !empty($_POST['password_nuevo']) || !empty($_POST['password_confirmar'])) {
            // Verificar password actual
            $usuarioActual = $this->userModel->find($userId);
            if (!$usuarioActual || !password_verify($_POST['password_actual'], $usuarioActual['password'])) {
                $errors[] = 'La contraseña actual es incorrecta';
            }

            // Verificar nuevo password
            if (empty($_POST['password_nuevo'])) {
                $errors[] = 'La nueva contraseña es obligatoria';
            } elseif (strlen($_POST['password_nuevo']) < 6) {
                $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres';
            }

            // Verificar confirmación
            if ($_POST['password_nuevo'] !== $_POST['password_confirmar']) {
                $errors[] = 'Las contraseñas nuevas no coinciden';
            }

            if (empty($errors)) {
                $data['password'] = $_POST['password_nuevo'];
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
            redirect('perfil.php');
        }

        $success = $this->userModel->updatePerfilPropietario($userId, $data);
        
        if ($success) {
            flash('success', 'Perfil actualizado exitosamente');
        } else {
            flash('error', 'Error al actualizar su perfil');
        }
        
        redirect('perfil.php');
    }
}
