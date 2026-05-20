<?php
/**
 * Modelo Base
 * Clase abstracta que define métodos comunes para todos los modelos
 */

abstract class Model {
    protected string $table;
    protected string $primaryKey = 'id';
    protected ?PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Obtiene todos los registros
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function all(string $orderBy = 'id', string $order = 'ASC'): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene registros activos
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function getActive(string $orderBy = 'id', string $order = 'ASC'): array {
        $sql = "SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca un registro por ID
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca un registro activo por ID
     * @param int $id
     * @return array|null
     */
    public function findActive(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Crea un nuevo registro
     * @param array $data
     * @return int ID del registro creado
     */
    public function create(array $data): int {
        $columns = array_keys($data);
        $values = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $values) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un registro
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . 
               " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Elimina un registro (soft delete - desactiva)
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Elimina un registro permanentemente (hard delete)
     * @param int $id
     * @return bool
     */
    public function hardDelete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Reactiva un registro desactivado
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool {
        return $this->update($id, ['activo' => 1]);
    }

    /**
     * Cuenta el total de registros
     * @param bool $onlyActive
     * @return int
     */
    public function count(bool $onlyActive = true): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($onlyActive) {
            $sql .= " WHERE activo = 1";
        }
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca registros por campo
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findBy(string $field, $value): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Busca un solo registro por campo
     * @param string $field
     * @param mixed $value
     * @return array|null
     */
    public function findOneBy(string $field, $value): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecuta una consulta SQL personalizada
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta SQL personalizada (único resultado)
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
