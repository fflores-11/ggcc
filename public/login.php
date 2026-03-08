<?php
/**
 * Login - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Si ya está autenticado, redirigir al dashboard (excepto para logout)
if (isAuth() && ($_GET['action'] ?? '') !== 'logout') {
    redirect('index.php');
}

$controller = new AuthController();
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'doLogin':
        $controller->doLogin();
        break;
        
    case 'forgot-password':
        $controller->forgotPassword();
        break;
        
    case 'send-reset-link':
        $controller->sendResetLink();
        break;
        
    case 'reset-password':
        $controller->resetPassword();
        break;
        
    case 'do-reset-password':
        $controller->doResetPassword();
        break;
        
    case 'logout':
        $controller->logout();
        break;
        
    default:
        $controller->login();
        break;
}
