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
     * @return array
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       c.whatsapp as colaborador_whatsapp,
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                ORDER BY pc.fecha DESC, pc.id DESC";
        
        $stmt = $this->db->query($sql);
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
}
