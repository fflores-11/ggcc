<?php
/**
 * Colaboradores - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new ColaboradoresController();
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
        
    case 'show':
        $controller->show();
        break;
        
    case 'pagos':
        $controller->pagos();
        break;
        
    case 'createPago':
        $controller->createPago();
        break;
        
    case 'storePago':
        $controller->storePago();
        break;
        
    case 'deletePago':
        $controller->deletePago();
        break;
}
