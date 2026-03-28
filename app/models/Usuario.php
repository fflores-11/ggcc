<?php
/**
 * Modelo Usuario
 * Maneja la autenticación y CRUD de usuarios
 */

require_once __DIR__ . '/Model.php';

class Usuario extends Model {
    protected string $table = 'usuarios';

    /**
     * Autentica un usuario por email o nombre de usuario
     * @param string $emailOUsuario
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $emailOUsuario, string $password): ?array {
        // Intentar buscar por email primero
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $emailOUsuario]);
        $user = $stmt->fetch();

        // Si no se encontró por email, buscar por nombre de usuario
        if (!$user) {
            $sql = "SELECT * FROM {$this->table} WHERE nombre = :nombre AND activo = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':nombre' => $emailOUsuario]);
            $user = $stmt->fetch();
        }

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
        } elseif (!in_array($data['rol'], ['admin', 'administrador', 'presidente', 'propietario'])) {
            $errors[] = 'El rol no es válido';
        }

        // Validar comunidad_id para administradores y propietarios
        if (in_array($data['rol'], ['administrador', 'propietario'])) {
            if (empty($data['comunidad_id'])) {
                $errors[] = 'Debe seleccionar una comunidad para el usuario';
            } elseif (!is_numeric($data['comunidad_id'])) {
                $errors[] = 'La comunidad seleccionada no es válida';
            }
        }

        // Validar propiedad_id para propietarios
        if ($data['rol'] === 'propietario') {
            if (empty($data['propiedad_id'])) {
                $errors[] = 'Debe seleccionar una propiedad para el usuario propietario';
            } elseif (!is_numeric($data['propiedad_id'])) {
                $errors[] = 'La propiedad seleccionada no es válida';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene usuarios por comunidad
     * @param int $comunidadId
     * @return array
     */
    public function getByComunidad(int $comunidadId): array {
        $sql = "SELECT u.*, c.nombre as comunidad_nombre 
                FROM {$this->table} u 
                LEFT JOIN comunidades c ON u.comunidad_id = c.id 
                WHERE u.comunidad_id = :comunidad_id AND u.activo = 1
                ORDER BY u.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':comunidad_id' => $comunidadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la comunidad asignada a un usuario
     * @param int $userId
     * @return array|null
     */
    public function getComunidad(int $userId): ?array {
        $sql = "SELECT u.comunidad_id, c.nombre as comunidad_nombre, c.*
                FROM {$this->table} u 
                LEFT JOIN comunidades c ON u.comunidad_id = c.id 
                WHERE u.id = :id AND u.activo = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifica si un usuario tiene acceso a una comunidad específica
     * @param int $userId
     * @param int $comunidadId
     * @return bool
     */
    public function hasAccessToComunidad(int $userId, int $comunidadId): bool {
        $sql = "SELECT rol, comunidad_id FROM {$this->table} WHERE id = :id AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // Super admin tiene acceso a todo
        if ($user['rol'] === 'admin') {
            return true;
        }

        // Administrador y presidente solo acceden a su comunidad asignada
        return (int)$user['comunidad_id'] === $comunidadId;
    }

    /**
     * Obtiene todos los usuarios con información de comunidad
     * @return array
     */
    public function getAllWithComunidad(): array {
        $sql = "SELECT u.*, c.nombre as comunidad_nombre 
                FROM {$this->table} u 
                LEFT JOIN comunidades c ON u.comunidad_id = c.id 
                ORDER BY u.nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la ruta de la firma del usuario
     * @param int $userId
     * @return string|null
     */
    public function getFirmaPath(int $userId): ?string {
        $sql = "SELECT firma_path FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetchColumn();
        
        // Asegurar que retornamos null si no hay valor
        if ($result === false || $result === null || $result === '') {
            return null;
        }
        
        return (string) $result;
    }

    /**
     * Actualiza la ruta de la firma del usuario
     * @param int $userId
     * @param string $path
     * @return bool
     */
    public function updateFirmaPath(int $userId, string $path): bool {
        $sql = "UPDATE {$this->table} SET firma_path = :path WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':path' => $path, ':id' => $userId]);
    }

    /**
     * Verifica si existe la imagen de firma del usuario
     * @param int $userId
     * @return bool
     */
    public function firmaExists(int $userId): bool {
        $path = $this->getFirmaPath($userId);
        if (!$path) return false;
        return file_exists(ROOT_PATH . '/public/' . $path);
    }

    /**
     * Obtiene la URL completa de la firma del usuario
     * @param int $userId
     * @return string
     */
    public function getFirmaUrl(int $userId): string {
        $path = $this->getFirmaPath($userId);
        if ($this->firmaExists($userId)) {
            return BASE_URL_FULL . $path;
        }
        return '';
    }

    /**
     * Busca usuario por email
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Genera token de recuperación de contraseña
     * @param int $userId
     * @return string|false
     */
    public function generateResetToken(int $userId): string|false {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $sql = "UPDATE {$this->table} SET reset_token = :token, reset_expires = :expires WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $userId
        ]);
        
        return $success ? $token : false;
    }

    /**
     * Busca usuario por token de recuperación
     * @param string $token
     * @return array|null
     */
    public function findByResetToken(string $token): ?array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reset_token = :token 
                AND reset_expires > NOW() 
                AND activo = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Restablece la contraseña y limpia el token
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $sql = "UPDATE {$this->table} 
                SET password = :password, reset_token = NULL, reset_expires = NULL 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);
    }

    /**
     * Limpia tokens expirados (puede ejecutarse periódicamente)
     * @return int
     */
    public function clearExpiredTokens(): int {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_expires = NULL WHERE reset_expires < NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Genera una contraseña aleatoria
     * @param int $length Longitud de la contraseña (default 10)
     * @return string
     */
    public function generarPassword(int $length = 10): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        
        return $password;
    }

    /**
     * Obtiene todos los usuarios propietarios con información de propiedad
     * @param int|null $comunidadId Filtrar por comunidad (opcional)
     * @return array
     */
    public function getUsuariosPropietarios(?int $comunidadId = null, bool $soloActivos = true): array {
        $sql = "SELECT u.*, p.nombre as propiedad_nombre, p.nombre_dueno, 
                       p.email_dueno, p.whatsapp_dueno, c.nombre as comunidad_nombre
                FROM {$this->table} u
                LEFT JOIN propiedades p ON u.propiedad_id = p.id
                LEFT JOIN comunidades c ON u.comunidad_id = c.id
                WHERE u.es_propietario = 1";
        
        $params = [];
        
        if ($soloActivos) {
            $sql .= " AND u.activo = 1";
        }
        
        if ($comunidadId !== null) {
            $sql .= " AND u.comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $sql .= " ORDER BY c.nombre, p.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un usuario propietario con información completa de propiedad
     * @param int $userId
     * @return array|null
     */
    public function getUsuarioPropietario(int $userId): ?array {
        $sql = "SELECT u.*, p.nombre as propiedad_nombre, p.nombre_dueno, 
                       p.email_dueno, p.whatsapp_dueno, p.tipo as propiedad_tipo,
                       p.precio_gastos_comunes, p.nombre_agente, p.email_agente, 
                       p.whatsapp_agente, c.nombre as comunidad_nombre,
                       c.direccion as comunidad_direccion, c.nombre_presidente,
                       c.whatsapp_presidente, c.email_presidente
                FROM {$this->table} u
                LEFT JOIN propiedades p ON u.propiedad_id = p.id
                LEFT JOIN comunidades c ON u.comunidad_id = c.id
                WHERE u.id = :id AND u.es_propietario = 1
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifica si una propiedad ya tiene usuario asignado
     * @param int $propiedadId
     * @param int|null $excludeUserId Excluir un usuario específico (para edición)
     * @return bool
     */
    public function propiedadHasUsuario(int $propiedadId, ?int $excludeUserId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE propiedad_id = :propiedad_id AND es_propietario = 1 AND activo = 1";
        $params = [':propiedad_id' => $propiedadId];

        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crea un usuario propietario
     * @param array $data
     * @return int|false
     */
    public function createPropietario(array $data): int|false {
        // Verificar que la propiedad no tenga ya un usuario
        if ($this->propiedadHasUsuario((int)$data['propiedad_id'])) {
            return false;
        }

        // Preparar datos específicos para propietario
        $data['rol'] = 'propietario';
        $data['es_propietario'] = 1;
        
        // El nombre será el nombre de la propiedad
        if (!isset($data['nombre'])) {
            // Obtener nombre de propiedad
            $sql = "SELECT nombre FROM propiedades WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $data['propiedad_id']]);
            $data['nombre'] = $stmt->fetchColumn() ?: 'Propietario';
        }

        return $this->create($data);
    }

    /**
     * Actualiza datos de perfil para propietario (solo campos editables)
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updatePerfilPropietario(int $userId, array $data): bool {
        // Solo permitir actualizar email y whatsapp
        $allowedFields = ['email', 'whatsapp'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // Si hay password, agregarlo
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }
        
        if (empty($updateData)) {
            return true; // No hay nada que actualizar
        }
        
        return $this->update($userId, $updateData);
    }

    /**
     * Verifica si un usuario tiene acceso a una propiedad específica
     * @param int $userId
     * @param int $propiedadId
     * @return bool
     */
    public function hasAccessToPropiedad(int $userId, int $propiedadId): bool {
        $sql = "SELECT rol, propiedad_id, comunidad_id FROM {$this->table} WHERE id = :id AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // Super admin tiene acceso a todo
        if ($user['rol'] === 'admin') {
            return true;
        }

        // Propietario solo accede a su propiedad
        if ($user['rol'] === 'propietario') {
            return (int)$user['propiedad_id'] === $propiedadId;
        }

        // Administrador y presidente acceden si la propiedad pertenece a su comunidad
        if (in_array($user['rol'], ['administrador', 'presidente'])) {
            $sql = "SELECT COUNT(*) FROM propiedades WHERE id = :propiedad_id AND comunidad_id = :comunidad_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':propiedad_id' => $propiedadId,
                ':comunidad_id' => $user['comunidad_id']
            ]);
            return (bool) $stmt->fetchColumn();
        }

        return false;
    }

    /**
     * Obtiene usuarios por comunidad (paginado)
     * @param int $comunidadId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByComunidadPaginated(int $comunidadId, int $offset, int $limit): array {
        $sql = "SELECT u.*, c.nombre as comunidad_nombre 
                FROM {$this->table} u 
                LEFT JOIN comunidades c ON u.comunidad_id = c.id 
                WHERE u.comunidad_id = :comunidad_id AND u.activo = 1
                ORDER BY u.nombre
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comunidad_id', $comunidadId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los usuarios con información de comunidad (paginado)
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllWithComunidadPaginated(int $offset, int $limit): array {
        $sql = "SELECT u.*, c.nombre as comunidad_nombre 
                FROM {$this->table} u 
                LEFT JOIN comunidades c ON u.comunidad_id = c.id 
                ORDER BY u.nombre
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta usuarios por comunidad
     * @param int|null $comunidadId
     * @return int
     */
    public function countByComunidad(?int $comunidadId = null): int {
        if ($comunidadId) {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE comunidad_id = :comunidad_id AND activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':comunidad_id' => $comunidadId]);
        } else {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->db->query($sql);
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene usuarios propietarios con información de propiedad (paginado)
     * @param int|null $comunidadId Filtrar por comunidad (opcional)
     * @param int $offset
     * @param int $limit
     * @param bool $soloActivos
     * @return array
     */
    public function getUsuariosPropietariosPaginated(?int $comunidadId = null, int $offset, int $limit, bool $soloActivos = true): array {
        $sql = "SELECT u.*, p.nombre as propiedad_nombre, p.nombre_dueno, 
                       p.email_dueno, p.whatsapp_dueno, c.nombre as comunidad_nombre
                FROM {$this->table} u
                LEFT JOIN propiedades p ON u.propiedad_id = p.id
                LEFT JOIN comunidades c ON u.comunidad_id = c.id
                WHERE u.es_propietario = 1";
        
        $params = [];
        
        if ($soloActivos) {
            $sql .= " AND u.activo = 1";
        }
        
        if ($comunidadId !== null) {
            $sql .= " AND u.comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $sql .= " ORDER BY c.nombre, p.nombre LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta usuarios propietarios
     * @param int|null $comunidadId
     * @param bool $soloActivos
     * @return int
     */
    public function countUsuariosPropietarios(?int $comunidadId = null, bool $soloActivos = true): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE es_propietario = 1";
        $params = [];
        
        if ($soloActivos) {
            $sql .= " AND activo = 1";
        }
        
        if ($comunidadId !== null) {
            $sql .= " AND comunidad_id = :comunidad_id";
            $params[':comunidad_id'] = $comunidadId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
