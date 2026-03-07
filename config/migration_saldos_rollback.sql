-- ============================================
-- ROLLBACK: Revertir Sistema de Saldos
-- Fecha: 2024
-- Descripción: Elimina todas las columnas y tablas agregadas
-- ADVERTENCIA: Esto eliminará el historial de movimientos
-- ============================================

-- 1. Eliminar tabla de historial (se pierden los movimientos)
DROP TABLE IF EXISTS propiedades_saldo_historial;

-- 2. Eliminar columnas agregadas
ALTER TABLE propiedades DROP COLUMN IF EXISTS saldo;
ALTER TABLE pagos DROP COLUMN IF EXISTS saldo_utilizado;
ALTER TABLE deudas DROP COLUMN IF EXISTS pagada_con_saldo;

-- 3. Verificar rollback
DESCRIBE propiedades;
DESCRIBE pagos;
DESCRIBE deudas;

-- Nota: Los datos existentes no se ven afectados, solo se elimina la funcionalidad
