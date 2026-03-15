<?php
/**
 * Modelo Mascota
 * Gestiona las mascotas de las propiedades
 */

require_once __DIR__ . '/Model.php';

class Mascota extends Model {
    protected string $table = 'mascotas';

    /**
     * Obtiene todas las mascotas de una propiedad
     * @param int $propiedadId
     * @return array
     */
    public function getByPropiedad(int $propiedadId): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE propiedad_id = :propiedad_id AND activo = 1 
                ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una mascota con información de la propiedad
     * @param int $mascotaId
     * @return array|null
     */
    public function getWithPropiedad(int $mascotaId): ?array {
        $sql = "SELECT m.*, p.nombre as propiedad_nombre, p.comunidad_id
                FROM {$this->table} m
                LEFT JOIN propiedades p ON m.propiedad_id = p.id
                WHERE m.id = :id AND m.activo = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $mascotaId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Valida datos de mascota
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data): array {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre de la mascota es obligatorio';
        }

        if (empty($data['tipo'])) {
            $errors[] = 'El tipo de mascota es obligatorio';
        } elseif (!in_array($data['tipo'], ['Gato', 'Perro', 'Ave', 'Hamster'])) {
            $errors[] = 'El tipo de mascota no es válido';
        }

        if (isset($data['edad']) && (!is_numeric($data['edad']) || $data['edad'] < 0)) {
            $errors[] = 'La edad debe ser un número positivo';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Actualiza la imagen de una mascota
     * @param int $mascotaId
     * @param string $imagenPath
     * @return bool
     */
    public function updateImagen(int $mascotaId, string $imagenPath): bool {
        return $this->update($mascotaId, ['imagen_path' => $imagenPath]);
    }

    /**
     * Elimina la imagen física de una mascota
     * @param int $mascotaId
     * @return bool
     */
    public function deleteImagen(int $mascotaId): bool {
        $mascota = $this->find($mascotaId);
        if ($mascota && !empty($mascota['imagen_path'])) {
            $fullPath = ROOT_PATH . '/public/' . $mascota['imagen_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            return $this->update($mascotaId, ['imagen_path' => null]);
        }
        return true;
    }

    /**
     * Obtiene la URL completa de la imagen
     * @param int $mascotaId
     * @return string
     */
    public function getImagenUrl(int $mascotaId): string {
        $mascota = $this->find($mascotaId);
        if ($mascota && !empty($mascota['imagen_path'])) {
            $fullPath = ROOT_PATH . '/public/' . $mascota['imagen_path'];
            if (file_exists($fullPath)) {
                return BASE_URL_FULL . $mascota['imagen_path'];
            }
        }
        return '';
    }

    /**
     * Cuenta mascotas por propiedad
     * @param int $propiedadId
     * @return int
     */
    public function countByPropiedad(int $propiedadId): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE propiedad_id = :propiedad_id AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        return (int) $stmt->fetchColumn();
    }
}
