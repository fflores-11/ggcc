<?php
/**
 * Correos - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new CorreosController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
        
    case 'general':
        $controller->general();
        break;
        
    case 'enviar-general':
        $controller->enviarGeneral();
        break;
        
    case 'cobranza':
        $controller->cobranza();
        break;
        
    case 'enviar-cobranza':
        $controller->enviarCobranza();
        break;
        
    case 'resultado':
        $controller->resultado();
        break;
        
    case 'reenviar':
        $controller->reenviar();
        break;
        
    case 'preview':
        $controller->preview();
        break;
}
