-- ============================================
-- TABLA: mascotas
-- ============================================
CREATE TABLE IF NOT EXISTS mascotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Gato', 'Perro', 'Ave', 'Hamster') NOT NULL,
    edad INT DEFAULT 0,
    alimento VARCHAR(255),
    imagen_path VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_propiedad (propiedad_id),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FIN DE LA MIGRACIÓN
-- ============================================
