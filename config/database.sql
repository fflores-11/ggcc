-- Sistema de Administración de Gastos Comunes de Condominios
-- PHP 8.1 + MySQL/MariaDB
-- Script de creación de base de datos y tablas

CREATE DATABASE IF NOT EXISTS condominios_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE condominios_db;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    whatsapp VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'administrador', 'presidente', 'propietario') DEFAULT 'administrador',
    comunidad_id INT NULL,
    propiedad_id INT NULL,
    es_propietario TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo),
    INDEX idx_comunidad_id (comunidad_id),
    INDEX idx_propiedad_id (propiedad_id),
    INDEX idx_es_propietario (es_propietario),
    FOREIGN KEY (comunidad_id) REFERENCES comunidades(id) ON DELETE SET NULL,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: comunidades
-- ============================================
CREATE TABLE IF NOT EXISTS comunidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion TEXT NOT NULL,
    pais VARCHAR(50) DEFAULT 'Chile',
    region VARCHAR(100) NOT NULL,
    comuna VARCHAR(100) NOT NULL,
    nombre_presidente VARCHAR(100) NOT NULL,
    whatsapp_presidente VARCHAR(20),
    email_presidente VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_activo (activo),
    INDEX idx_comuna (comuna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: propiedades
-- ============================================
CREATE TABLE IF NOT EXISTS propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comunidad_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Casa', 'Departamento', 'Parcela') NOT NULL,
    precio_gastos_comunes DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    nombre_dueno VARCHAR(100) NOT NULL,
    email_dueno VARCHAR(100) NOT NULL,
    whatsapp_dueno VARCHAR(20),
    nombre_agente VARCHAR(100),
    email_agente VARCHAR(100),
    whatsapp_agente VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (comunidad_id) REFERENCES comunidades(id) ON DELETE CASCADE,
    INDEX idx_comunidad (comunidad_id),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    INDEX idx_email_dueno (email_dueno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: deudas
-- ============================================
CREATE TABLE IF NOT EXISTS deudas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id INT NOT NULL,
    mes INT NOT NULL,
    anio INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado ENUM('Pendiente', 'Pagado') DEFAULT 'Pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    UNIQUE KEY uk_propiedad_mes_anio (propiedad_id, mes, anio),
    INDEX idx_propiedad (propiedad_id),
    INDEX idx_mes_anio (mes, anio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: pagos
-- ============================================
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    propiedad_id INT NOT NULL,
    fecha DATE NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    observaciones TEXT,
    recibo_generado TINYINT(1) DEFAULT 0,
    recibo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_propiedad (propiedad_id),
    INDEX idx_fecha (fecha),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: pagos_detalle (meses pagados en cada pago)
-- ============================================
CREATE TABLE IF NOT EXISTS pagos_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT NOT NULL,
    deuda_id INT NOT NULL,
    monto_pagado DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    FOREIGN KEY (deuda_id) REFERENCES deudas(id) ON DELETE CASCADE,
    INDEX idx_pago (pago_id),
    INDEX idx_deuda (deuda_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: envios_correo
-- ============================================
CREATE TABLE IF NOT EXISTS envios_correo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comunidad_id INT NOT NULL,
    tipo ENUM('general', 'cobranza') NOT NULL,
    mes INT,
    anio INT,
    asunto VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    total_enviados INT DEFAULT 0,
    total_exitosos INT DEFAULT 0,
    total_fallidos INT DEFAULT 0,
    enviado_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comunidad_id) REFERENCES comunidades(id) ON DELETE CASCADE,
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_comunidad (comunidad_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: envios_correo_detalle
-- ============================================
CREATE TABLE IF NOT EXISTS envios_correo_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    envio_id INT NOT NULL,
    propiedad_id INT NOT NULL,
    email_enviado VARCHAR(100) NOT NULL,
    estado ENUM('enviado', 'error') DEFAULT 'enviado',
    error_msg TEXT,
    FOREIGN KEY (envio_id) REFERENCES envios_correo(id) ON DELETE CASCADE,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    INDEX idx_envio (envio_id),
    INDEX idx_propiedad (propiedad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VISTAS PARA REPORTES
-- ============================================

-- Vista de resumen de pagos por comunidad
CREATE OR REPLACE VIEW vista_resumen_pagos AS
SELECT 
    c.id AS comunidad_id,
    c.nombre AS comunidad_nombre,
    COUNT(DISTINCT p.id) AS total_propiedades,
    SUM(CASE WHEN d.estado = 'Pagado' THEN d.monto ELSE 0 END) AS total_pagado,
    SUM(CASE WHEN d.estado = 'Pendiente' THEN d.monto ELSE 0 END) AS total_deuda,
    COUNT(CASE WHEN d.estado = 'Pagado' THEN 1 END) AS pagos_realizados,
    COUNT(CASE WHEN d.estado = 'Pendiente' THEN 1 END) AS pagos_pendientes
FROM comunidades c
LEFT JOIN propiedades p ON c.id = p.comunidad_id AND p.activo = 1
LEFT JOIN deudas d ON p.id = d.propiedad_id
WHERE c.activo = 1
GROUP BY c.id, c.nombre;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar usuario administrador por defecto
-- Password: admin123 (encriptado con password_hash de PHP)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@condominios.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar algunas comunidades de ejemplo
INSERT INTO comunidades (nombre, direccion, pais, region, comuna, nombre_presidente, whatsapp_presidente, email_presidente) VALUES
('Condominio Los Robles', 'Av. Las Flores 123', 'Chile', 'Metropolitana', 'Las Condes', 'Juan Pérez', '+56912345678', 'juan.perez@email.cl'),
('Edificio Vista Hermosa', 'Calle Principal 456', 'Chile', 'Metropolitana', 'Providencia', 'María González', '+56987654321', 'maria.gonzalez@email.cl');

-- Insertar propiedades de ejemplo
INSERT INTO propiedades (comunidad_id, nombre, tipo, precio_gastos_comunes, nombre_dueno, email_dueno, whatsapp_dueno) VALUES
(1, 'Casa A-101', 'Casa', 85000.00, 'Carlos Rodríguez', 'carlos@email.cl', '+56911223344'),
(1, 'Casa A-102', 'Casa', 85000.00, 'Ana Martínez', 'ana@email.cl', '+56922334455'),
(2, 'Depto 301', 'Departamento', 65000.00, 'Luis Silva', 'luis@email.cl', '+56933445566'),
(2, 'Depto 302', 'Departamento', 65000.00, 'Patricia López', 'patricia@email.cl', '+56944556677');

-- Generar deudas de ejemplo (últimos 6 meses)
SET @mes_actual = MONTH(CURRENT_DATE);
SET @anio_actual = YEAR(CURRENT_DATE);

INSERT INTO deudas (propiedad_id, mes, anio, monto, estado)
SELECT 
    p.id,
    CASE 
        WHEN @mes_actual - n <= 0 THEN 12 + (@mes_actual - n)
        ELSE @mes_actual - n
    END AS mes,
    CASE 
        WHEN @mes_actual - n <= 0 THEN @anio_actual - 1
        ELSE @anio_actual
    END AS anio,
    p.precio_gastos_comunes,
    CASE WHEN RAND() > 0.3 THEN 'Pagado' ELSE 'Pendiente' END
FROM propiedades p
CROSS JOIN (SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) numeros;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================

DELIMITER ;

-- ============================================
-- TABLA: colaboradores
-- ============================================
CREATE TABLE IF NOT EXISTS colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_colaborador ENUM('personal', 'empresa') DEFAULT 'personal',
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    whatsapp VARCHAR(20),
    direccion TEXT,
    region VARCHAR(100),
    comuna VARCHAR(100),
    banco VARCHAR(100),
    tipo_cuenta ENUM('corriente', 'vista') DEFAULT 'vista',
    numero_cuenta VARCHAR(50),
    numero_cliente VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo_colaborador),
    INDEX idx_activo (activo),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: pagos_colaboradores
-- ============================================
CREATE TABLE IF NOT EXISTS pagos_colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    detalle TEXT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha DATE NOT NULL,
    pagado_por INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE RESTRICT,
    FOREIGN KEY (pagado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (fecha),
    INDEX idx_pagado_por (pagado_por)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //

-- Procedimiento para generar deudas mensuales automáticamente
CREATE PROCEDURE IF NOT EXISTS generar_deudas_mes(
    IN p_mes INT,
    IN p_anio INT
)
BEGIN
    INSERT INTO deudas (propiedad_id, mes, anio, monto)
    SELECT p.id, p_mes, p_anio, p.precio_gastos_comunes
    FROM propiedades p
    WHERE p.activo = 1
    AND NOT EXISTS (
        SELECT 1 FROM deudas d 
        WHERE d.propiedad_id = p.id 
        AND d.mes = p_mes 
        AND d.anio = p_anio
    );
END //

-- Procedimiento para obtener resumen de una comunidad
CREATE PROCEDURE IF NOT EXISTS obtener_resumen_comunidad(
    IN p_comunidad_id INT,
    IN p_mes INT,
    IN p_anio INT
)
BEGIN
    SELECT 
        p.id AS propiedad_id,
        p.nombre AS propiedad_nombre,
        p.nombre_dueno,
        d.monto AS monto_deuda,
        d.estado,
        CASE 
            WHEN d.estado = 'Pagado' THEN d.monto
            ELSE 0
        END AS monto_pagado
    FROM propiedades p
    LEFT JOIN deudas d ON p.id = d.propiedad_id 
        AND d.mes = p_mes 
        AND d.anio = p_anio
    WHERE p.comunidad_id = p_comunidad_id
    AND p.activo = 1
    ORDER BY p.nombre;
END //

DELIMITER ;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
