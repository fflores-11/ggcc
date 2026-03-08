<?php
/**
 * Modelo SaldoMensual
 * Gestiona el control de caja mensual de las comunidades
 */

require_once __DIR__ . '/Model.php';

class SaldoMensual extends Model {
    protected string $table = 'saldos_mensuales';

    /**
     * Obtiene o crea el registro de saldo para un período específico
     * @param int $comunidadId
     * @param int $anio
     * @param int $mes
     * @return array
     */
    public function getOrCreate(int $comunidadId, int $anio, int $mes): array {
        // Buscar si existe
        $sql = "SELECT * FROM {$this->table} 
                WHERE comunidad_id = :comunidad_id AND anio = :anio AND mes = :mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);
        $saldo = $stmt->fetch();

        if ($saldo) {
            // Actualizar totales automáticos
            $this->actualizarTotales($saldo['id']);
            // Recargar con datos actualizados
            return $this->find($saldo['id']);
        }

        // Si no existe, crearlo
        return $this->crearNuevoPeriodo($comunidadId, $anio, $mes);
    }

    /**
     * Crea un nuevo período de saldo
     * @param int $comunidadId
     * @param int $anio
     * @param int $mes
     * @return array
     */
    private function crearNuevoPeriodo(int $comunidadId, int $anio, int $mes): array {
        // Obtener saldo del mes anterior
        $saldoAnterior = $this->getSaldoMesAnterior($comunidadId, $anio, $mes);

        $data = [
            'comunidad_id' => $comunidadId,
            'anio' => $anio,
            'mes' => $mes,
            'saldo_mes_anterior' => $saldoAnterior,
            'total_ingresos_gc' => 0,
            'total_egresos_colaboradores' => 0,
            'ajustes_ingreso' => 0,
            'ajustes_egreso' => 0,
            'saldo_calculado' => $saldoAnterior,
            'saldo_final' => $saldoAnterior,
            'cerrado' => 0
        ];

        $id = $this->create($data);
        
        // Actualizar totales automáticos
        $this->actualizarTotales($id);
        
        return $this->find($id);
    }

    /**
     * Obtiene el saldo final del mes anterior
     * @param int $comunidadId
     * @param int $anio
     * @param int $mes
     * @return float
     */
    private function getSaldoMesAnterior(int $comunidadId, int $anio, int $mes): float {
        // Calcular mes anterior
        $mesAnterior = $mes - 1;
        $anioAnterior = $anio;
        if ($mesAnterior == 0) {
            $mesAnterior = 12;
            $anioAnterior = $anio - 1;
        }

        // Buscar el saldo del mes anterior (sin importar si está cerrado o no)
        $sql = "SELECT saldo_final FROM {$this->table} 
                WHERE comunidad_id = :comunidad_id AND anio = :anio AND mes = :mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':anio' => $anioAnterior,
            ':mes' => $mesAnterior
        ]);
        
        $result = $stmt->fetchColumn();
        return $result !== false ? (float) $result : 0;
    }

    /**
     * Actualiza los totales automáticos desde pagos y colaboradores
     * @param int $saldoId
     * @return bool
     */
    public function actualizarTotales(int $saldoId): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo) return false;

        // Calcular ingresos de gastos comunes
        $ingresosGC = $this->calcularIngresosGC($saldo['comunidad_id'], $saldo['mes'], $saldo['anio']);
        
        // Calcular egresos a colaboradores
        $egresosCol = $this->calcularEgresosColaboradores($saldo['comunidad_id'], $saldo['mes'], $saldo['anio']);

        // Calcular saldo
        $saldoCalculado = ($saldo['saldo_mes_anterior'] + $ingresosGC + $saldo['ajustes_ingreso']) 
                        - ($egresosCol + $saldo['ajustes_egreso']);

        $data = [
            'total_ingresos_gc' => $ingresosGC,
            'total_egresos_colaboradores' => $egresosCol,
            'saldo_calculado' => $saldoCalculado
        ];

        // Si no hay saldo_final manual, usar el calculado
        if ($saldo['saldo_final'] == 0 || $saldo['saldo_final'] == $saldo['saldo_calculado']) {
            $data['saldo_final'] = $saldoCalculado;
        }

        return $this->update($saldoId, $data);
    }

    /**
     * Calcula ingresos por gastos comunes
     * @param int $comunidadId
     * @param int $mes
     * @param int $anio
     * @return float
     */
    private function calcularIngresosGC(int $comunidadId, int $mes, int $anio): float {
        $sql = "SELECT COALESCE(SUM(p.monto), 0) 
                FROM pagos p
                JOIN propiedades pr ON p.propiedad_id = pr.id
                WHERE pr.comunidad_id = :comunidad_id 
                AND MONTH(p.fecha) = :mes 
                AND YEAR(p.fecha) = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return (float) $stmt->fetchColumn();
    }

    /**
     * Calcula egresos a colaboradores
     * @param int $comunidadId
     * @param int $mes
     * @param int $anio
     * @return float
     */
    private function calcularEgresosColaboradores(int $comunidadId, int $mes, int $anio): float {
        // Nota: Los pagos a colaboradores no están vinculados directamente a comunidad
        // Asumimos que son a nivel sistema, o podemos agregar filtro si es necesario
        $sql = "SELECT COALESCE(SUM(pc.monto), 0) 
                FROM pagos_colaboradores pc
                WHERE MONTH(pc.fecha) = :mes 
                AND YEAR(pc.fecha) = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return (float) $stmt->fetchColumn();
    }

    /**
     * Agrega un ajuste manual de ingreso
     * @param int $saldoId
     * @param float $monto
     * @param string $descripcion
     * @return bool
     */
    public function agregarAjusteIngreso(int $saldoId, float $monto, string $descripcion): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo || $saldo['cerrado']) return false;

        $nuevoAjuste = $saldo['ajustes_ingreso'] + $monto;
        $nuevaDescripcion = $saldo['descripcion_ajustes'] 
            ? $saldo['descripcion_ajustes'] . "\n+ Ingreso: $monto - $descripcion"
            : "+ Ingreso: $monto - $descripcion";

        return $this->update($saldoId, [
            'ajustes_ingreso' => $nuevoAjuste,
            'descripcion_ajustes' => $nuevaDescripcion
        ]);
    }

    /**
     * Agrega un ajuste manual de egreso
     * @param int $saldoId
     * @param float $monto
     * @param string $descripcion
     * @return bool
     */
    public function agregarAjusteEgreso(int $saldoId, float $monto, string $descripcion): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo || $saldo['cerrado']) return false;

        $nuevoAjuste = $saldo['ajustes_egreso'] + $monto;
        $nuevaDescripcion = $saldo['descripcion_ajustes'] 
            ? $saldo['descripcion_ajustes'] . "\n- Egreso: $monto - $descripcion"
            : "- Egreso: $monto - $descripcion";

        return $this->update($saldoId, [
            'ajustes_egreso' => $nuevoAjuste,
            'descripcion_ajustes' => $nuevaDescripcion
        ]);
    }

    /**
     * Establece saldo final manual
     * @param int $saldoId
     * @param float $saldoFinal
     * @return bool
     */
    public function establecerSaldoFinal(int $saldoId, float $saldoFinal): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo || $saldo['cerrado']) return false;

        return $this->update($saldoId, ['saldo_final' => $saldoFinal]);
    }

    /**
     * Cierra el período mensual
     * @param int $saldoId
     * @param int $usuarioId
     * @return bool
     */
    public function cerrarPeriodo(int $saldoId, int $usuarioId): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo || $saldo['cerrado']) return false;

        return $this->update($saldoId, [
            'cerrado' => 1,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'cerrado_por' => $usuarioId
        ]);
    }

    /**
     * Obtiene todos los períodos de una comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getByComunidad(int $comunidadId): array {
        $sql = "SELECT sm.*, 
                (sm.total_ingresos_gc + sm.ajustes_ingreso) as total_ingresos,
                (sm.total_egresos_colaboradores + sm.ajustes_egreso) as total_egresos,
                sm.saldo_final as saldo_real,
                (sm.saldo_final - ((sm.saldo_mes_anterior + sm.total_ingresos_gc + sm.ajustes_ingreso) - 
                (sm.total_egresos_colaboradores + sm.ajustes_egreso))) as diferencia
                FROM {$this->table} sm
                WHERE sm.comunidad_id = :comunidad_id
                ORDER BY sm.anio DESC, sm.mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene resumen de todos los períodos
     * @return array
     */
    public function getAllResumen(): array {
        $sql = "SELECT sm.*, c.nombre as comunidad_nombre,
                (sm.total_ingresos_gc + sm.ajustes_ingreso) as total_ingresos,
                (sm.total_egresos_colaboradores + sm.ajustes_egreso) as total_egresos
                FROM {$this->table} sm
                LEFT JOIN comunidades c ON sm.comunidad_id = c.id
                ORDER BY sm.anio DESC, sm.mes DESC, c.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el saldo final de un período específico
     * @param int $comunidadId
     * @param int $anio
     * @param int $mes
     * @return float|null Saldo final si existe y está cerrado, null si no
     */
    public function getSaldoPeriodo(int $comunidadId, int $anio, int $mes): ?float {
        $sql = "SELECT saldo_final FROM {$this->table} 
                WHERE comunidad_id = :comunidad_id AND anio = :anio AND mes = :mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);
        
        $result = $stmt->fetch();
        return $result ? (float) $result['saldo_final'] : null;
    }

    /**
     * Obtiene resumen financiero del mes actual para todas las comunidades
     * @param int $mes
     * @param int $anio
     * @return array Resumen con totales
     */
    public function getResumenFinancieroMensual(int $mes, int $anio): array {
        $sql = "SELECT 
                    COUNT(DISTINCT comunidad_id) as total_comunidades,
                    SUM(saldo_mes_anterior) as saldo_anterior_total,
                    SUM(total_ingresos_gc + ajustes_ingreso) as total_ingresos,
                    SUM(total_egresos_colaboradores + ajustes_egreso) as total_egresos,
                    SUM(saldo_final) as saldo_actual_total
                FROM {$this->table}
                WHERE mes = :mes AND anio = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mes' => $mes, ':anio' => $anio]);
        $resumen = $stmt->fetch();
        
        return [
            'total_comunidades' => (int) ($resumen['total_comunidades'] ?? 0),
            'saldo_anterior' => (float) ($resumen['saldo_anterior_total'] ?? 0),
            'total_ingresos' => (float) ($resumen['total_ingresos'] ?? 0),
            'total_egresos' => (float) ($resumen['total_egresos'] ?? 0),
            'saldo_actual' => (float) ($resumen['saldo_actual_total'] ?? 0)
        ];
    }

    /**
     * Elimina un registro de saldo mensual (solo si no está cerrado)
     * @param int $saldoId
     * @return bool
     */
    public function eliminarPeriodo(int $saldoId): bool {
        $saldo = $this->find($saldoId);
        if (!$saldo) return false;
        
        // No permitir eliminar períodos cerrados
        if ($saldo['cerrado']) return false;
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $saldoId]);
    }
}
