-- ============================================
-- MIGRACIÓN: Soporte para Logo Dual (Modo Claro/Oscuro)
-- Agrega campo para almacenar logo para modo oscuro
-- ============================================

-- Agregar configuración para logo oscuro
INSERT INTO configuracion_sistema (clave, valor, tipo, descripcion) VALUES
('logo_path_dark', 'assets/images/logo_dark.png', 'archivo', 'Ruta del logo para modo oscuro')
ON DUPLICATE KEY UPDATE 
valor = VALUES(valor),
tipo = VALUES(tipo),
descripcion = VALUES(descripcion);

-- Verificar inserción
SELECT * FROM configuracion_sistema WHERE clave LIKE 'logo%';
