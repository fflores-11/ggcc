<?php
/**
 * Vista: Olvidé mi Contraseña
 */

$title = 'Recuperar Contraseña';
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
        
        .forgot-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 450px;
            width: 90%;
            padding: 40px;
        }
        
        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .forgot-header i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .forgot-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .forgot-header p {
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
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <i class="bi bi-key-fill"></i>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p>Ingresa tu email y te enviaremos instrucciones para restablecerla.</p>
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
        
        <?php if ($warning = flash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= $warning ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['show_link']) && isset($_SESSION['reset_url'])): ?>
            <div class="alert alert-info">
                <h6><i class="bi bi-link-45deg me-2"></i>Enlace de Recuperación (Modo Desarrollo)</h6>
                <p class="mb-2">Para: <strong><?= e($_SESSION['reset_user'] ?? '') ?></strong></p>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" value="<?= e($_SESSION['reset_url']) ?>" id="resetLink" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyResetLink()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <a href="<?= e($_SESSION['reset_url']) ?>" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Ir al enlace
                </a>
                <small class="d-block mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Este enlace expira en 24 horas. En producción se enviará por email.
                </small>
            </div>
            <?php 
            // Limpiar variables de sesión
            unset($_SESSION['reset_url']); 
            unset($_SESSION['reset_user']);
            ?>
        <?php else: ?>

        <form action="login.php?action=send-reset-link" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required
                           placeholder="correo@ejemplo.com" autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom">
                <i class="bi bi-send me-2"></i>Enviar Instrucciones
            </button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">
                <i class="bi bi-arrow-left me-1"></i>Volver al login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/form-loading.js?v=2"></script>
    
    <script>
        function copyResetLink() {
            var copyText = document.getElementById("resetLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // Mostrar tooltip o alert
            alert("Enlace copiado al portapapeles");
        }
    </script>
</body>
</html>