<?php
/**
 * Modelo Dashboard
 * Obtiene métricas y estadísticas para el dashboard
 */

class Dashboard {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Obtiene métricas principales del sistema
     * @return array
     */
    public function getMetricas(): array {
        $metricas = [];

        // Total comunidades activas
        $sql = "SELECT COUNT(*) FROM comunidades WHERE activo = 1";
        $metricas['total_comunidades'] = (int) $this->db->query($sql)->fetchColumn();

        // Total propiedades activas
        $sql = "SELECT COUNT(*) FROM propiedades WHERE activo = 1";
        $metricas['total_propiedades'] = (int) $this->db->query($sql)->fetchColumn();

        // Total deuda pendiente
        $sql = "SELECT SUM(d.monto) FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE d.estado = 'Pendiente' AND p.activo = 1";
        $metricas['total_deuda'] = (float) ($this->db->query($sql)->fetchColumn() ?? 0);

        // Total recaudado (histórico)
        $sql = "SELECT SUM(monto) FROM pagos";
        $metricas['total_recaudado'] = (float) ($this->db->query($sql)->fetchColumn() ?? 0);

        // Pagos del mes actual
        $sql = "SELECT SUM(monto) FROM pagos 
                WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha) = YEAR(CURRENT_DATE())";
        $metricas['pagos_mes_actual'] = (float) ($this->db->query($sql)->fetchColumn() ?? 0);

        // Cantidad de pagos del mes
        $sql = "SELECT COUNT(*) FROM pagos 
                WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha) = YEAR(CURRENT_DATE())";
        $metricas['cantidad_pagos_mes'] = (int) $this->db->query($sql)->fetchColumn();

        // Deudas pendientes del mes actual
        $sql = "SELECT COUNT(*) FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE d.estado = 'Pendiente' 
                AND d.mes = MONTH(CURRENT_DATE())
                AND d.anio = YEAR(CURRENT_DATE())
                AND p.activo = 1";
        $metricas['deudas_pendientes_mes'] = (int) $this->db->query($sql)->fetchColumn();

        return $metricas;
    }

    /**
     * Obtiene comunidades con mayor deuda
     * @param int $limit
     * @return array
     */
    public function getComunidadesConMayorDeuda(int $limit = 5): array {
        $sql = "SELECT c.id, c.nombre, c.comuna,
                       COUNT(DISTINCT p.id) as total_propiedades,
                       SUM(CASE WHEN d.estado = 'Pendiente' THEN d.monto ELSE 0 END) as total_deuda,
                       COUNT(CASE WHEN d.estado = 'Pendiente' THEN 1 END) as deudas_pendientes
                FROM comunidades c
                LEFT JOIN propiedades p ON c.id = p.comunidad_id AND p.activo = 1
                LEFT JOIN deudas d ON p.id = d.propiedad_id AND d.estado = 'Pendiente'
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre, c.comuna
                HAVING total_deuda > 0
                ORDER BY total_deuda DESC
                LIMIT " . (int) $limit;
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene últimas actividades (pagos recientes)
     * @param int $limit
     * @return array
     */
    public function getUltimasActividades(int $limit = 10): array {
        $sql = "SELECT p.id, p.fecha, p.monto, p.created_at,
                       pr.nombre as propiedad_nombre,
                       pr.nombre_dueno,
                       c.nombre as comunidad_nombre,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio DESC, d.mes DESC SEPARATOR ', ') as meses_pagados
                FROM pagos p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN comunidades c ON pr.comunidad_id = c.id
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT " . (int) $limit;
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene últimas deudas generadas
     * @param int $limit
     * @return array
     */
    public function getUltimasDeudas(int $limit = 10): array {
        $sql = "SELECT d.id, d.mes, d.anio, d.monto, d.estado,
                       p.nombre as propiedad_nombre,
                       p.nombre_dueno,
                       c.nombre as comunidad_nombre
                FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE p.activo = 1
                ORDER BY d.created_at DESC
                LIMIT " . (int) $limit;
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene estadísticas mensuales para gráficos
     * @param int $meses
     * @return array
     */
    public function getEstadisticasMensuales(int $meses = 6): array {
        $resultados = [];
        
        for ($i = $meses - 1; $i >= 0; $i--) {
            $fecha = new DateTime();
            $fecha->modify("-{$i} months");
            
            $mes = (int) $fecha->format('n');
            $anio = (int) $fecha->format('Y');
            
            // Pagos del mes
            $sqlPagos = "SELECT SUM(monto) FROM pagos 
                        WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
            $stmtPagos = $this->db->prepare($sqlPagos);
            $stmtPagos->execute([':mes' => $mes, ':anio' => $anio]);
            $pagos = (float) ($stmtPagos->fetchColumn() ?? 0);
            
            // Deudas generadas del mes
            $sqlDeudas = "SELECT SUM(monto) FROM deudas d
                         LEFT JOIN propiedades p ON d.propiedad_id = p.id
                         WHERE d.mes = :mes AND d.anio = :anio AND p.activo = 1";
            $stmtDeudas = $this->db->prepare($sqlDeudas);
            $stmtDeudas->execute([':mes' => $mes, ':anio' => $anio]);
            $deudas = (float) ($stmtDeudas->fetchColumn() ?? 0);
            
            $resultados[] = [
                'mes' => $mes,
                'anio' => $anio,
                'mes_nombre' => getMonthName($mes),
                'pagos' => $pagos,
                'deudas' => $deudas
            ];
        }
        
        return $resultados;
    }

    /**
     * Obtiene propiedades morosas (con deudas antiguas)
     * @param int $limit
     * @return array
     */
    public function getPropiedadesMorosas(int $limit = 10): array {
        $sql = "SELECT p.id, p.nombre as propiedad_nombre, p.nombre_dueno,
                       c.nombre as comunidad_nombre,
                       COUNT(d.id) as meses_adeudados,
                       SUM(d.monto) as total_adeudado,
                       MIN(d.anio * 12 + d.mes) as primera_deuda_mes
                FROM propiedades p
                LEFT JOIN deudas d ON p.id = d.propiedad_id AND d.estado = 'Pendiente'
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE p.activo = 1 AND d.estado = 'Pendiente'
                GROUP BY p.id, p.nombre, p.nombre_dueno, c.nombre
                HAVING meses_adeudados > 0
                ORDER BY total_adeudado DESC
                LIMIT " . (int) $limit;
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene resumen por comunidad
     * @return array
     */
    public function getResumenPorComunidad(): array {
        $sql = "SELECT c.id, c.nombre, c.comuna,
                       COUNT(DISTINCT p.id) as total_propiedades,
                       SUM(CASE WHEN d.estado = 'Pagado' THEN d.monto ELSE 0 END) as total_pagado,
                       SUM(CASE WHEN d.estado = 'Pendiente' THEN d.monto ELSE 0 END) as total_deuda,
                       COUNT(CASE WHEN d.estado = 'Pagado' THEN 1 END) as pagos_realizados,
                       COUNT(CASE WHEN d.estado = 'Pendiente' THEN 1 END) as pagos_pendientes,
                       ROUND(
                           (COUNT(CASE WHEN d.estado = 'Pagado' THEN 1 END) * 100.0 / 
                            NULLIF(COUNT(d.id), 0)), 2
                       ) as porcentaje_cobranza
                FROM comunidades c
                LEFT JOIN propiedades p ON c.id = p.comunidad_id AND p.activo = 1
                LEFT JOIN deudas d ON p.id = d.propiedad_id
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre, c.comuna
                ORDER BY c.nombre";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
