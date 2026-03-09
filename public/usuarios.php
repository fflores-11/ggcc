<?php
/**
 * Usuarios - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new UsuariosController();
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
        
    case 'edit':
        $controller->edit();
        break;
        
    case 'update':
        $controller->update();
        break;
        
    case 'delete':
        $controller->delete();
        break;
        
    case 'restore':
        $controller->restore();
        break;
        
    case 'subir-firma':
        $controller->subirFirma();
        break;
        
    case 'eliminar-firma':
        $controller->eliminarFirma();
        break;
}
