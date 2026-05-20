-- Migración: Agregar configuración de imagen de fondo para página de login
-- Fecha: 2026-03-08

-- Insertar configuración para imagen de fondo si no existe
INSERT INTO configuracion_sistema (clave, valor, tipo, descripcion) 
SELECT 'login_background_image', '', 'string', 'Ruta de la imagen de fondo para la página de inicio (login). Dejar vacío para usar fondo azul por defecto.'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'login_background_image');

-- Insertar configuración para modo de fondo (cover, contain, repeat)
INSERT INTO configuracion_sistema (clave, valor, tipo, descripcion) 
SELECT 'login_background_mode', 'cover', 'string', 'Modo de visualización de la imagen de fondo: cover, contain, repeat'
WHERE NOT EXISTS (SELECT 1 FROM configuracion_sistema WHERE clave = 'login_background_mode');