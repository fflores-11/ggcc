<?php
/**
 * Header Template
 * Incluye Bootstrap, menú de navegación y estilos
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' - ' : '' ?><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
        }
        
        .sidebar-brand small {
            color: rgba(255,255,255,0.6);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-divider {
            margin: 20px 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu .menu-title {
            padding: 10px 25px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.4);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-card.primary .icon {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .stat-card.success .icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .stat-card.warning .icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .stat-card.danger .icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .stat-card .number {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
            word-wrap: break-word;
            line-height: 1.2;
        }
        
        .stat-card .label {
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-container .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-container .table-body {
            padding: 0;
        }
        
        /* Forms */
        .form-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 30px;
        }
        
        .form-section .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            color: white;
            opacity: 0.9;
        }
        
        /* User dropdown */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-menu .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Logo dual mode styles - light/dark mode switching */
        .sidebar-logo {
            text-align: center;
        }
        
        .sidebar-logo img {
            display: block;
            margin: 0 auto;
            max-height: 80px;
            max-width: 100%;
        }
        
        /* FORZAR: siempre mostrar logo CLARO, ignorar preferencia del sistema */
        .sidebar-logo {
            text-align: center;
            position: relative;
            min-height: 80px;
        }
        
        .sidebar-logo img {
            display: block;
            margin: 0 auto;
            max-height: 80px;
            max-width: 100%;
            position: relative;
        }
        
        .sidebar-logo .logo-light {
            display: block !important;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 10px;
            border-radius: 8px;
            position: relative;
            z-index: 2;
        }
        
        .sidebar-logo .logo-dark {
            display: none !important;
            background-color: transparent;
            padding: 10px;
            border-radius: 8px;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }
        
        .sidebar-logo .logo-dark {
            display: none !important;
            background-color: transparent;
            padding: 10px;
            border-radius: 8px;
        }
        
        /* Media query deshabilitada - el logo claro siempre se muestra */
        /*
        @media (prefers-color-scheme: dark) {
            .sidebar-logo .logo-light {
                display: none !important;
            }
            .sidebar-logo .logo-dark {
                display: block !important;
            }
        }
        */
    </style>
</head>
<body>
    <?php
    // Cargar logos desde la base de datos para el sidebar (modo dual: claro/oscuro)
    require_once __DIR__ . '/../../models/ConfiguracionSistema.php';
    $configModelSidebar = new ConfiguracionSistema();
    $logosSidebar = $configModelSidebar->getBothLogos();
    ?>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <?php if ($logosSidebar['light_exists'] || $logosSidebar['dark_exists']): ?>
                <div class="sidebar-logo mb-3" id="sidebar-logo-wrapper">
                    <?php if ($logosSidebar['light_exists']): ?>
                        <img class="logo-light" 
                             src="<?= $logosSidebar['light'] ?>" 
                             alt="Logo"
                             onerror="this.style.display='none'; console.error('Error cargando logo claro:', this.src);">
                    <?php endif; ?>
                    <?php if ($logosSidebar['dark_exists']): ?>
                        <img class="logo-dark" 
                             src="<?= $logosSidebar['dark'] ?>" 
                             alt="Logo"
                             onerror="this.style.display='none'; console.error('Error cargando logo oscuro:', this.src);">
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Debug: No logos found -->
                <div style="display: none;">Light: <?= $logosSidebar['light'] ?? 'N/A' ?>, Dark: <?= $logosSidebar['dark'] ?? 'N/A' ?></div>
            <?php endif; ?>
            <h4><?= APP_NAME ?></h4>
            <small>Administración</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <div class="sidebar-divider"></div>
            <div class="menu-title">Mantenedores</div>
            
            <a href="usuarios.php" class="<?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Usuarios
            </a>
            <a href="comunidades.php" class="<?= basename($_SERVER['PHP_SELF']) === 'comunidades.php' ? 'active' : '' ?>">
                <i class="bi bi-building"></i> Comunidades
            </a>
            <a href="propiedades.php" class="<?= basename($_SERVER['PHP_SELF']) === 'propiedades.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door"></i> Propiedades
            </a>
            
            <div class="sidebar-divider"></div>
            <div class="menu-title">Operaciones</div>
            
            <a href="pagos.php" class="<?= basename($_SERVER['PHP_SELF']) === 'pagos.php' ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i> Pagos
            </a>
            <a href="saldos-mensuales.php" class="<?= basename($_SERVER['PHP_SELF']) === 'saldos-mensuales.php' ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i> Saldos Mensuales
            </a>
            <a href="colaboradores.php" class="<?= basename($_SERVER['PHP_SELF']) === 'colaboradores.php' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Colaboradores
            </a>
            <a href="correos.php" class="<?= basename($_SERVER['PHP_SELF']) === 'correos.php' ? 'active' : '' ?>">
                <i class="bi bi-envelope"></i> Envío de Correos
            </a>
            <a href="consolidados.php" class="<?= basename($_SERVER['PHP_SELF']) === 'consolidados.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3"></i> Consolidados
            </a>
            
            <div class="sidebar-divider"></div>
            <div class="menu-title">Reportes</div>
            
            <a href="reportes.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Reportes
            </a>
            
            <?php if (hasRole('admin')): ?>
            <div class="sidebar-divider"></div>
            <div class="menu-title">Administración</div>
            
            <a href="configuracion.php" class="<?= basename($_SERVER['PHP_SELF']) === 'configuracion.php' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Configuración Sistema
            </a>
            <a href="configuracion_smtp.php" class="<?= basename($_SERVER['PHP_SELF']) === 'configuracion_smtp.php' ? 'active' : '' ?>">
                <i class="bi bi-envelope-gear"></i> Configuración SMTP
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h5 class="mb-0"><?= isset($title) ? e($title) : 'Dashboard' ?></h5>
            </div>
            
            <div class="dropdown">
                <div class="user-menu" data-bs-toggle="dropdown">
                    <div class="avatar">
                        <?= substr($_SESSION['user_nombre'] ?? 'A', 0, 1) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= e($_SESSION['user_nombre'] ?? 'Usuario') ?></div>
                        <small class="text-muted"><?= ucfirst($_SESSION['user_rol'] ?? 'Usuario') ?></small>
                    </div>
                    <i class="bi bi-chevron-down ms-2"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= e($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($warning = flash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= e($warning) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="content-wrapper">
