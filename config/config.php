<?php
/**
 * Configuración General del Sistema
 */

session_start();

// Configuración de zona horaria
date_default_timezone_set('America/Santiago');

// Configuración de errores (en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En producción, usar:
// error_reporting(0);
// ini_set('display_errors', 0);

// Constantes del sistema
define('APP_NAME', 'Sistema GGCC');
define('APP_VERSION', '1.1.0');
define('BASE_URL', '/');

// === CONFIGURACIÓN DE URL DEL SERVIDOR ===
// Detectar protocolo automáticamente (http o https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

// Dominio o IP del servidor (configurable)
define('SERVER_DOMAIN', '10.10.12.140');  // Cambiar aquí si cambia la IP o dominio

// URL completa base (ej: http://10.10.12.140/)
define('BASE_URL_FULL', $protocol . SERVER_DOMAIN . '/');

// URLs para assets e imágenes
define('ASSETS_URL', BASE_URL_FULL . 'assets/');
define('IMAGES_URL', ASSETS_URL . 'images/');

// Configuración de email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('EMAIL_FROM', 'noreply@condominios.cl');
define('EMAIL_FROM_NAME', 'Sistema GGCC');

// Rutas
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Autoload de clases
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Funciones de utilidad

/**
 * Redirecciona a una URL
 * @param string $url
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit();
}

/**
 * Sanitiza un string para evitar XSS
 * @param string $text
 * @return string
 */
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Muestra mensajes de flash
 * @param string $type (success, error, warning, info)
 * @param string|null $message
 * @return string|null
 */
function flash(string $type, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash_' . $type] = $message;
        return null;
    }

    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }

    return null;
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function isAuth(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null
 */
function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtiene el rol del usuario actual
 * @return string|null
 */
function getUserRole(): ?string {
    return $_SESSION['user_rol'] ?? null;
}

/**
 * Verifica si el usuario tiene rol específico
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles): bool {
    if (!isAuth()) return false;
    
    $userRole = getUserRole();
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    return $userRole === $roles;
}

/**
 * Requiere autenticación para acceder
 */
function requireAuth(): void {
    if (!isAuth()) {
        flash('error', 'Debe iniciar sesión para acceder');
        redirect('login.php');
    }
}

/**
 * Genera un token CSRF
 * @return string
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica un token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtiene el ID de la comunidad asignada al usuario logueado
 * @return int|null
 */
function getUserComunidadId(): ?int {
    return $_SESSION['user_comunidad_id'] ?? null;
}

/**
 * Obtiene el ID de la propiedad asignada al usuario logueado (solo propietarios)
 * @return int|null
 */
function getUserPropiedadId(): ?int {
    return $_SESSION['user_propiedad_id'] ?? null;
}

/**
 * Verifica si el usuario tiene acceso a una comunidad específica
 * @param int $comunidadId
 * @return bool
 */
function hasAccessToComunidad(int $comunidadId): bool {
    // Super admin tiene acceso a todo
    if (getUserRole() === 'admin') {
        return true;
    }
    
    // Administradores, presidentes y propietarios solo acceden a su comunidad asignada
    $userComunidadId = getUserComunidadId();
    return $userComunidadId === $comunidadId;
}

/**
 * Requiere acceso a una comunidad específica o redirige
 * @param int $comunidadId
 */
function requireAccessToComunidad(int $comunidadId): void {
    if (!hasAccessToComunidad($comunidadId)) {
        flash('error', 'No tiene permisos para acceder a esta comunidad');
        redirect('index.php');
    }
}

/**
 * Obtiene el filtro de comunidad para consultas SQL
 * @return string
 */
function getComunidadFilter(): string {
    $userRol = getUserRole();
    $comunidadId = getUserComunidadId();
    
    if ($userRol !== 'admin' && $comunidadId !== null) {
        return " AND comunidad_id = " . (int)$comunidadId;
    }
    
    return '';
}

/**
 * Formatea un número como moneda
 * @param float $amount
 * @return string
 */
function formatMoney(float $amount): string {
    return '$' . number_format($amount, 0, ',', '.');
}

/**
 * Formatea una fecha
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate(string $date, string $format = 'd/m/Y'): string {
    return date($format, strtotime($date));
}

/**
 * Obtiene el nombre del mes en español
 * @param int $month
 * @return string
 */
function getMonthName(int $month): string {
    $months = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $months[$month] ?? '';
}

/**
 * Obtiene lista de años para select
 * @param int $startAgo
 * @param int $endForward
 * @return array
 */
function getYearList(int $startAgo = 2, int $endForward = 1): array {
    $current = date('Y');
    $years = [];
    for ($i = $current - $startAgo; $i <= $current + $endForward; $i++) {
        $years[] = $i;
    }
    return $years;
}

// Cargar helper de paginación
require_once APP_PATH . '/helpers/PaginationHelper.php';
