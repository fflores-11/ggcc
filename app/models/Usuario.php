<?php
/**
 * Modelo Usuario
 * Maneja la autenticación y CRUD de usuarios
 */

require_once __DIR__ . '/Model.php';

class Usuario extends Model {
    protected string $table = 'usuarios';

    /**
     * Autentica un usuario
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $email, string $password): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Actualizar último acceso
            $this->update((int)$user['id'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return null;
    }

    /**
     * Crea un usuario con password hasheado
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        // Hashear el password antes de guardar
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return parent::create($data);
    }

    /**
     * Actualiza usuario, hasheando password si es necesario
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        // Hashear el password solo si se está cambiando
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } elseif (isset($data['password']) && empty($data['password'])) {
            // Si el password está vacío, no lo actualizamos
            unset($data['password']);
        }
        return parent::update($id, $data);
    }

    /**
     * Cambia el password de un usuario
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Verifica si un email ya existe (excepto para un usuario específico)
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
     * Obtiene usuarios por rol
     * @param string $rol
     * @return array
     */
    public function getByRol(string $rol): array {
        return $this->findBy('rol', $rol);
    }

    /**
     * Obtiene el último acceso de un usuario
     * @param int $userId
     * @return string|null
     */
    public function getLastAccess(int $userId): ?string {
        $sql = "SELECT ultimo_acceso FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Valida datos de usuario antes de crear/actualizar
     * @param array $data
     * @param int|null $userId (para actualizar)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data, ?int $userId = null): array {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio';
        }

        if (empty($data['email'])) {
            $errors[] = 'El email es obligatorio';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->emailExists($data['email'], $userId)) {
            $errors[] = 'El email ya está registrado';
        }

        // Validar password solo al crear o si se está cambiando
        if ($userId === null && (empty($data['password']) || strlen($data['password']) < 6)) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (empty($data['rol'])) {
            $errors[] = 'El rol es obligatorio';
        } elseif (!in_array($data['rol'], ['admin', 'administrador', 'presidente'])) {
            $errors[] = 'El rol no es válido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
