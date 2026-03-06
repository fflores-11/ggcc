<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right {
            padding: 50px;
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
    </style>
</head>
<body>
    <div class="container">
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
