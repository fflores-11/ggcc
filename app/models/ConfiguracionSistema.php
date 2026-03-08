<?php
/**
 * Modelo ConfiguracionSistema
 * Gestiona la configuración general del sistema
 */

require_once __DIR__ . '/Model.php';

class ConfiguracionSistema extends Model {
    protected string $table = 'configuracion_sistema';

    /**
     * Obtiene un valor de configuración por su clave
     * @param string $clave
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function get(string $clave, $default = null) {
        $sql = "SELECT valor FROM {$this->table} WHERE clave = :clave";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':clave' => $clave]);
        $result = $stmt->fetch();
        
        return $result ? $result['valor'] : $default;
    }

    /**
     * Actualiza un valor de configuración
     * @param string $clave
     * @param string $valor
     * @param int|null $userId Usuario que hace la actualización
     * @return bool
     */
    public function set(string $clave, string $valor, ?int $userId = null): bool {
        $sql = "UPDATE {$this->table} 
                SET valor = :valor, updated_by = :user_id 
                WHERE clave = :clave";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':clave' => $clave,
            ':valor' => $valor,
            ':user_id' => $userId
        ]);
    }

    /**
     * Obtiene todas las configuraciones
     * @return array
     */
    public function getAll(): array {
        $sql = "SELECT cs.*, u.nombre as updated_by_nombre 
                FROM {$this->table} cs
                LEFT JOIN usuarios u ON cs.updated_by = u.id
                ORDER BY cs.clave";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la ruta del logo
     * @return string
     */
    public function getLogoPath(): string {
        return $this->get('logo_path', 'assets/images/logo.png');
    }

    /**
     * Actualiza la ruta del logo
     * @param string $path
     * @param int|null $userId
     * @return bool
     */
    public function setLogoPath(string $path, ?int $userId = null): bool {
        return $this->set('logo_path', $path, $userId);
    }

    /**
     * Verifica si existe el logo
     * @return bool
     */
    public function logoExists(): bool {
        $logoPath = $this->getLogoPath();
        return file_exists(ROOT_PATH . '/public/' . $logoPath);
    }

    /**
     * Obtiene la URL completa del logo
     * @return string
     */
    public function getLogoUrl(): string {
        $logoPath = $this->getLogoPath();
        if ($this->logoExists()) {
            return BASE_URL_FULL . $logoPath;
        }
        return '';
    }

    /**
     * Obtiene la ruta del logo para modo oscuro
     * @return string
     */
    public function getLogoDarkPath(): string {
        return $this->get('logo_path_dark', 'assets/images/logo_dark.png');
    }

    /**
     * Actualiza la ruta del logo oscuro
     * @param string $path
     * @param int|null $userId
     * @return bool
     */
    public function setLogoDarkPath(string $path, ?int $userId = null): bool {
        return $this->set('logo_path_dark', $path, $userId);
    }

    /**
     * Verifica si existe el logo oscuro
     * @return bool
     */
    public function logoDarkExists(): bool {
        $logoPath = $this->getLogoDarkPath();
        return file_exists(ROOT_PATH . '/public/' . $logoPath);
    }

    /**
     * Obtiene la URL completa del logo oscuro
     * @return string
     */
    public function getLogoDarkUrl(): string {
        $logoPath = $this->getLogoDarkPath();
        if ($this->logoDarkExists()) {
            return BASE_URL_FULL . $logoPath;
        }
        return '';
    }

    /**
     * Obtiene ambos logos (claro y oscuro)
     * @return array ['light' => url, 'dark' => url, 'light_exists' => bool, 'dark_exists' => bool]
     */
    public function getBothLogos(): array {
        return [
            'light' => $this->getLogoUrl(),
            'dark' => $this->getLogoDarkUrl(),
            'light_exists' => $this->logoExists(),
            'dark_exists' => $this->logoDarkExists()
        ];
    }
}
