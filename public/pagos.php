<?php
/**
 * Pagos - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new PagosController();
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
        
    case 'recibo':
        $controller->recibo();
        break;
        
    case 'pdf':
        $controller->pdf();
        break;
        
    case 'enviar':
        $controller->enviarEmail();
        break;
        
    case 'api-deudas':
        $controller->apiGetDeudas();
        break;
        
    case 'generar-deudas':
        $controller->generarDeudas();
        break;
}
