<?php
/**
 * Modelo Propiedad
 * CRUD de propiedades (casas, departamentos, parcelas)
 */

require_once __DIR__ . '/Model.php';

class Propiedad extends Model {
    protected string $table = 'propiedades';

    /**
     * Valida datos de propiedad
     * @param array $data
     * @param int|null $propiedadId
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data, ?int $propiedadId = null): array {
        $errors = [];

        if (empty($data['comunidad_id'])) {
            $errors[] = 'Debe seleccionar una comunidad';
        }

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre de la propiedad es obligatorio';
        }

        if (empty($data['tipo'])) {
            $errors[] = 'El tipo de propiedad es obligatorio';
        } elseif (!in_array($data['tipo'], ['Casa', 'Departamento', 'Parcela'])) {
            $errors[] = 'El tipo de propiedad no es válido';
        }

        if (!isset($data['precio_gastos_comunes']) || $data['precio_gastos_comunes'] < 0) {
            $errors[] = 'El precio de gastos comunes no es válido';
        }

        if (empty($data['nombre_dueno'])) {
            $errors[] = 'El nombre del dueño es obligatorio';
        }

        if (empty($data['email_dueno'])) {
            $errors[] = 'El email del dueño es obligatorio';
        } elseif (!filter_var($data['email_dueno'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email del dueño no es válido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene propiedades de una comunidad específica
     * @param int $comunidadId
     * @return array
     */
    public function getByComunidad(int $comunidadId): array {
        $sql = "SELECT p.*, c.nombre as comunidad_nombre 
                FROM {$this->table} p 
                LEFT JOIN comunidades c ON p.comunidad_id = c.id 
                WHERE p.comunidad_id = :comunidad_id AND p.activo = 1 
                ORDER BY p.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene propiedades con información de comunidad
     * @return array
     */
    public function getAllWithComunidad(): array {
        $sql = "SELECT p.*, c.nombre as comunidad_nombre, c.direccion as comunidad_direccion
                FROM {$this->table} p 
                LEFT JOIN comunidades c ON p.comunidad_id = c.id 
                WHERE p.activo = 1 
                ORDER BY p.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene propiedades para select/options filtradas por comunidad
     * @param int|null $comunidadId
     * @return array
     */
    public function getForSelect(?int $comunidadId = null): array {
        $sql = "SELECT p.id, p.nombre, p.nombre_dueno, c.nombre as comunidad_nombre 
                FROM {$this->table} p 
                LEFT JOIN comunidades c ON p.comunidad_id = c.id 
                WHERE p.activo = 1";
        
        $params = [];
        if ($comunidadId) {
            $sql .= " AND p.comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $sql .= " ORDER BY p.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca propiedades por nombre o dueño
     * @param string $search
     * @param int|null $comunidadId
     * @return array
     */
    public function search(string $search, ?int $comunidadId = null): array {
        $sql = "SELECT p.*, c.nombre as comunidad_nombre 
                FROM {$this->table} p 
                LEFT JOIN comunidades c ON p.comunidad_id = c.id 
                WHERE p.activo = 1 
                AND (p.nombre LIKE :search OR p.nombre_dueno LIKE :search OR p.email_dueno LIKE :search)";
        
        $params = [':search' => '%' . $search . '%'];
        
        if ($comunidadId) {
            $sql .= " AND p.comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $sql .= " ORDER BY p.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene propiedad con deudas pendientes
     * @param int $propiedadId
     * @return array|null
     */
    public function getWithDeudas(int $propiedadId): ?array {
        $sql = "SELECT p.*, c.nombre as comunidad_nombre, c.direccion as comunidad_direccion
                FROM {$this->table} p 
                LEFT JOIN comunidades c ON p.comunidad_id = c.id 
                WHERE p.id = :id AND p.activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $propiedadId]);
        $propiedad = $stmt->fetch();
        
        if (!$propiedad) {
            return null;
        }

        // Obtener deudas pendientes
        $sqlDeudas = "SELECT * FROM deudas 
                      WHERE propiedad_id = :propiedad_id AND estado = 'Pendiente' 
                      ORDER BY anio DESC, mes DESC";
        $stmtDeudas = $this->db->prepare($sqlDeudas);
        $stmtDeudas->execute([':propiedad_id' => $propiedadId]);
        $propiedad['deudas'] = $stmtDeudas->fetchAll();
        
        // Calcular total de deuda
        $propiedad['total_deuda'] = array_sum(array_column($propiedad['deudas'], 'monto'));
        
        return $propiedad;
    }

    /**
     * Obtiene el historial de pagos de una propiedad
     * @param int $propiedadId
     * @param int $limit
     * @return array
     */
    public function getPaymentHistory(int $propiedadId, int $limit = 10): array {
        $sql = "SELECT p.*, GROUP_CONCAT(d.mes, '-', d.anio ORDER BY d.anio DESC, d.mes DESC SEPARATOR ', ') as meses_pagados
                FROM pagos p
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                WHERE p.propiedad_id = :propiedad_id
                GROUP BY p.id
                ORDER BY p.fecha DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':propiedad_id', $propiedadId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
