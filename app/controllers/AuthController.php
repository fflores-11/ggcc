<?php
/**
 * Controlador de Autenticación
 * Maneja login, logout y gestión de sesiones
 */

class AuthController {
    private Usuario $userModel;
    private ConfiguracionSMTP $smtpModel;

    public function __construct() {
        $this->userModel = new Usuario();
        $this->smtpModel = new ConfiguracionSMTP();
    }

    /**
     * Muestra el formulario de login
     */
    public function login(): void {
        if (isAuth()) {
            redirect('index.php');
        }
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Procesa el inicio de sesión
     */
    public function doLogin(): void {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login.php');
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token'])) {
            flash('error', 'Falta token CSRF');
            redirect('login.php');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'CSRF inválido. Recargue la página e intente nuevamente.');
            redirect('login.php');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($email) || empty($password)) {
            flash('error', 'Por favor ingrese email y contraseña');
            redirect('login.php');
        }

        // Intentar autenticar
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_rol'] = $user['rol'];
            
            // Guardar comunidad_id para administradores y presidentes
            if (in_array($user['rol'], ['administrador', 'presidente'])) {
                $_SESSION['user_comunidad_id'] = $user['comunidad_id'] ?? null;
            } else {
                $_SESSION['user_comunidad_id'] = null; // Super admin accede a todas
            }

            flash('success', 'Bienvenido, ' . $user['nombre']);
            redirect('index.php');
        } else {
            flash('error', 'Email o contraseña incorrectos');
            redirect('login.php');
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout(): void {
        // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Destruir la sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();

        flash('success', 'Sesión cerrada correctamente');
        redirect('login.php');
    }

    /**
     * Muestra el dashboard (después de login exitoso)
     */
    public function dashboard(): void {
        requireAuth();
        require_once VIEWS_PATH . '/dashboard/index.php';
    }

    /**
     * Muestra formulario de olvidé mi contraseña
     */
    public function forgotPassword(): void {
        if (isAuth()) {
            redirect('index.php');
        }
        require_once VIEWS_PATH . '/auth/forgot-password.php';
    }

    /**
     * Procesa solicitud de recuperación de contraseña
     */
    public function sendResetLink(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login.php?action=forgot-password');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('login.php?action=forgot-password');
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Por favor ingrese un email válido');
            redirect('login.php?action=forgot-password');
        }

        // Protección anti-duplicados: verificar si ya se envió un email recientemente
        $requestKey = 'reset_request_' . md5($email);
        $lastRequest = $_SESSION[$requestKey] ?? 0;
        $currentTime = time();
        
        // Si se envió hace menos de 30 segundos, ignorar
        if (($currentTime - $lastRequest) < 30) {
            flash('warning', 'Por favor espere un momento antes de solicitar otro email de recuperación.');
            redirect('login.php?action=forgot-password');
        }
        
        // Marcar esta solicitud
        $_SESSION[$requestKey] = $currentTime;

        // Buscar usuario por email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // No revelar si el email existe o no (seguridad)
            flash('success', 'Si el email existe en nuestro sistema, recibirá instrucciones para restablecer su contraseña.');
            redirect('login.php');
        }

        // Generar token
        $token = $this->userModel->generateResetToken((int)$user['id']);

        if (!$token) {
            flash('error', 'Error al generar el enlace de recuperación. Intente nuevamente.');
            redirect('login.php?action=forgot-password');
        }

        // Enviar email con el enlace
        $resetUrl = BASE_URL_FULL . 'login.php?action=reset-password&token=' . $token;
        
        // Obtener comunidad_id del usuario para usar la configuración SMTP correspondiente
        $comunidadId = null;
        if (in_array($user['rol'], ['administrador', 'presidente']) && !empty($user['comunidad_id'])) {
            $comunidadId = (int)$user['comunidad_id'];
        }
        
        $emailSent = $this->sendPasswordResetEmail($user['email'], $user['nombre'], $resetUrl, $comunidadId);

        if ($emailSent) {
            flash('success', 'Se han enviado las instrucciones a su email. Por favor revise su bandeja de entrada.');
            redirect('login.php');
        } else {
            // Si no se pudo enviar el email, mostrar el enlace en pantalla (modo desarrollo)
            $_SESSION['reset_url'] = $resetUrl;
            $_SESSION['reset_user'] = $user['nombre'];
            flash('warning', 'No se pudo enviar el email automáticamente. Use el enlace mostrado abajo.');
            redirect('login.php?action=forgot-password&show_link=1');
        }
    }

    /**
     * Muestra formulario para restablecer contraseña
     */
    public function resetPassword(): void {
        if (isAuth()) {
            redirect('index.php');
        }

        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            flash('error', 'Enlace de recuperación inválido');
            redirect('login.php');
        }

        // Verificar token
        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            flash('error', 'El enlace ha expirado o no es válido. Solicite uno nuevo.');
            redirect('login.php?action=forgot-password');
        }

        require_once VIEWS_PATH . '/auth/reset-password.php';
    }

    /**
     * Procesa el restablecimiento de contraseña
     */
    public function doResetPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('login.php');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token)) {
            flash('error', 'Enlace de recuperación inválido');
            redirect('login.php');
        }

        // Validar contraseña
        if (strlen($password) < 6) {
            flash('error', 'La contraseña debe tener al menos 6 caracteres');
            redirect('login.php?action=reset-password&token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            flash('error', 'Las contraseñas no coinciden');
            redirect('login.php?action=reset-password&token=' . urlencode($token));
        }

        // Verificar token nuevamente
        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            flash('error', 'El enlace ha expirado o no es válido. Solicite uno nuevo.');
            redirect('login.php?action=forgot-password');
        }

        // Restablecer contraseña
        $success = $this->userModel->resetPassword((int)$user['id'], $password);

        if ($success) {
            flash('success', 'Contraseña restablecida exitosamente. Ya puede iniciar sesión.');
            redirect('login.php');
        } else {
            flash('error', 'Error al restablecer la contraseña. Intente nuevamente.');
            redirect('login.php?action=reset-password&token=' . urlencode($token));
        }
    }

    /**
     * Envía email de recuperación de contraseña usando configuración SMTP del sistema
     * También guarda en archivo log para modo desarrollo
     * @param string $toEmail Email destinatario
     * @param string $toName Nombre del destinatario
     * @param string $resetUrl URL de recuperación
     * @param int|null $comunidadId ID de comunidad para usar su configuración SMTP
     * @return bool
     */
    private function sendPasswordResetEmail(string $toEmail, string $toName, string $resetUrl, ?int $comunidadId = null): bool {
        $subject = 'Recuperación de Contraseña - ' . APP_NAME;
        $appName = APP_NAME;
        
        $body = <<<HTML
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Recuperación de Contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$toName}</strong>,</p>
                    <p>Has solicitado restablecer tu contraseña en <strong>{$appName}</strong>.</p>
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <center>
                        <a href='{$resetUrl}' class='button'>Restablecer Contraseña</a>
                    </center>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; font-size: 12px; color: #666;'>{$resetUrl}</p>
                    <p><strong>Importante:</strong> Este enlace expirará en 24 horas.</p>
                    <p>Si no solicitaste este cambio, puedes ignorar este email.</p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático de {$appName}</p>
                    <p>Si necesitas ayuda, contacta al administrador.</p>
                </div>
            </div>
        </body>
        </html>
HTML;

        // Guardar en archivo log para modo desarrollo/pruebas
        $logDir = ROOT_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/emails_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . " | TO: {$toEmail} | SUBJECT: {$subject}\n";
        $logEntry .= "URL Recuperación: {$resetUrl}\n";
        $logEntry .= str_repeat('-', 80) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Intentar enviar usando configuración SMTP si existe
        if ($comunidadId !== null) {
            $config = $this->smtpModel->getByComunidad($comunidadId);
            
            if ($config) {
                try {
                    require_once ROOT_PATH . '/vendor/autoload.php';
                    
                    // Crear transporte SMTP
                    $encryption = $config['encryption'] === 'none' ? null : $config['encryption'];
                    $transport = (new Swift_SmtpTransport($config['host'], $config['port'], $encryption))
                        ->setUsername($config['username'])
                        ->setPassword($config['password']);

                    $mailer = new Swift_Mailer($transport);
                    
                    // Crear mensaje
                    $message = (new Swift_Message($subject))
                        ->setFrom([$config['from_email'] => $config['from_name']])
                        ->setTo([$toEmail => $toName])
                        ->setBody($body, 'text/html')
                        ->addPart(strip_tags($body), 'text/plain');

                    // Enviar
                    $result = $mailer->send($message);
                    
                    if ($result > 0) {
                        error_log("Email enviado exitosamente a {$toEmail} usando SMTP de comunidad {$comunidadId}");
                        return true;
                    }
                } catch (Exception $e) {
                    error_log('Error enviando email con SMTP configurado: ' . $e->getMessage());
                }
            } else {
                error_log("No hay configuración SMTP para comunidad {$comunidadId}");
            }
        }
        
        // Fallback: usar función mail() de PHP
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . EMAIL_FROM . "\r\n";

        $sent = @mail($toEmail, $subject, $body, $headers);
        
        // Log del resultado
        if (!$sent) {
            error_log("Error al enviar email a {$toEmail} usando mail(). Email guardado en: {$logFile}");
        }
        
        return $sent;
    }
}
