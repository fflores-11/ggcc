<?php
/**
 * Login - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuth()) {
    redirect('index.php');
}

$controller = new AuthController();
$action = $_GET['action'] ?? 'login';

if ($action === 'doLogin') {
    $controller->doLogin();
} else {
    $controller->login();
}
