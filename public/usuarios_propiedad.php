<?php
/**
 * Entry point para gestión de Usuarios por Propiedad
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación
requireAuth();

// Solo admin y administrador pueden acceder a esta sección
if (!in_array(getUserRole(), ['admin', 'administrador'])) {
    flash('error', 'No tiene permisos para acceder a esta sección');
    redirect('dashboard.php');
}

$controller = new UsuariosPropiedadController();
$action = $_GET['action'] ?? 'index';

// Rutas disponibles
$validActions = [
    'index', 'create', 'store', 'edit', 'update', 
    'delete', 'restore', 'generarNuevaPassword', 'getPropiedadesByComunidad'
];

if (in_array($action, $validActions)) {
    $controller->$action();
} else {
    $controller->index();
}
