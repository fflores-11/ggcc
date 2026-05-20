<?php
/**
 * Vista: Restablecer Contraseña
 */

$title = 'Restablecer Contraseña';
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 450px;
            width: 90%;
            padding: 40px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .reset-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .reset-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            color: white;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h2>Crear Nueva Contraseña</h2>
            <p>Ingresa tu nueva contraseña para continuar.</p>
        </div>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="login.php?action=do-reset-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-4">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required
                           minlength="6" placeholder="Mínimo 6 caracteres" autofocus>
                </div>
                <div class="password-requirements">
                    <i class="bi bi-info-circle me-1"></i>La contraseña debe tener al menos 6 caracteres
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required
                           minlength="6" placeholder="Repita la contraseña">
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom">
                <i class="bi bi-check-lg me-2"></i>Restablecer Contraseña
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">
                <i class="bi bi-arrow-left me-1"></i>Volver al login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/form-loading.js?v=2"></script>
    
    <script>
        // Validación de contraseñas coincidan
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('password_confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    </script>
</body>
</html>