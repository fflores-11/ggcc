<?php
/**
 * Logout
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$controller = new AuthController();
$controller->logout();
