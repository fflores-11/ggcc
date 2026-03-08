<?php
/**
 * Configuración del Sistema - Punto de entrada
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new ConfiguracionController();

$action = $_GET['action'] ?? 'index';

// Enrutamiento de acciones
switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'logo':
        $controller->logo();
        break;
    case 'subir-logo':
        $controller->subirLogo();
        break;
    case 'eliminar-logo':
        $controller->eliminarLogo();
        break;
    case 'actualizar':
        $controller->actualizar();
        break;
    default:
        $controller->index();
}
