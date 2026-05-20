-- ============================================
-- MIGRACIÓN: Sistema de Control de Caja Mensual
-- Fecha: 2024
-- Descripción: Tabla para controlar saldos de caja mes a mes
-- ROLLBACK: Eliminar tabla saldos_mensuales
-- ============================================

-- Tabla para control de caja mensual
CREATE TABLE IF NOT EXISTS saldos_mensuales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comunidad_id INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,
    -- Montos calculados automáticamente
    total_ingresos_gc DECIMAL(12, 2) DEFAULT 0.00,  -- Pagos de gastos comunes recibidos
    total_egresos_colaboradores DECIMAL(12, 2) DEFAULT 0.00,  -- Pagos a colaboradores
    saldo_mes_anterior DECIMAL(12, 2) DEFAULT 0.00,  -- Saldo que viene del mes anterior
    -- Ajustes manuales
    ajustes_ingreso DECIMAL(12, 2) DEFAULT 0.00,  -- Otros ingresos (donaciones, multas, etc)
    ajustes_egreso DECIMAL(12, 2) DEFAULT 0.00,  -- Otros egresos (gastos extraordinarios)
    descripcion_ajustes TEXT,  -- Descripción de los ajustes
    -- Saldo calculado
    saldo_calculado DECIMAL(12, 2) DEFAULT 0.00,  -- (ingresos + saldo_anterior + ajustes_ingreso) - (egresos + ajustes_egreso)
    -- Saldo final (puede ser modificado manualmente si hay diferencias)
    saldo_final DECIMAL(12, 2) DEFAULT 0.00,
    cerrado TINYINT(1) DEFAULT 0,  -- Indica si el mes está cerrado (no se pueden hacer más cambios)
    fecha_cierre DATETIME NULL,
    cerrado_por INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Constraints
    UNIQUE KEY uk_comunidad_periodo (comunidad_id, anio, mes),
    FOREIGN KEY (comunidad_id) REFERENCES comunidades(id) ON DELETE CASCADE,
    FOREIGN KEY (cerrado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_comunidad (comunidad_id),
    INDEX idx_periodo (anio, mes),
    INDEX idx_cerrado (cerrado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista para calcular automáticamente los totales por período
CREATE OR REPLACE VIEW vista_caja_mensual AS
SELECT 
    sm.id,
    sm.comunidad_id,
    c.nombre as comunidad_nombre,
    sm.anio,
    sm.mes,
    sm.saldo_mes_anterior,
    -- Ingresos calculados
    sm.total_ingresos_gc + sm.ajustes_ingreso as total_ingresos,
    -- Egresos calculados  
    sm.total_egresos_colaboradores + sm.ajustes_egreso as total_egresos,
    -- Saldo teórico
    (sm.saldo_mes_anterior + sm.total_ingresos_gc + sm.ajustes_ingreso) - 
    (sm.total_egresos_colaboradores + sm.ajustes_egreso) as saldo_teorico,
    sm.saldo_final as saldo_real,
    -- Diferencia
    sm.saldo_final - ((sm.saldo_mes_anterior + sm.total_ingresos_gc + sm.ajustes_ingreso) - 
    (sm.total_egresos_colaboradores + sm.ajustes_egreso)) as diferencia,
    sm.cerrado,
    sm.fecha_cierre,
    sm.descripcion_ajustes
FROM saldos_mensuales sm
LEFT JOIN comunidades c ON sm.comunidad_id = c.id;

-- Verificar creación
DESCRIBE saldos_mensuales;
SHOW CREATE VIEW vista_caja_mensual;
