-- Migración: Agregar campo imagen_path a pagos_colaboradores
-- Fecha: 2026-03-29

ALTER TABLE pagos_colaboradores
ADD COLUMN imagen_path VARCHAR(255) NULL AFTER fecha;

-- Crear índice para búsquedas más rápidas
CREATE INDEX idx_pagos_colaboradores_imagen ON pagos_colaboradores(imagen_path);
