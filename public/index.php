<?php
/**
 * Punto de entrada principal del sistema
 * Router simple y controlador frontal
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación para todas las páginas excepto login
$currentPage = basename($_SERVER['PHP_SELF']);
$publicPages = ['login.php'];

if (!in_array($currentPage, $publicPages)) {
    requireAuth();
}

// Router simple
$action = $_GET['action'] ?? 'index';

// Determinar qué controlador usar
$page = str_replace('.php', '', $currentPage);

switch ($page) {
    case 'index':
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case 'login':
        $controller = new AuthController();
        if ($action === 'doLogin') {
            $controller->doLogin();
        } else {
            $controller->login();
        }
        break;
        
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'usuarios':
        require_once __DIR__ . '/usuarios.php';
        break;
        
    case 'comunidades':
        require_once __DIR__ . '/comunidades.php';
        break;
        
    case 'propiedades':
        require_once __DIR__ . '/propiedades.php';
        break;
        
    case 'pagos':
        require_once __DIR__ . '/pagos.php';
        break;
        
    case 'correos':
        require_once __DIR__ . '/correos.php';
        break;
        
    case 'consolidados':
        require_once __DIR__ . '/consolidados.php';
        break;
        
    case 'reportes':
        require_once __DIR__ . '/reportes.php';
        break;
        
    default:
        // Página no encontrada
        header('HTTP/1.0 404 Not Found');
        echo 'Página no encontrada';
        break;
}
