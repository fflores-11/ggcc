<?php
/**
 * Modelo PagoColaborador
 * Gestiona los pagos realizados a colaboradores
 */

require_once __DIR__ . '/Model.php';

class PagoColaborador extends Model {
    protected string $table = 'pagos_colaboradores';

    /**
     * Obtiene todos los pagos con información del colaborador y usuario
     * @param int|null $limit
     * @return array
     */
    public function getAllWithDetails(?int $limit = null): array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       c.whatsapp as colaborador_whatsapp,
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                ORDER BY pc.fecha DESC, pc.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los pagos con información del colaborador y usuario (paginado)
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllWithDetailsPaginated(int $offset, int $limit): array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       c.whatsapp as colaborador_whatsapp,
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                ORDER BY pc.fecha DESC, pc.id DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por colaborador
     * @param int $colaboradorId
     * @return array
     */
    public function getByColaborador(int $colaboradorId): array {
        $sql = "SELECT pc.*, 
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.colaborador_id = :colaborador_id
                ORDER BY pc.fecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por colaborador con paginación
     * @param int $colaboradorId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByColaboradorPaginated(int $colaboradorId, int $offset, int $limit): array {
        $sql = "SELECT pc.*, 
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.colaborador_id = :colaborador_id
                ORDER BY pc.fecha DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':colaborador_id', $colaboradorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene resumen de pagos por período
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function getResumenPeriodo(string $fechaInicio, string $fechaFin): array {
        $sql = "SELECT 
                    DATE_FORMAT(pc.fecha, '%Y-%m') as mes,
                    COUNT(*) as cantidad_pagos,
                    SUM(pc.monto) as total_pagado
                FROM {$this->table} pc
                WHERE pc.fecha BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE_FORMAT(pc.fecha, '%Y-%m')
                ORDER BY mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el total pagado a un colaborador
     * @param int $colaboradorId
     * @return float
     */
    public function getTotalPagado(int $colaboradorId): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM {$this->table} WHERE colaborador_id = :colaborador_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Calcula el total pagado en el mes actual
     * @return float
     */
    public function getTotalMesActual(): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM {$this->table} 
                WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Valida datos del pago
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data): array {
        $errors = [];

        if (empty($data['colaborador_id'])) {
            $errors[] = 'Debe seleccionar un colaborador';
        }

        if (empty($data['detalle'])) {
            $errors[] = 'El detalle del pago es obligatorio';
        }

        if (!isset($data['monto']) || $data['monto'] <= 0) {
            $errors[] = 'El monto debe ser mayor a 0';
        }

        if (empty($data['fecha'])) {
            $errors[] = 'La fecha es obligatoria';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene un pago con su imagen
     * @param int $pagoId
     * @return array|null
     */
    public function getWithImagen(int $pagoId): ?array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.id = :id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pagoId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Actualiza la ruta de la imagen de un pago
     * @param int $pagoId
     * @param string $imagenPath
     * @return bool
     */
    public function updateImagenPath(int $pagoId, string $imagenPath): bool {
        $sql = "UPDATE {$this->table} SET imagen_path = :imagen_path WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':imagen_path' => $imagenPath, ':id' => $pagoId]);
    }

    /**
     * Obtiene la ruta de la imagen de un pago
     * @param int $pagoId
     * @return string|null
     */
    public function getImagenPath(int $pagoId): ?string {
        $sql = "SELECT imagen_path FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pagoId]);
        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    /**
     * Elimina la imagen de un pago (solo de la BD, no el archivo físico)
     * @param int $pagoId
     * @return bool
     */
    public function deleteImagen(int $pagoId): bool {
        return $this->updateImagenPath($pagoId, '');
    }

    /**
     * Cuenta pagos por colaborador
     * @param int $colaboradorId
     * @return int
     */
    public function countByColaborador(int $colaboradorId): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE colaborador_id = :colaborador_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cuenta total de pagos a colaboradores
     * @return int
     */
    public function countPagos(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}
