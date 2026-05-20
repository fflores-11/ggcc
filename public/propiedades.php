<?php
/**
 * Propiedades - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new PropiedadesController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
        
    case 'show':
        $controller->show();
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
        
    case 'api-list':
        $controller->apiListByComunidad();
        break;
        
    case 'agregarMascota':
        $controller->agregarMascota();
        break;
        
    case 'actualizarMascota':
        $controller->actualizarMascota();
        break;
        
    case 'eliminarMascota':
        $controller->eliminarMascota();
        break;
}
