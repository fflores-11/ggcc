<?php
/**
 * Consolidados - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new ConsolidadosController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
        
    case 'exportar':
        $controller->exportar();
        break;
}
