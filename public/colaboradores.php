<?php
/**
 * Colaboradores - Punto de entrada
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? 'index';

// Permitir acceso sin autenticación para generarReciboPDF con token válido
if ($action === 'generarReciboPDF') {
    // Verificar token de acceso para el PDF
    $token = $_GET['token'] ?? '';
    $id = (int) ($_GET['id'] ?? 0);
    
    // Generar token esperado
    $expectedToken = hash('sha256', 'recibo_' . $id . '_ggcc_2024');
    
    if ($token !== $expectedToken) {
        // Token inválido, requerir autenticación normal
        requireAuth();
    }
} else {
    requireAuth();
}

$controller = new ColaboradoresController();

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
        
    case 'editPago':
        $controller->editPago();
        break;
        
    case 'updatePago':
        $controller->updatePago();
        break;
        
    case 'generarReciboPDF':
        $controller->generarReciboPDF();
        break;
        
    case 'verImagen':
        $controller->verImagen();
        break;
        
    case 'eliminarImagen':
        $controller->eliminarImagen();
        break;
}
