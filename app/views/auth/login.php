<?php
// Cargar configuración desde la base de datos
require_once __DIR__ . '/../../models/ConfiguracionSistema.php';
$configModel = new ConfiguracionSistema();
$logos = $configModel->getBothLogos();
$bgConfig = $configModel->getLoginBackgroundConfig();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
            width: 100%;
        }
        
        body {
            min-height: 100vh;
            min-width: 100vw;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
<?php if ($bgConfig['exists']): ?>
            background: url('<?= $bgConfig['url'] ?>') center center/cover no-repeat fixed;
<?php else: ?>
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
<?php endif; ?>
        }
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
        }
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right {
            padding: 25px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .input-group-lg .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            background-color: #f8f9fa;
        }
        .input-group-lg .btn-outline-secondary:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }
        .input-group-lg .form-control:focus + .btn-outline-secondary {
            border-color: #667eea;
        }

        /* Logo dual mode styles - light/dark mode switching */
        .login-logo-container {
            text-align: center;
            position: relative;
            min-height: 200px;
        }
        
        .login-logo-container img {
            display: block;
            margin: 0 auto;
            max-width: 350px;
            max-height: 200px;
            object-fit: contain;
            position: relative;
        }
        
        /* Por defecto: mostrar logo CLARO */
        .login-logo-container .logo-light {
            display: block !important;
            background-color: transparent;
            padding: 10px;
            border-radius: 8px;
            position: relative;
            z-index: 2;
        }
        
        .login-logo-container .logo-dark {
            display: none !important;
            background-color: rgba(45, 55, 72, 0.1);
            padding: 10px;
            border-radius: 8px;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }
        
        /* Media query deshabilitada - el logo claro siempre se muestra */
        /*
        @media (prefers-color-scheme: dark) {
            .login-logo-container .logo-light {
                display: none !important;
            }
            .login-logo-container .logo-dark {
                display: block !important;
            }
        }
        */
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container row g-0">
            <div class="col-md-5 login-left">
                <h2 class="mb-4"><?= APP_NAME ?></h2>
                <p class="mb-4">Sistema de Administración de Gastos Comunes de Condominios</p>
                <div class="mt-4">
                    <p class="mb-2"><i class="bi bi-building me-2"></i> Gestión de comunidades</p>
                    <p class="mb-2"><i class="bi bi-house-door me-2"></i> Control de propiedades</p>
                    <p class="mb-2"><i class="bi bi-cash-coin me-2"></i> Pagos y cobranzas</p>
                    <p class="mb-2"><i class="bi bi-envelope me-2"></i> Envío de correos masivos</p>
                </div>
            </div>
            <div class="col-md-7 login-right">
                <!-- Logo con detección automática de modo oscuro -->
                <div class="text-center mb-4 login-logo-container">
                    <?php if ($logos['light_exists'] || $logos['dark_exists']): ?>
                        <?php if ($logos['light_exists']): ?>
                            <img class="logo-light" 
                                 src="<?= $logos['light'] ?>" 
                                 alt="Logo"
                                 onerror="this.style.display='none'; console.error('Error cargando logo claro:', this.src);">
                        <?php endif; ?>
                        <?php if ($logos['dark_exists']): ?>
                            <img class="logo-dark" 
                                 src="<?= $logos['dark'] ?>" 
                                 alt="Logo"
                                 onerror="this.style.display='none'; console.error('Error cargando logo oscuro:', this.src);">
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="display-4 text-primary mb-2">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h3 class="mb-4 text-center">Iniciar Sesión</h3>
                
                <?php if ($error = flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success = flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= e($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="login.php?action=doLogin" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" 
                               id="email" name="email" required 
                               placeholder="nombre@ejemplo.com"
                               value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" 
                                   id="password" name="password" required 
                                   placeholder="********">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Mostrar/Ocultar contraseña">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login btn-lg">
                            Ingresar al Sistema
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4 text-muted">
                    <small>Versión <?= APP_VERSION ?></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
                this.title = 'Ocultar contraseña';
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
                this.title = 'Mostrar contraseña';
            }
        });
    </script>
</body>
</html>
