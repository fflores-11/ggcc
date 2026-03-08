-- Migración: Agregar campo comunidad_id a tabla usuarios
-- Fecha: 2026-03-08
-- Descripción: Permite asignar usuarios administradores a comunidades específicas

-- Agregar columna comunidad_id
ALTER TABLE usuarios 
ADD COLUMN comunidad_id INT NULL AFTER rol;

-- Agregar índice para búsquedas rápidas
ALTER TABLE usuarios 
ADD INDEX idx_comunidad (comunidad_id);

-- Agregar clave foránea (opcional - descomentar si se quiere integridad referencial)
-- ALTER TABLE usuarios 
-- ADD CONSTRAINT fk_usuarios_comunidad 
-- FOREIGN KEY (comunidad_id) REFERENCES comunidades(id) 
-- ON DELETE SET NULL 
-- ON UPDATE CASCADE;