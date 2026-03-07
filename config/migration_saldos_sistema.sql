-- ============================================
-- MIGRACIÓN: Sistema de Saldos y Pagos Anticipados
-- Fecha: 2024
-- Descripción: Agrega campo saldo a propiedades y tabla de movimientos
-- ROLLBACK: Ver archivo migration_saldos_rollback.sql
-- ============================================

-- 1. Agregar campo saldo a propiedades (default 0, permite valores negativos para deuda)
ALTER TABLE propiedades 
ADD COLUMN IF NOT EXISTS saldo DECIMAL(10, 2) DEFAULT 0.00 
AFTER whatsapp_agente;

-- 2. Crear tabla para historial de movimientos de saldo (opcional, permite trackear todo)
CREATE TABLE IF NOT EXISTS propiedades_saldo_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id INT NOT NULL,
    tipo_movimiento ENUM('ingreso', 'egreso', 'aplicacion_deuda') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    saldo_anterior DECIMAL(10, 2) NOT NULL,
    saldo_nuevo DECIMAL(10, 2) NOT NULL,
    referencia_tipo ENUM('pago', 'deuda', 'ajuste_manual', 'generacion_mensual') NULL,
    referencia_id INT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_propiedad (propiedad_id),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar campo para indicar si el pago usó saldo
ALTER TABLE pagos 
ADD COLUMN IF NOT EXISTS saldo_utilizado DECIMAL(10, 2) DEFAULT 0.00 
AFTER observaciones;

-- 4. Agregar campo para indicar si la deuda fue pagada con saldo
ALTER TABLE deudas 
ADD COLUMN IF NOT EXISTS pagada_con_saldo TINYINT(1) DEFAULT 0 
AFTER estado;

-- 5. Verificar que todo se creó correctamente
DESCRIBE propiedades;
DESCRIBE propiedades_saldo_historial;
DESCRIBE pagos;
DESCRIBE deudas;
