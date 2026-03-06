<?php
/**
 * Configuración de Base de Datos
 * Sistema de Administración de Gastos Comunes de Condominios
 * PHP 8.1 + PDO + MySQL/MariaDB
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'condominios_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase Database - Singleton para conexión PDO
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Obtiene la instancia de conexión PDO
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Cierra la conexión
     */
    public static function close(): void {
        self::$instance = null;
    }
}

/**
 * Función auxiliar para obtener conexión
 * @return PDO
 */
function getDB(): PDO {
    return Database::getConnection();
}
