<?php
/**
 * Entry point para Perfil de Usuario Propietario
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación
requireAuth();

$controller = new UsuariosPropiedadController();
$action = $_GET['action'] ?? 'perfil';

// Rutas disponibles
$validActions = [
    'perfil', 'updatePerfil', 'updatePropiedad'
];

if (in_array($action, $validActions)) {
    $controller->$action();
} else {
    $controller->perfil();
}
