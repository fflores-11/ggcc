<?php
/**
 * Modelo ConfiguracionSMTP
 * Gestiona la configuración SMTP por comunidad
 */

require_once __DIR__ . '/Model.php';

class ConfiguracionSMTP extends Model {
    protected string $table = 'configuracion_smtp';

    /**
     * Obtiene configuración SMTP de una comunidad
     * @param int $comunidadId
     * @return array|null
     */
    public function getByComunidad(int $comunidadId): ?array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE comunidad_id = :comunidad_id AND activo = 1 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtiene todas las configuraciones SMTP con info de comunidad
     * @return array
     */
    public function getAllWithComunidad(): array {
        $sql = "SELECT s.*, c.nombre as comunidad_nombre, c.comuna
                FROM {$this->table} s
                LEFT JOIN comunidades c ON s.comunidad_id = c.id
                WHERE s.activo = 1 AND c.activo = 1
                ORDER BY c.nombre";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Guarda o actualiza configuración SMTP
     * @param array $data
     * @return int
     */
    public function save(array $data): int {
        // Verificar si ya existe para esta comunidad
        $sql = "SELECT id FROM {$this->table} WHERE comunidad_id = :comunidad_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $data['comunidad_id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Actualizar
            $this->update((int)$existing['id'], $data);
            return (int)$existing['id'];
        } else {
            // Crear
            return $this->create($data);
        }
    }

    /**
     * Valida datos SMTP
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data): array {
        $errors = [];

        if (empty($data['comunidad_id'])) {
            $errors[] = 'Debe seleccionar una comunidad';
        }

        if (empty($data['host'])) {
            $errors[] = 'El host SMTP es obligatorio';
        }

        if (empty($data['port']) || !is_numeric($data['port'])) {
            $errors[] = 'El puerto debe ser numérico';
        }

        if (empty($data['username'])) {
            $errors[] = 'El usuario SMTP es obligatorio';
        }

        if (empty($data['password'])) {
            $errors[] = 'La contraseña SMTP es obligatoria';
        }

        if (empty($data['from_email']) || !filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email remitente no es válido';
        }

        if (empty($data['from_name'])) {
            $errors[] = 'El nombre remitente es obligatorio';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene comunidades sin configuración SMTP
     * @return array
     */
    public function getComunidadesSinConfig(): array {
        $sql = "SELECT c.id, c.nombre, c.comuna
                FROM comunidades c
                WHERE c.activo = 1
                AND NOT EXISTS (
                    SELECT 1 FROM {$this->table} s 
                    WHERE s.comunidad_id = c.id AND s.activo = 1
                )
                ORDER BY c.nombre";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Prueba la conexión SMTP
     * @param array $config
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(array $config): array {
        try {
            $transport = (new Swift_SmtpTransport($config['host'], $config['port'], $config['encryption']))
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            $mailer = new Swift_Mailer($transport);
            
            // Intentar enviar un mensaje de prueba
            $message = (new Swift_Message('Test SMTP'))
                ->setFrom([$config['from_email'] => $config['from_name']])
                ->setTo([$config['from_email']])
                ->setBody('Conexión SMTP exitosa');

            $result = $mailer->send($message);
            
            return [
                'success' => $result > 0,
                'message' => $result > 0 ? 'Conexión SMTP exitosa' : 'No se pudo enviar el mensaje de prueba'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }
}
