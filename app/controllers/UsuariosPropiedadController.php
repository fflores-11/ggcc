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
    private Mascota $mascotaModel;
    private PDO $db;

    public function __construct() {
        $this->userModel = new Usuario();
        $this->propiedadModel = new Propiedad();
        $this->comunidadModel = new Comunidad();
        $this->mascotaModel = new Mascota();
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

        // Paginación
        $pagination = getPaginationParams(20);

        // Obtener usuarios según rol
        if (getUserRole() === 'admin') {
            $totalRecords = $this->userModel->countUsuariosPropietarios();
            $usuarios = $this->userModel->getUsuariosPropietariosPaginated(null, $pagination['offset'], $pagination['perPage']);
        } else {
            $comunidadId = getUserComunidadId();
            $totalRecords = $this->userModel->countUsuariosPropietarios($comunidadId);
            $usuarios = $this->userModel->getUsuariosPropietariosPaginated($comunidadId, $pagination['offset'], $pagination['perPage']);
        }

        $title = 'Usuarios por Propiedad';
        $currentPage = $pagination['page'];
        $perPage = $pagination['perPage'];
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

        // Validar email solo si se proporciona
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El email no es válido';
            } elseif ($this->userModel->emailExists($data['email'])) {
                $errors[] = 'El email ya está registrado';
            }
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

        // Validar email solo si se proporciona
        $errors = [];
        
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El email no es válido';
            } elseif ($this->userModel->emailExists($data['email'], $id)) {
                $errors[] = 'El email ya está registrado por otro usuario';
            }
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

        // Obtener mascotas de la propiedad
        $mascotas = [];
        if (!empty($usuario['propiedad_id'])) {
            $mascotas = $this->mascotaModel->getByPropiedad($usuario['propiedad_id']);
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

        // Validar email solo si se proporciona
        $errors = [];
        
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El email no es válido';
            } elseif ($this->userModel->emailExists($data['email'], $userId)) {
                $errors[] = 'El email ya está registrado por otro usuario';
            }
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

    /**
     * Actualiza los datos de la propiedad por el propietario logueado
     */
    public function updatePropiedad(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('perfil.php');
        }

        // Solo propietarios pueden actualizar su propiedad
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        $userId = getUserId();
        $propiedadId = getUserPropiedadId();
        
        if (!$propiedadId) {
            flash('error', 'No tiene una propiedad asignada');
            redirect('perfil.php');
        }

        // Verificar que el usuario tenga acceso a esta propiedad
        if (!$this->userModel->hasAccessToPropiedad($userId, $propiedadId)) {
            flash('error', 'No tiene permisos para editar esta propiedad');
            redirect('perfil.php');
        }

        $data = [
            'nombre_dueno' => trim($_POST['nombre_dueno'] ?? ''),
            'email_dueno' => trim($_POST['email_dueno'] ?? ''),
            'whatsapp_dueno' => trim($_POST['whatsapp_dueno'] ?? ''),
            'nombre_agente' => trim($_POST['nombre_agente'] ?? ''),
            'email_agente' => trim($_POST['email_agente'] ?? ''),
            'whatsapp_agente' => trim($_POST['whatsapp_agente'] ?? '')
        ];

        // Validaciones
        $errors = [];

        if (empty($data['nombre_dueno'])) {
            $errors[] = 'El nombre del dueño es obligatorio';
        }

        if (!empty($data['email_dueno']) && !filter_var($data['email_dueno'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del dueño no es válido';
        }

        if (!empty($data['email_agente']) && !filter_var($data['email_agente'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del agente no es válido';
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
            redirect('perfil.php');
        }

        $success = $this->propiedadModel->updateByPropietario($propiedadId, $data);
        
        if ($success) {
            flash('success', 'Datos de la propiedad actualizados exitosamente');
        } else {
            flash('error', 'Error al actualizar los datos de la propiedad');
        }
        
        redirect('perfil.php');
    }

    /**
     * Agrega una nueva mascota a la propiedad del propietario logueado
     */
    public function agregarMascota(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('perfil.php');
        }

        // Solo propietarios pueden agregar mascotas
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        $userId = getUserId();
        $propiedadId = getUserPropiedadId();
        
        if (!$propiedadId) {
            flash('error', 'No tiene una propiedad asignada');
            redirect('perfil.php');
        }

        // Verificar que el usuario tenga acceso a esta propiedad
        if (!$this->userModel->hasAccessToPropiedad($userId, $propiedadId)) {
            flash('error', 'No tiene permisos para agregar mascotas a esta propiedad');
            redirect('perfil.php');
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
            redirect('perfil.php');
        }

        // Procesar imagen si se subió
        $imagenPath = $this->procesarImagenMascota($_FILES['imagen'] ?? null);
        if ($imagenPath !== false) {
            $data['imagen_path'] = $imagenPath;
        }

        $mascotaId = $this->mascotaModel->create($data);
        
        if ($mascotaId) {
            flash('success', 'Mascota agregada exitosamente');
        } else {
            flash('error', 'Error al agregar la mascota');
        }
        
        redirect('perfil.php');
    }

    /**
     * Actualiza una mascota existente
     */
    public function actualizarMascota(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('perfil.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('perfil.php');
        }

        // Solo propietarios pueden actualizar mascotas
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        $mascotaId = (int)($_POST['mascota_id'] ?? 0);
        if (!$mascotaId) {
            flash('error', 'ID de mascota no válido');
            redirect('perfil.php');
        }

        // Verificar que la mascota pertenezca a la propiedad del usuario
        $mascota = $this->mascotaModel->getWithPropiedad($mascotaId);
        if (!$mascota || $mascota['propiedad_id'] !== getUserPropiedadId()) {
            flash('error', 'No tiene permisos para editar esta mascota');
            redirect('perfil.php');
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
            redirect('perfil.php');
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
        
        redirect('perfil.php');
    }

    /**
     * Elimina (desactiva) una mascota
     */
    public function eliminarMascota(): void {
        $mascotaId = (int)($_GET['id'] ?? 0);
        
        if (!$mascotaId) {
            flash('error', 'ID de mascota no válido');
            redirect('perfil.php');
        }

        // Solo propietarios pueden eliminar mascotas
        if (getUserRole() !== 'propietario') {
            flash('error', 'No tiene permisos para esta acción');
            redirect('dashboard.php');
        }

        // Verificar que la mascota pertenezca a la propiedad del usuario
        $mascota = $this->mascotaModel->getWithPropiedad($mascotaId);
        if (!$mascota || $mascota['propiedad_id'] !== getUserPropiedadId()) {
            flash('error', 'No tiene permisos para eliminar esta mascota');
            redirect('perfil.php');
        }

        // Eliminar imagen física
        $this->mascotaModel->deleteImagen($mascotaId);

        $success = $this->mascotaModel->delete($mascotaId);
        
        if ($success) {
            flash('success', 'Mascota eliminada exitosamente');
        } else {
            flash('error', 'Error al eliminar la mascota');
        }
        
        redirect('perfil.php');
    }

    /**
     * Procesa la imagen de una mascota
     * @param array|null $file
     * @return string|false
     */
    private function procesarImagenMascota(?array $file): string|false {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
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
        }

        return false;
    }
}
