<?php
/**
 * Modelo Pago
 * Gestiona los pagos de gastos comunes
 */

require_once __DIR__ . '/Model.php';

class Pago extends Model {
    protected string $table = 'pagos';

    /**
     * Obtiene todos los pagos con información de propiedad y comunidad
     * @param int|null $limit
     * @return array
     */
    public function getAllWithDetails(?int $limit = null): array {
        $sql = "SELECT p.*, 
                       pr.nombre as propiedad_nombre, 
                       pr.nombre_dueno,
                       c.nombre as comunidad_nombre,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio DESC, d.mes DESC SEPARATOR ', ') as meses_pagados
                FROM {$this->table} p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN comunidades c ON pr.comunidad_id = c.id
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                GROUP BY p.id
                ORDER BY p.fecha DESC, p.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getByComunidad(int $comunidadId): array {
        $sql = "SELECT p.*, 
                       pr.nombre as propiedad_nombre, 
                       pr.nombre_dueno,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio DESC, d.mes DESC SEPARATOR ', ') as meses_pagados
                FROM {$this->table} p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                WHERE pr.comunidad_id = :comunidad_id
                GROUP BY p.id
                ORDER BY p.fecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un pago con todos sus detalles
     * @param int $pagoId
     * @return array|null
     */
    public function getWithDetails(int $pagoId): ?array {
        $sql = "SELECT p.*, 
                       pr.nombre as propiedad_nombre, 
                       pr.nombre_dueno,
                       pr.email_dueno,
                       pr.whatsapp_dueno,
                       pr.precio_gastos_comunes,
                       c.nombre as comunidad_nombre,
                       c.direccion as comunidad_direccion,
                       c.nombre_presidente,
                       c.email_presidente
                FROM {$this->table} p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN comunidades c ON pr.comunidad_id = c.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pagoId]);
        $pago = $stmt->fetch();
        
        if (!$pago) {
            return null;
        }

        // Obtener detalle de meses pagados
        $sqlDetalle = "SELECT pd.*, d.mes, d.anio, d.monto as monto_deuda
                       FROM pagos_detalle pd
                       LEFT JOIN deudas d ON pd.deuda_id = d.id
                       WHERE pd.pago_id = :pago_id
                       ORDER BY d.anio DESC, d.mes DESC";
        $stmtDetalle = $this->db->prepare($sqlDetalle);
        $stmtDetalle->execute([':pago_id' => $pagoId]);
        $pago['detalles'] = $stmtDetalle->fetchAll();
        
        return $pago;
    }

    /**
     * Registra un nuevo pago con sus detalles
     * @param array $data
     * @param array $deudaIds Array de IDs de deudas a pagar
     * @return int|false ID del pago creado o false en error
     */
    public function registrarPago(array $data, array $deudaIds) {
        try {
            $this->db->beginTransaction();

            // Insertar el pago
            $pagoData = [
                'propiedad_id' => $data['propiedad_id'],
                'fecha' => $data['fecha'],
                'monto' => $data['monto'],
                'observaciones' => $data['observaciones'] ?? '',
                'recibo_generado' => 0
            ];

            $pagoId = parent::create($pagoData);

            if (!$pagoId) {
                throw new Exception('Error al crear el pago');
            }

            // Insertar detalles y actualizar deudas
            foreach ($deudaIds as $deudaId) {
                // Insertar en pagos_detalle
                $sqlDetalle = "INSERT INTO pagos_detalle (pago_id, deuda_id, monto_pagado) 
                              VALUES (:pago_id, :deuda_id, 
                              (SELECT monto FROM deudas WHERE id = :deuda_id2))";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    ':pago_id' => $pagoId,
                    ':deuda_id' => $deudaId,
                    ':deuda_id2' => $deudaId
                ]);

                // Actualizar estado de la deuda
                $sqlUpdate = "UPDATE deudas SET estado = 'Pagado' WHERE id = :id";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute([':id' => $deudaId]);
            }

            $this->db->commit();
            return $pagoId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al registrar pago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene pagos del mes actual
     * @return array
     */
    public function getPagosMesActual(): array {
        $sql = "SELECT p.*, 
                       pr.nombre as propiedad_nombre, 
                       pr.nombre_dueno,
                       c.nombre as comunidad_nombre
                FROM {$this->table} p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN comunidades c ON pr.comunidad_id = c.id
                WHERE MONTH(p.fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(p.fecha) = YEAR(CURRENT_DATE())
                ORDER BY p.fecha DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el total recaudado en un período
     * @param int|null $mes
     * @param int|null $anio
     * @param int|null $comunidadId
     * @return float
     */
    public function getTotalRecaudado(?int $mes = null, ?int $anio = null, ?int $comunidadId = null): float {
        $sql = "SELECT SUM(p.monto) FROM {$this->table} p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                WHERE 1=1";
        
        $params = [];
        
        if ($mes) {
            $sql .= " AND MONTH(p.fecha) = :mes";
            $params[':mes'] = $mes;
        }
        
        if ($anio) {
            $sql .= " AND YEAR(p.fecha) = :anio";
            $params[':anio'] = $anio;
        }
        
        if ($comunidadId) {
            $sql .= " AND pr.comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * Genera número de recibo
     * @param int $pagoId
     * @return string
     */
    public function generarNumeroRecibo(int $pagoId): string {
        return 'REC-' . str_pad($pagoId, 6, '0', STR_PAD_LEFT) . '-' . date('Y');
    }

    /**
     * Marca recibo como generado
     * @param int $pagoId
     * @param string $path
     * @return bool
     */
    public function marcarReciboGenerado(int $pagoId, string $path): bool {
        return $this->update($pagoId, [
            'recibo_generado' => 1,
            'recibo_path' => $path
        ]);
    }
}
