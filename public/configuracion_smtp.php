<?php
/**
 * Configuración SMTP - Punto de entrada
 * Solo accesible para super usuarios (rol: admin)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación
requireAuth();

// Verificar que sea admin
if (!hasRole('admin')) {
    flash('error', 'Acceso denegado. Solo super usuarios pueden configurar SMTP.');
    redirect('index.php');
}

$controller = new ConfiguracionSMTPController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
        
    case 'create':
        $controller->create();
        break;
        
    case 'store':
        $controller->store();
        break;
        
    case 'delete':
        $controller->delete();
        break;
        
    case 'test':
        $controller->test();
        break;
}
