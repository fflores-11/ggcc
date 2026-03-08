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
                ORDER BY LENGTH(p.nombre), p.nombre";
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
                ORDER BY LENGTH(p.nombre), p.nombre";
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
        
        $sql .= " ORDER BY LENGTH(p.nombre), p.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el saldo actual de una propiedad
     * @param int $propiedadId
     * @return array|null
     */
    public function getSaldo(int $propiedadId): float {
        $sql = "SELECT COALESCE(saldo, 0) FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $propiedadId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Incrementa el saldo de una propiedad (cuando hay sobrepago)
     * @param int $propiedadId
     * @param float $monto
     * @param string $descripcion
     * @param string|null $referenciaTipo
     * @param int|null $referenciaId
     * @return bool
     */
    public function incrementarSaldo(int $propiedadId, float $monto, string $descripcion = '', ?string $referenciaTipo = null, ?int $referenciaId = null): bool {
        try {
            $this->db->beginTransaction();
            
            // Obtener saldo actual
            $saldoAnterior = $this->getSaldo($propiedadId);
            $saldoNuevo = $saldoAnterior + $monto;
            
            // Actualizar saldo
            $sql = "UPDATE {$this->table} SET saldo = :saldo WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':saldo' => $saldoNuevo, ':id' => $propiedadId]);
            
            // Registrar en historial
            $sqlHist = "INSERT INTO propiedades_saldo_historial 
                        (propiedad_id, tipo_movimiento, monto, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion) 
                        VALUES (:propiedad_id, 'ingreso', :monto, :saldo_anterior, :saldo_nuevo, :referencia_tipo, :referencia_id, :descripcion)";
            $stmtHist = $this->db->prepare($sqlHist);
            $stmtHist->execute([
                ':propiedad_id' => $propiedadId,
                ':monto' => $monto,
                ':saldo_anterior' => $saldoAnterior,
                ':saldo_nuevo' => $saldoNuevo,
                ':referencia_tipo' => $referenciaTipo,
                ':referencia_id' => $referenciaId,
                ':descripcion' => $descripcion ?: 'Incremento de saldo'
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error incrementando saldo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Aplica saldo a deuda (decrementa saldo)
     * @param int $propiedadId
     * @param float $monto
     * @param int $deudaId
     * @param string $descripcion
     * @return bool
     */
    public function aplicarSaldoADeuda(int $propiedadId, float $monto, int $deudaId, string $descripcion = ''): bool {
        try {
            $this->db->beginTransaction();
            
            // Obtener saldo actual
            $saldoAnterior = $this->getSaldo($propiedadId);
            
            if ($saldoAnterior < $monto) {
                throw new Exception("Saldo insuficiente");
            }
            
            $saldoNuevo = $saldoAnterior - $monto;
            
            // Actualizar saldo
            $sql = "UPDATE {$this->table} SET saldo = :saldo WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':saldo' => $saldoNuevo, ':id' => $propiedadId]);
            
            // Registrar en historial
            $sqlHist = "INSERT INTO propiedades_saldo_historial 
                        (propiedad_id, tipo_movimiento, monto, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion) 
                        VALUES (:propiedad_id, 'aplicacion_deuda', :monto, :saldo_anterior, :saldo_nuevo, 'deuda', :deuda_id, :descripcion)";
            $stmtHist = $this->db->prepare($sqlHist);
            $stmtHist->execute([
                ':propiedad_id' => $propiedadId,
                ':monto' => $monto,
                ':saldo_anterior' => $saldoAnterior,
                ':saldo_nuevo' => $saldoNuevo,
                ':deuda_id' => $deudaId,
                ':descripcion' => $descripcion ?: 'Aplicación de saldo a deuda'
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error aplicando saldo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene historial de movimientos de saldo
     * @param int $propiedadId
     * @param int $limit
     * @return array
     */
    public function getSaldoHistorial(int $propiedadId, int $limit = 20): array {
        $sql = "SELECT * FROM propiedades_saldo_historial 
                WHERE propiedad_id = :propiedad_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':propiedad_id', $propiedadId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
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
        
        $sql .= " ORDER BY LENGTH(p.nombre), p.nombre";
        
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
