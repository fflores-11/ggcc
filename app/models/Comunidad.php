<?php
/**
 * Modelo Comunidad
 * CRUD de comunidades/condominios
 */

require_once __DIR__ . '/Model.php';

class Comunidad extends Model {
    protected string $table = 'comunidades';

    /**
     * Valida datos de comunidad
     * @param array $data
     * @param int|null $comunidadId
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data, ?int $comunidadId = null): array {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre de la comunidad es obligatorio';
        }

        if (empty($data['direccion'])) {
            $errors[] = 'La dirección es obligatoria';
        }

        if (empty($data['region'])) {
            $errors[] = 'La región es obligatoria';
        }

        if (empty($data['comuna'])) {
            $errors[] = 'La comuna es obligatoria';
        }

        if (empty($data['nombre_presidente'])) {
            $errors[] = 'El nombre del presidente es obligatorio';
        }

        if (!empty($data['email_presidente']) && !filter_var($data['email_presidente'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del presidente no es válido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene comunidades con conteo de propiedades
     * @return array
     */
    public function getWithPropertyCount(): array {
        $sql = "SELECT c.*, COUNT(p.id) as total_propiedades 
                FROM {$this->table} c 
                LEFT JOIN propiedades p ON c.id = p.comunidad_id AND p.activo = 1 
                WHERE c.activo = 1 
                GROUP BY c.id 
                ORDER BY c.nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el resumen de deudas de una comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getDebtSummary(int $comunidadId): array {
        $sql = "SELECT 
                    COUNT(DISTINCT p.id) as total_propiedades,
                    SUM(CASE WHEN d.estado = 'Pendiente' THEN d.monto ELSE 0 END) as total_deuda,
                    SUM(CASE WHEN d.estado = 'Pagado' THEN d.monto ELSE 0 END) as total_pagado,
                    COUNT(CASE WHEN d.estado = 'Pendiente' THEN 1 END) as pagos_pendientes,
                    COUNT(CASE WHEN d.estado = 'Pagado' THEN 1 END) as pagos_realizados
                FROM propiedades p
                LEFT JOIN deudas d ON p.id = d.propiedad_id
                WHERE p.comunidad_id = :comunidad_id AND p.activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Busca comunidades por nombre
     * @param string $search
     * @return array
     */
    public function search(string $search): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE activo = 1 
                AND (nombre LIKE :search OR direccion LIKE :search OR comuna LIKE :search)
                ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las comunidades para select/options
     * @return array
     */
    public function getForSelect(): array {
        return $this->getActive('nombre', 'ASC');
    }

    /**
     * Obtiene comunidades con conteo de propiedades (paginado)
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getWithPropertyCountPaginated(int $offset, int $limit): array {
        $sql = "SELECT c.*, COUNT(p.id) as total_propiedades 
                FROM {$this->table} c 
                LEFT JOIN propiedades p ON c.id = p.comunidad_id AND p.activo = 1 
                WHERE c.activo = 1 
                GROUP BY c.id 
                ORDER BY c.nombre ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de comunidades activas
     * @return int
     */
    public function countActive(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE activo = 1";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}
