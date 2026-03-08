<?php
/**
 * Controlador de Configuración del Sistema
 * Gestiona la configuración general del sistema
 */

class ConfiguracionController {
    private ConfiguracionSistema $configModel;

    public function __construct() {
        $this->configModel = new ConfiguracionSistema();
    }

    /**
     * Página principal de configuración
     */
    public function index(): void {
        $configuraciones = $this->configModel->getAll();
        
        // Obtener ambos logos con sus URLs completas
        $logos = $this->configModel->getBothLogos();
        $logoUrl = $logos['light'];
        $logoExists = $logos['light_exists'];
        $logoDarkUrl = $logos['dark'];
        $logoDarkExists = $logos['dark_exists'];
        
        $title = 'Configuración del Sistema';
        require_once VIEWS_PATH . '/configuracion/index.php';
    }

    /**
     * Muestra el formulario para cambiar el logo
     */
    public function logo(): void {
        // Obtener ambos logos con sus URLs completas
        $logos = $this->configModel->getBothLogos();
        $logoUrl = $logos['light'];
        $logoExists = $logos['light_exists'];
        $logoDarkUrl = $logos['dark'];
        $logoDarkExists = $logos['dark_exists'];
        
        $title = 'Cambiar Logo del Sistema';
        require_once VIEWS_PATH . '/configuracion/logo.php';
    }

    /**
     * Procesa la subida de logos (claro y/o oscuro)
     */
    public function subirLogo(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('configuracion.php?action=logo');
        }

        $tipoLogo = $_POST['tipo_logo'] ?? 'light'; // 'light' o 'dark'
        $inputName = $tipoLogo === 'dark' ? 'logo_dark' : 'logo';
        
        // Verificar si se subió un archivo
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'No se seleccionó ningún archivo');
            redirect('configuracion.php?action=logo');
        }

        $file = $_FILES[$inputName];

        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Error al subir el archivo: ' . $this->getUploadError($file['error']));
            redirect('configuracion.php?action=logo');
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            flash('error', 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WEBP)');
            redirect('configuracion.php?action=logo');
        }

        // Validar tamaño (máximo 2MB)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            flash('error', 'El archivo es demasiado grande. Máximo 2MB');
            redirect('configuracion.php?action=logo');
        }

        // Crear directorio si no existe
        $uploadDir = ROOT_PATH . '/public/assets/images/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                flash('error', 'Error al crear el directorio de subida. Verifique los permisos.');
                redirect('configuracion.php?action=logo');
            }
        }

        // Verificar permisos de escritura
        if (!is_writable($uploadDir)) {
            flash('error', 'El directorio de imágenes no tiene permisos de escritura. Contacte al administrador.');
            redirect('configuracion.php?action=logo');
        }

        // Generar nombre único para el archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $prefix = $tipoLogo === 'dark' ? 'logo_dark_' : 'logo_';
        $newFileName = $prefix . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;

        // Intentar eliminar logo anterior personalizado
        $logoPath = $tipoLogo === 'dark' ? $this->configModel->getLogoDarkPath() : $this->configModel->getLogoPath();
        $oldLogoFullPath = ROOT_PATH . '/public/' . $logoPath;
        if (file_exists($oldLogoFullPath) && is_file($oldLogoFullPath)) {
            // Solo eliminar si es un logo subido previamente (comienza con logo_ o logo_dark_)
            if (strpos(basename($oldLogoFullPath), 'logo_') === 0) {
                @unlink($oldLogoFullPath);
            }
        }

        // Mover el archivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Verificar que el archivo se movió correctamente
            if (file_exists($uploadPath)) {
                // Actualizar la configuración en la base de datos
                $relativePath = 'assets/images/' . $newFileName;
                $userId = getUserId();
                
                $success = $tipoLogo === 'dark' 
                    ? $this->configModel->setLogoDarkPath($relativePath, $userId)
                    : $this->configModel->setLogoPath($relativePath, $userId);
                
                if ($success) {
                    $tipoTexto = $tipoLogo === 'dark' ? 'oscuro' : 'claro';
                    flash('success', 'Logo ' . $tipoTexto . ' actualizado exitosamente');
                } else {
                    // Si falla la BD, eliminar el archivo subido
                    @unlink($uploadPath);
                    flash('error', 'Error al guardar la configuración en la base de datos');
                }
            } else {
                flash('error', 'Error: El archivo no se guardó correctamente');
            }
        } else {
            $error = error_get_last();
            flash('error', 'Error al mover el archivo: ' . ($error['message'] ?? 'Error desconocido'));
        }

        redirect('configuracion.php?action=logo');
    }

    /**
     * Elimina el logo (claro u oscuro) y vuelve al default
     */
    public function eliminarLogo(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('configuracion.php?action=logo');
        }

        $tipoLogo = $_POST['tipo_logo'] ?? 'light';
        
        $logoPath = $tipoLogo === 'dark' 
            ? $this->configModel->getLogoDarkPath() 
            : $this->configModel->getLogoPath();
        $fullPath = ROOT_PATH . '/public/' . $logoPath;

        // Eliminar archivo si existe y es un logo subido
        if (file_exists($fullPath) && is_file($fullPath)) {
            if (strpos(basename($fullPath), 'logo_') === 0) {
                @unlink($fullPath);
            }
        }

        // Restaurar configuración default
        $userId = getUserId();
        if ($tipoLogo === 'dark') {
            $this->configModel->setLogoDarkPath('assets/images/logo_dark.png', $userId);
        } else {
            $this->configModel->setLogoPath('assets/images/logo.png', $userId);
        }

        $tipoTexto = $tipoLogo === 'dark' ? 'oscuro' : 'claro';
        flash('success', 'Logo ' . $tipoTexto . ' eliminado. Se restaurará el logo por defecto.');
        redirect('configuracion.php?action=logo');
    }

    /**
     * Actualiza otras configuraciones del sistema
     */
    public function actualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('configuracion.php');
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            flash('error', 'Token de seguridad inválido');
            redirect('configuracion.php');
        }

        $userId = getUserId();
        $actualizadas = 0;

        // Actualizar cada configuración enviada
        foreach ($_POST as $clave => $valor) {
            // Ignorar campos del sistema
            if (in_array($clave, ['csrf_token', 'action'])) {
                continue;
            }

            // Validar que la clave existe en la BD
            $configActual = $this->configModel->get($clave);
            if ($configActual !== null) {
                if ($this->configModel->set($clave, $valor, $userId)) {
                    $actualizadas++;
                }
            }
        }

        if ($actualizadas > 0) {
            flash('success', "Se actualizaron {$actualizadas} configuración(es) exitosamente");
        } else {
            flash('warning', 'No se realizaron cambios');
        }

        redirect('configuracion.php');
    }

    /**
     * Obtiene mensaje de error de subida de archivo
     */
    private function getUploadError(int $code): string {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por PHP';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en el disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP detuvo la subida del archivo';
            default:
                return 'Error desconocido';
        }
    }
}
