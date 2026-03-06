<?php
/**
 * Controlador de Autenticación
 * Maneja login, logout y gestión de sesiones
 */

class AuthController {
    private Usuario $userModel;

    public function __construct() {
        $this->userModel = new Usuario();
    }

    /**
     * Muestra el formulario de login
     */
    public function login(): void {
        if (isAuth()) {
            redirect('index.php');
        }
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Procesa el inicio de sesión
     */
    public function doLogin(): void {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login.php');
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token'])) {
            flash('error', 'Falta token CSRF');
            redirect('login.php');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'CSRF inválido. Recargue la página e intente nuevamente.');
            redirect('login.php');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($email) || empty($password)) {
            flash('error', 'Por favor ingrese email y contraseña');
            redirect('login.php');
        }

        // Intentar autenticar
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_rol'] = $user['rol'];

            flash('success', 'Bienvenido, ' . $user['nombre']);
            redirect('index.php');
        } else {
            flash('error', 'Email o contraseña incorrectos');
            redirect('login.php');
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout(): void {
        // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Destruir la sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();

        flash('success', 'Sesión cerrada correctamente');
        redirect('login.php');
    }

    /**
     * Muestra el dashboard (después de login exitoso)
     */
    public function dashboard(): void {
        requireAuth();
        require_once VIEWS_PATH . '/dashboard/index.php';
    }
}
