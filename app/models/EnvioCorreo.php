<?php
/**
 * Modelo EnvioCorreo
 * Gestiona el envío de correos masivos a propiedades
 */

require_once __DIR__ . '/Model.php';

class EnvioCorreo extends Model {
    protected string $table = 'envios_correo';

    /**
     * Obtiene todos los envíos con información de comunidad y usuario
     * @return array
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT e.*, 
                       c.nombre as comunidad_nombre,
                       u.nombre as enviado_por_nombre
                FROM {$this->table} e
                LEFT JOIN comunidades c ON e.comunidad_id = c.id
                LEFT JOIN usuarios u ON e.enviado_por = u.id
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un envío con todos sus detalles
     * @param int $envioId
     * @return array|null
     */
    public function getWithDetails(int $envioId): ?array {
        $sql = "SELECT e.*, 
                       c.nombre as comunidad_nombre,
                       c.direccion as comunidad_direccion,
                       c.nombre_presidente,
                       u.nombre as enviado_por_nombre
                FROM {$this->table} e
                LEFT JOIN comunidades c ON e.comunidad_id = c.id
                LEFT JOIN usuarios u ON e.enviado_por = u.id
                WHERE e.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $envioId]);
        $envio = $stmt->fetch();
        
        if (!$envio) {
            return null;
        }

        // Obtener detalles de envíos individuales
        $sqlDetalle = "SELECT ed.*, p.nombre as propiedad_nombre, p.nombre_dueno
                       FROM envios_correo_detalle ed
                       LEFT JOIN propiedades p ON ed.propiedad_id = p.id
                       WHERE ed.envio_id = :envio_id
                       ORDER BY p.nombre";
        $stmtDetalle = $this->db->prepare($sqlDetalle);
        $stmtDetalle->execute([':envio_id' => $envioId]);
        $envio['detalles'] = $stmtDetalle->fetchAll();
        
        return $envio;
    }

    /**
     * Crea un nuevo envío de correo masivo
     * @param array $data
     * @param array $propiedadesIds
     * @return int|false
     */
    public function crearEnvio(array $data, array $propiedadesIds) {
        try {
            $this->db->beginTransaction();

            // Insertar el envío principal
            $envioData = [
                'comunidad_id' => $data['comunidad_id'],
                'tipo' => $data['tipo'],
                'mes' => $data['mes'] ?? null,
                'anio' => $data['anio'] ?? null,
                'asunto' => $data['asunto'],
                'body' => $data['body'],
                'total_enviados' => count($propiedadesIds),
                'total_exitosos' => 0,
                'total_fallidos' => 0,
                'enviado_por' => $data['enviado_por']
            ];

            $envioId = parent::create($envioData);

            if (!$envioId) {
                throw new Exception('Error al crear el envío');
            }

            // Insertar detalles para cada propiedad
            foreach ($propiedadesIds as $propiedadId) {
                $sqlDetalle = "INSERT INTO envios_correo_detalle (envio_id, propiedad_id, email_enviado, estado) 
                              VALUES (:envio_id, :propiedad_id, 
                              (SELECT email_dueno FROM propiedades WHERE id = :prop_id), 'enviado')";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    ':envio_id' => $envioId,
                    ':propiedad_id' => $propiedadId,
                    ':prop_id' => $propiedadId
                ]);
            }

            $this->db->commit();
            return $envioId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al crear envío: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza contadores de éxito/fallo
     * @param int $envioId
     * @param int $exitosos
     * @param int $fallidos
     * @return bool
     */
    public function actualizarContadores(int $envioId, int $exitosos, int $fallidos): bool {
        return $this->update($envioId, [
            'total_exitosos' => $exitosos,
            'total_fallidos' => $fallidos
        ]);
    }

    /**
     * Marca un envío individual como fallido
     * @param int $detalleId
     * @param string $errorMsg
     * @return bool
     */
    public function marcarFallido(int $detalleId, string $errorMsg): bool {
        $sql = "UPDATE envios_correo_detalle 
                SET estado = 'error', error_msg = :error_msg 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $detalleId,
            ':error_msg' => $errorMsg
        ]);
    }

    /**
     * Obtiene historial de envíos por comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getByComunidad(int $comunidadId): array {
        $sql = "SELECT e.*, 
                       c.nombre as comunidad_nombre,
                       u.nombre as enviado_por_nombre
                FROM {$this->table} e
                LEFT JOIN comunidades c ON e.comunidad_id = c.id
                LEFT JOIN usuarios u ON e.enviado_por = u.id
                WHERE e.comunidad_id = :comunidad_id
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Procesa variables dinámicas en el cuerpo del mensaje
     * @param string $template
     * @param array $data Variables disponibles
     * @return string
     */
    public function procesarVariables(string $template, array $data): string {
        $variables = [
            '{nombre_propiedad}' => $data['propiedad_nombre'] ?? '',
            '{nombre_dueno}' => $data['nombre_dueno'] ?? '',
            '{monto_deuda}' => isset($data['monto_deuda']) ? formatMoney((float)$data['monto_deuda']) : '',
            '{mes}' => isset($data['mes']) ? getMonthName((int)$data['mes']) : '',
            '{anio}' => $data['anio'] ?? '',
            '{comunidad}' => $data['comunidad_nombre'] ?? '',
            '{direccion}' => $data['comunidad_direccion'] ?? '',
            '{presidente}' => $data['nombre_presidente'] ?? ''
        ];

        return str_replace(
            array_keys($variables),
            array_values($variables),
            $template
        );
    }

    /**
     * Reenvía un correo a una propiedad específica
     * @param int $detalleId
     * @return bool
     */
    public function reenviar(int $detalleId): bool {
        $sql = "UPDATE envios_correo_detalle 
                SET estado = 'enviado', error_msg = NULL 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $detalleId]);
    }

    /**
     * Obtiene todos los envíos con información de comunidad y usuario (paginado)
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllWithDetailsPaginated(int $offset, int $limit): array {
        $sql = "SELECT e.*, 
                       c.nombre as comunidad_nombre,
                       u.nombre as enviado_por_nombre
                FROM {$this->table} e
                LEFT JOIN comunidades c ON e.comunidad_id = c.id
                LEFT JOIN usuarios u ON e.enviado_por = u.id
                ORDER BY e.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta total de envíos
     * @return int
     */
    public function countEnvios(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}
