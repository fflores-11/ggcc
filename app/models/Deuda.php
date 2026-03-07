<?php
/**
 * Modelo Deuda
 * Gestiona las deudas de gastos comunes de las propiedades
 */

require_once __DIR__ . '/Model.php';

class Deuda extends Model {
    protected string $table = 'deudas';

    /**
     * Obtiene deudas pendientes de una propiedad
     * @param int $propiedadId
     * @return array
     */
    public function getPendientesByPropiedad(int $propiedadId): array {
        $sql = "SELECT d.*, 
                       p.nombre as propiedad_nombre,
                       p.nombre_dueno,
                       c.nombre as comunidad_nombre
                FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE d.propiedad_id = :propiedad_id 
                AND d.estado = 'Pendiente'
                ORDER BY d.anio DESC, d.mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las deudas pendientes de una comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getPendientesByComunidad(int $comunidadId): array {
        $sql = "SELECT d.*, 
                       p.nombre as propiedad_nombre,
                       p.nombre_dueno,
                       p.email_dueno,
                       c.nombre as comunidad_nombre
                FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE p.comunidad_id = :comunidad_id 
                AND d.estado = 'Pendiente'
                AND p.activo = 1
                ORDER BY p.nombre, d.anio DESC, d.mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el total de deuda de una propiedad
     * @param int $propiedadId
     * @return float
     */
    public function getTotalDeudaPropiedad(int $propiedadId): float {
        $sql = "SELECT SUM(monto) FROM {$this->table} 
                WHERE propiedad_id = :propiedad_id AND estado = 'Pendiente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * Calcula el total de deuda de una comunidad
     * @param int $comunidadId
     * @return float
     */
    public function getTotalDeudaComunidad(int $comunidadId): float {
        $sql = "SELECT SUM(d.monto) FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE p.comunidad_id = :comunidad_id 
                AND d.estado = 'Pendiente'
                AND p.activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * Genera deudas mensuales para todas las propiedades de una comunidad
     * @param int $comunidadId
     * @param int $mes
     * @param int $anio
     * @return int Número de deudas generadas
     */
    public function generarDeudasMes(int $comunidadId, int $mes, int $anio): int {
        $sql = "INSERT INTO {$this->table} (propiedad_id, mes, anio, monto, estado)
                SELECT p.id, :mes, :anio, p.precio_gastos_comunes, 'Pendiente'
                FROM propiedades p
                WHERE p.comunidad_id = :comunidad_id 
                AND p.activo = 1
                AND NOT EXISTS (
                    SELECT 1 FROM deudas d 
                    WHERE d.propiedad_id = p.id 
                    AND d.mes = :mes2 
                    AND d.anio = :anio2
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':mes' => $mes,
            ':anio' => $anio,
            ':mes2' => $mes,
            ':anio2' => $anio
        ]);
        
        return $stmt->rowCount();
    }

    /**
     * Obtiene períodos únicos donde hay deudas en una comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getPeriodosPorComunidad(int $comunidadId): array {
        $sql = "SELECT DISTINCT d.mes, d.anio 
                FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE p.comunidad_id = :comunidad_id
                AND p.activo = 1
                ORDER BY d.anio DESC, d.mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Crea deudas para una nueva propiedad en períodos existentes
     * @param int $propiedadId
     * @param float $monto
     * @param int $comunidadId
     * @return int
     */
    public function crearDeudasNuevaPropiedad(int $propiedadId, float $monto, int $comunidadId): int {
        $periodos = $this->getPeriodosPorComunidad($comunidadId);
        $creadas = 0;
        
        foreach ($periodos as $periodo) {
            // Verificar que no exista ya
            $sqlCheck = "SELECT COUNT(*) FROM {$this->table} 
                        WHERE propiedad_id = :propiedad_id 
                        AND mes = :mes AND anio = :anio";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([
                ':propiedad_id' => $propiedadId,
                ':mes' => $periodo['mes'],
                ':anio' => $periodo['anio']
            ]);
            
            if ($stmtCheck->fetchColumn() == 0) {
                // Crear la deuda
                $sqlInsert = "INSERT INTO {$this->table} 
                             (propiedad_id, mes, anio, monto, estado) 
                             VALUES (:propiedad_id, :mes, :anio, :monto, 'Pendiente')";
                $stmtInsert = $this->db->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':propiedad_id' => $propiedadId,
                    ':mes' => $periodo['mes'],
                    ':anio' => $periodo['anio'],
                    ':monto' => $monto
                ]);
                $creadas++;
            }
        }
        
        return $creadas;
    }

    /**
     * Obtiene deuda por ID
     * @param int $deudaId
     * @return array|null
     */
    public function getWithPropiedad(int $deudaId): ?array {
        $sql = "SELECT d.*, 
                       p.nombre as propiedad_nombre,
                       p.nombre_dueno,
                       p.email_dueno,
                       c.nombre as comunidad_nombre
                FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE d.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $deudaId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtiene resumen de deudas por mes y año
     * @param int $comunidadId
     * @param int $anio
     * @return array
     */
    public function getResumenPorMes(int $comunidadId, int $anio): array {
        $sql = "SELECT 
                    d.mes,
                    COUNT(*) as total_propiedades,
                    SUM(CASE WHEN d.estado = 'Pagado' THEN 1 ELSE 0 END) as pagos_realizados,
                    SUM(CASE WHEN d.estado = 'Pendiente' THEN 1 ELSE 0 END) as pagos_pendientes,
                    SUM(CASE WHEN d.estado = 'Pagado' THEN d.monto ELSE 0 END) as monto_pagado,
                    SUM(CASE WHEN d.estado = 'Pendiente' THEN d.monto ELSE 0 END) as monto_pendiente
                FROM {$this->table} d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE p.comunidad_id = :comunidad_id 
                AND d.anio = :anio
                AND p.activo = 1
                GROUP BY d.mes
                ORDER BY d.mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':anio' => $anio
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica si una deuda existe y está pendiente
     * @param int $deudaId
     * @return bool
     */
    public function isPendiente(int $deudaId): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE id = :id AND estado = 'Pendiente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $deudaId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crea una deuda manualmente
     * @param array $data
     * @return int|false
     */
    public function createDeuda(array $data) {
        // Verificar que no exista duplicada
        $sql = "SELECT id FROM {$this->table} 
                WHERE propiedad_id = :propiedad_id 
                AND mes = :mes 
                AND anio = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':propiedad_id' => $data['propiedad_id'],
            ':mes' => $data['mes'],
            ':anio' => $data['anio']
        ]);
        
        if ($stmt->fetch()) {
            return false; // Ya existe
        }
        
        return parent::create($data);
    }

    /**
     * Intenta pagar deudas pendientes con el saldo disponible de la propiedad
     * @param int $propiedadId
     * @return array ['deudas_pagadas' => int, 'monto_aplicado' => float, 'saldo_restante' => float]
     */
    public function intentarPagoConSaldo(int $propiedadId): array {
        $resultado = [
            'deudas_pagadas' => 0,
            'monto_aplicado' => 0,
            'saldo_restante' => 0
        ];

        try {
            $this->db->beginTransaction();

            // Obtener saldo actual
            $propiedadModel = new Propiedad();
            $saldoDisponible = $propiedadModel->getSaldo($propiedadId);

            if ($saldoDisponible <= 0) {
                $this->db->commit();
                return $resultado;
            }

            // Obtener deudas pendientes ordenadas por antigüedad
            $deudasPendientes = $this->getPendientesByPropiedad($propiedadId);
            
            if (empty($deudasPendientes)) {
                $this->db->commit();
                $resultado['saldo_restante'] = $saldoDisponible;
                return $resultado;
            }

            $saldoRestante = $saldoDisponible;
            $montoTotalAplicado = 0;
            $deudasPagadas = [];

            foreach ($deudasPendientes as $deuda) {
                if ($saldoRestante <= 0) {
                    break;
                }

                $montoDeuda = (float) $deuda['monto'];

                if ($saldoRestante >= $montoDeuda) {
                    // Pagar la deuda completa con saldo
                    $propiedadModel->aplicarSaldoADeuda(
                        $propiedadId,
                        $montoDeuda,
                        $deuda['id'],
                        "Pago automático con saldo disponible"
                    );

                    // Marcar deuda como pagada con saldo
                    $sqlUpdate = "UPDATE {$this->table} SET estado = 'Pagado', pagada_con_saldo = 1 WHERE id = :id";
                    $stmtUpdate = $this->db->prepare($sqlUpdate);
                    $stmtUpdate->execute([':id' => $deuda['id']]);

                    $saldoRestante -= $montoDeuda;
                    $montoTotalAplicado += $montoDeuda;
                    $deudasPagadas[] = $deuda['id'];
                }
            }

            $this->db->commit();

            return [
                'deudas_pagadas' => count($deudasPagadas),
                'monto_aplicado' => $montoTotalAplicado,
                'saldo_restante' => $saldoRestante,
                'deuda_ids' => $deudasPagadas
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al intentar pago con saldo: ' . $e->getMessage());
            return $resultado;
        }
    }

    /**
     * Genera deudas mensuales e intenta aplicar saldos automáticamente
     * @param int $comunidadId
     * @param int $mes
     * @param int $anio
     * @param bool $aplicarSaldos Si es true, intenta pagar con saldo disponible
     * @return array ['deudas_generadas' => int, 'saldos_aplicados' => array]
     */
    public function generarDeudasMesConSaldo(int $comunidadId, int $mes, int $anio, bool $aplicarSaldos = false): array {
        // Generar deudas normalmente
        $deudasGeneradas = $this->generarDeudasMes($comunidadId, $mes, $anio);
        
        $resultado = [
            'deudas_generadas' => $deudasGeneradas,
            'saldos_aplicados' => []
        ];

        if ($aplicarSaldos && $deudasGeneradas > 0) {
            // Obtener propiedades afectadas
            $sql = "SELECT DISTINCT propiedad_id FROM {$this->table} 
                    WHERE mes = :mes AND anio = :anio 
                    AND propiedad_id IN (
                        SELECT id FROM propiedades WHERE comunidad_id = :comunidad_id AND activo = 1
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':mes' => $mes,
                ':anio' => $anio,
                ':comunidad_id' => $comunidadId
            ]);
            $propiedades = $stmt->fetchAll();

            foreach ($propiedades as $prop) {
                $aplicacion = $this->intentarPagoConSaldo($prop['propiedad_id']);
                if ($aplicacion['deudas_pagadas'] > 0) {
                    $resultado['saldos_aplicados'][] = [
                        'propiedad_id' => $prop['propiedad_id'],
                        'deudas_pagadas' => $aplicacion['deudas_pagadas'],
                        'monto_aplicado' => $aplicacion['monto_aplicado']
                    ];
                }
            }
        }

        return $resultado;
    }
}
