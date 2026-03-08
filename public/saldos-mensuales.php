<?php
/**
 * Punto de entrada para Saldos Mensuales (Control de Caja)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireAuth();

$controller = new SaldosMensualesController();

$action = $_GET['action'] ?? 'index';

// Enrutamiento de acciones
switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'show':
        $controller->show();
        break;
    case 'consultar':
        $controller->consultar();
        break;
    case 'agregar-ingreso':
        $controller->agregarIngreso();
        break;
    case 'agregar-egreso':
        $controller->agregarEgreso();
        break;
    case 'ajustar-saldo':
        $controller->ajustarSaldo();
        break;
    case 'cerrar':
        $controller->cerrar();
        break;
    case 'recalcular':
        $controller->recalcular();
        break;
    case 'traer-saldo-anterior':
        $controller->traerSaldoAnterior();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        $controller->index();
}
