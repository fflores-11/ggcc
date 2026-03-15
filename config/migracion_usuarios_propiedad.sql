-- ============================================
-- MIGRACION: Sistema de Usuarios por Propiedad
-- Fecha: 2025-03-15
-- ============================================

USE condominios_db;

-- ============================================
-- 1. AGREGAR CAMPOS A TABLA USUARIOS
-- ============================================

-- Agregar columna propiedad_id
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS propiedad_id INT NULL AFTER comunidad_id,
ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(20) NULL AFTER email,
ADD COLUMN IF NOT EXISTS es_propietario TINYINT(1) DEFAULT 0;

-- Agregar foreign key
ALTER TABLE usuarios
ADD CONSTRAINT fk_usuarios_propiedad 
FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE SET NULL;

-- Agregar índices
CREATE INDEX idx_propiedad_id ON usuarios(propiedad_id);
CREATE INDEX idx_es_propietario ON usuarios(es_propietario);

-- ============================================
-- 2. MODIFICAR ENUM DE ROL
-- ============================================

ALTER TABLE usuarios 
MODIFY COLUMN rol ENUM('admin', 'administrador', 'presidente', 'propietario') DEFAULT 'administrador';

-- ============================================
-- 3. VISTA PARA USUARIOS PROPIETARIOS CON INFO DE PROPIEDAD
-- ============================================

CREATE OR REPLACE VIEW vista_usuarios_propietarios AS
SELECT 
    u.id,
    u.nombre,
    u.email,
    u.whatsapp,
    u.password,
    u.rol,
    u.comunidad_id,
    u.propiedad_id,
    u.es_propietario,
    u.activo,
    u.ultimo_acceso,
    u.created_at,
    u.updated_at,
    p.nombre as propiedad_nombre,
    p.nombre_dueno,
    p.email_dueno,
    p.whatsapp_dueno,
    c.nombre as comunidad_nombre
FROM usuarios u
LEFT JOIN propiedades p ON u.propiedad_id = p.id
LEFT JOIN comunidades c ON u.comunidad_id = c.id
WHERE u.es_propietario = 1;

-- ============================================
-- FIN MIGRACION
-- ============================================
