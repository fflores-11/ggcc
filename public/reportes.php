<?php
/**
 * Reportes - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new ReportesController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
        
    case 'morosidad':
        $controller->morosidad();
        break;
        
    case 'pagos':
        $controller->pagos();
        break;
        
    case 'deudas':
        $controller->deudas();
        break;
        
    case 'egresos':
        $controller->egresos();
        break;
}
