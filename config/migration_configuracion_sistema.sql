-- ============================================
-- MIGRACIÓN: Configuración del Sistema
-- Almacena configuraciones generales del sistema
-- ============================================

CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(50) DEFAULT 'texto', -- texto, numero, booleano, archivo
    descripcion VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones por defecto
INSERT INTO configuracion_sistema (clave, valor, tipo, descripcion) VALUES
('logo_path', 'assets/images/logo.png', 'archivo', 'Ruta del logo del sistema'),
('nombre_sistema', 'GGCC - Gestión Gastos Comunes', 'texto', 'Nombre del sistema'),
('version', '1.0', 'texto', 'Versión del sistema'),
('moneda', 'CLP', 'texto', 'Moneda por defecto')
ON DUPLICATE KEY UPDATE 
valor = VALUES(valor),
tipo = VALUES(tipo),
descripcion = VALUES(descripcion);

-- Verificar inserción
SELECT * FROM configuracion_sistema;
