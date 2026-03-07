<?php
/**
 * Modelo Colaborador
 * CRUD de colaboradores/personal
 */

require_once __DIR__ . '/Model.php';

class Colaborador extends Model {
    protected string $table = 'colaboradores';

    /**
     * Valida datos de colaborador
     * @param array $data
     * @param int|null $colaboradorId
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data, ?int $colaboradorId = null): array {
        $errors = [];
        $tipo = $data['tipo_colaborador'] ?? 'personal';

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio';
        }

        // Validaciones específicas según el tipo
        if ($tipo === 'personal') {
            // Validaciones para Personal
            if (empty($data['email'])) {
                $errors[] = 'El email es obligatorio';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El email no es válido';
            } elseif ($this->emailExists($data['email'], $colaboradorId)) {
                $errors[] = 'El email ya está registrado';
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
        } else {
            // Validaciones para Empresa
            if (empty($data['numero_cliente'])) {
                $errors[] = 'El número de cliente es obligatorio';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verifica si un email ya existe
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Obtiene colaboradores con conteo de pagos realizados
     * @return array
     */
    public function getAllWithPagosCount(): array {
        $sql = "SELECT c.*, 
                       COUNT(pc.id) as total_pagos,
                       COALESCE(SUM(pc.monto), 0) as total_pagado
                FROM {$this->table} c
                LEFT JOIN pagos_colaboradores pc ON c.id = pc.colaborador_id
                WHERE c.activo = 1
                GROUP BY c.id
                ORDER BY c.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene colaboradores para select/options
     * @return array
     */
    public function getForSelect(): array {
        return $this->getActive('nombre', 'ASC');
    }
}
