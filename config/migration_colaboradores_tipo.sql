-- ============================================
-- MIGRACIÓN: Actualizar tabla colaboradores
-- Agrega campos para soportar tipo de colaborador (Personal/Empresa)
-- ============================================

-- Agregar columna tipo_colaborador
ALTER TABLE colaboradores 
ADD COLUMN IF NOT EXISTS tipo_colaborador ENUM('personal', 'empresa') DEFAULT 'personal' 
AFTER id;

-- Agregar columna numero_cliente (solo para empresas)
ALTER TABLE colaboradores 
ADD COLUMN IF NOT EXISTS numero_cliente VARCHAR(50) NULL 
AFTER numero_cuenta;

-- Actualizar registros existentes como 'personal'
UPDATE colaboradores SET tipo_colaborador = 'personal' WHERE tipo_colaborador IS NULL;

-- Verificar que las columnas se agregaron correctamente
DESCRIBE colaboradores;
