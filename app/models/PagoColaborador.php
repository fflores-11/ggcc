<?php
/**
 * Modelo PagoColaborador
 * Gestiona los pagos realizados a colaboradores
 */

require_once __DIR__ . '/Model.php';

class PagoColaborador extends Model {
    protected string $table = 'pagos_colaboradores';

    // Constant for max images per payment
    const MAX_IMAGENES = 4;

    /**
     * Obtiene todos los pagos con información del colaborador y usuario
     * @param int|null $limit
     * @return array
     */
    public function getAllWithDetails(?int $limit = null): array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       c.whatsapp as colaborador_whatsapp,
                       u.nombre as pagado_por_nombre,
                       (SELECT COUNT(*) FROM pagos_colaborador_imagenes pci WHERE pci.pago_colaborador_id = pc.id) as total_imagenes
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                ORDER BY pc.fecha DESC, pc.id DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los pagos con información del colaborador y usuario (paginado)
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllWithDetailsPaginated(int $offset, int $limit): array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       c.whatsapp as colaborador_whatsapp,
                       u.nombre as pagado_por_nombre,
                       (SELECT COUNT(*) FROM pagos_colaborador_imagenes pci WHERE pci.pago_colaborador_id = pc.id) as total_imagenes
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                ORDER BY pc.fecha DESC, pc.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por colaborador
     * @param int $colaboradorId
     * @return array
     */
    public function getByColaborador(int $colaboradorId): array {
        $sql = "SELECT pc.*, 
                       u.nombre as pagado_por_nombre,
                       (SELECT COUNT(*) FROM pagos_colaborador_imagenes pci WHERE pci.pago_colaborador_id = pc.id) as total_imagenes
                FROM {$this->table} pc
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.colaborador_id = :colaborador_id
                ORDER BY pc.fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por colaborador con paginación
     * @param int $colaboradorId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByColaboradorPaginated(int $colaboradorId, int $offset, int $limit): array {
        $sql = "SELECT pc.*, 
                       u.nombre as pagado_por_nombre,
                       (SELECT COUNT(*) FROM pagos_colaborador_imagenes pci WHERE pci.pago_colaborador_id = pc.id) as total_imagenes
                FROM {$this->table} pc
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.colaborador_id = :colaborador_id
                ORDER BY pc.fecha DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':colaborador_id', $colaboradorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene resumen de pagos por período
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function getResumenPeriodo(string $fechaInicio, string $fechaFin): array {
        $sql = "SELECT 
                            DATE_FORMAT(pc.fecha, '%Y-%m') as mes,
                            COUNT(*) as cantidad_pagos,
                            SUM(pc.monto) as total_pagado
                FROM {$this->table} pc
                WHERE pc.fecha BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE_FORMAT(pc.fecha, '%Y-%m')
                ORDER BY mes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el total pagado a un colaborador
     * @param int $colaboradorId
     * @return float
     */
    public function getTotalPagado(int $colaboradorId): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM {$this->table} WHERE colaborador_id = :colaborador_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Calcula el total pagado en el mes actual
     * @return float
     */
    public function getTotalMesActual(): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) FROM {$this->table} 
                WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Valida datos del pago
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate(array $data): array {
        $errors = [];

        if (empty($data['colaborador_id'])) {
            $errors[] = 'Debe seleccionar un colaborador';
        }

        if (empty($data['detalle'])) {
            $errors[] = 'El detalle del pago es obligatorio';
        }

        if (!isset($data['monto']) || $data['monto'] <= 0) {
            $errors[] = 'El monto debe ser mayor a 0';
        }

        if (empty($data['fecha'])) {
            $errors[] = 'La fecha es obligatoria';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene un pago con su imagen
     * @param int $pagoId
     * @return array|null
     */
    public function getWithImagen(int $pagoId): ?array {
        $sql = "SELECT pc.*, 
                       c.nombre as colaborador_nombre,
                       c.email as colaborador_email,
                       u.nombre as pagado_por_nombre
                FROM {$this->table} pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE pc.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pagoId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Actualiza la ruta de la imagen de un pago
     * @param int $pagoId
     * @param string $imagenPath
     * @return bool
     */
    public function updateImagenPath(int $pagoId, string $imagenPath): bool {
        $sql = "UPDATE {$this->table} SET imagen_path = :imagen_path WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':imagen_path' => $imagenPath, ':id' => $pagoId]);
    }

    /**
     * Obtiene la ruta de la imagen de un pago
     * @param int $pagoId
     * @return string|null
     */
    public function getImagenPath(int $pagoId): ?string {
        $sql = "SELECT imagen_path FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pagoId]);
        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    /**
     * Elimina la imagen de un pago (solo de la BD, no el archivo físico)
     * @param int $pagoId
     * @return bool
     */
    public function deleteImagen(int $pagoId): bool {
        return $this->updateImagenPath($pagoId, '');
    }

    /**
     * Cuenta pagos por colaborador
     * @param int $colaboradorId
     * @return int
     */
    public function countByColaborador(int $colaboradorId): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE colaborador_id = :colaborador_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':colaborador_id' => $colaboradorId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cuenta total de pagos a colaboradores
     * @return int
     */
    public function countPagos(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene todas las imágenes de un pago
     * @param int $pagoId
     * @return array
     */
    public function getImagenes(int $pagoId): array {
        $sql = "SELECT * FROM pagos_colaborador_imagenes WHERE pago_colaborador_id = :pago_id ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pago_id' => $pagoId]);
        return $stmt->fetchAll();
    }

    /**
     * Sube una o más imágenes para un pago
     * @param int $pagoId
     * @param array $archivos Array de archivos subidos desde $_FILES
     * @return array ['success' => int, 'total' => int, 'errors' => array]
     */
    public function subirImagenes(int $pagoId, array $archivos): array {
        $errors = [];
        $successCount = 0;

        foreach ($archivos['tmp_name'] as $key => $tmpName) {
            if (empty($tmpName)) {
                continue;
            }

            // Validar solo la extensión (imagen o PDF)
            $ext = strtolower(pathinfo($archivos['name'][$key], PATHINFO_EXTENSION));
            $extensionesValidas = ['jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff', 'pdf'];
            if (!in_array($ext, $extensionesValidas)) {
                $errors[] = "Formato no permitido: {$archivos['name'][$key]} (solo imágenes o PDF)";
                continue;
            }

            $nombreArchivo = 'pago_' . time() . '_' . uniqid() . '.' . $ext;
            $directorioRelativo = 'assets/images/pagos_colaboradores/';
            $directorio = PUBLIC_PATH . '/' . $directorioRelativo;
            @mkdir($directorio, 0755, true);
            $rutaDestino = $directorio . $nombreArchivo;

            if (move_uploaded_file($tmpName, $rutaDestino)) {
                @chmod($rutaDestino, 0644);
                $sql = "INSERT INTO pagos_colaborador_imagenes (pago_colaborador_id, ruta_imagen) VALUES (:pago_id, :ruta)";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute([':pago_id' => $pagoId, ':ruta' => $directorioRelativo . $nombreArchivo])) {
                    $successCount++;
                } else {
                    $errors[] = "Error al guardar el registro de la imagen: {$archivos['name'][$key]}";
                    unlink($rutaDestino);
                }
            } else {
                $errors[] = "Error al subir el archivo: {$archivos['name'][$key]}";
            }
        }

        return [
            'success' => $successCount,
            'total' => count($archivos['name']),
            'errors' => $errors
        ];
    }

    /**
     * Elimina una imagen específica
     * @param int $imagenId
     * @return bool
     */
    public function eliminarImagen(int $imagenId): bool {
        $sql = "SELECT ruta_imagen FROM pagos_colaborador_imagenes WHERE id = :imagen_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':imagen_id' => $imagenId]);
        $imagen = $stmt->fetch();

        if (!$imagen) {
            return false;
        }

        $rutaArchivo = $imagen['ruta_imagen'];
        $rutaAbsoluta = PUBLIC_PATH . '/' . ltrim($rutaArchivo, '/');
        if (file_exists($rutaAbsoluta)) {
            unlink($rutaAbsoluta);
        }

        $sql = "DELETE FROM pagos_colaborador_imagenes WHERE id = :imagen_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':imagen_id' => $imagenId]);
    }

    /**
     * Elimina todas las imágenes de un pago (registros en BD)
     * @param int $pagoId
     * @return int Número de registros eliminados
     */
    public function eliminarTodasImagenes(int $pagoId): int {
        $sql = "DELETE FROM pagos_colaborador_imagenes WHERE pago_colaborador_id = :pago_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pago_id' => $pagoId]);
        return $stmt->rowCount();
    }

    /**
     * Sube varias imágenes individuales a un pago
     * @param int $pagoId
     * @param array $files Array de archivos de $_FILES
     * @return array ['success' => int, 'total' => int, 'errors' => array]
     */
    public function subirImagenesIndividuales(int $pagoId, array $files): array {
        $errors = [];
        $successCount = 0;
        $totalCount = count($files);

        foreach ($files as $file) {
            // Verificar errores de subida
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
                ];
                $errorMsg = $errorMessages[$file['error']] ?? 'Error desconocido al subir el archivo';
                $errors[] = "Error en {$file['name']}: $errorMsg";
                continue;
            }

            // Validar solo la extensión (imagen o PDF)
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extensionesValidas = ['jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff', 'pdf'];
            if (!in_array($ext, $extensionesValidas)) {
                $errors[] = "Formato no permitido: {$file['name']} (solo imágenes o PDF)";
                continue;
            }

            // Generar nombre único
            $nombreArchivo = 'pago_' . time() . '_' . uniqid() . '.' . $ext;
            $directorioRelativo = 'assets/images/pagos_colaboradores/';
            $directorio = PUBLIC_PATH . '/' . $directorioRelativo;
            @mkdir($directorio, 0755, true);
            $rutaDestino = $directorio . $nombreArchivo;

            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                @chmod($rutaDestino, 0644);
                // Guardar ruta relativa en base de datos
                $sql = "INSERT INTO pagos_colaborador_imagenes (pago_colaborador_id, ruta_imagen) VALUES (:pago_id, :ruta)";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute([':pago_id' => $pagoId, ':ruta' => $directorioRelativo . $nombreArchivo])) {
                    $successCount++;
                } else {
                    $errors[] = "Error al guardar el registro de la imagen: {$file['name']}";
                    unlink($rutaDestino);
                }
            } else {
                $errors[] = "Error al subir el archivo: {$file['name']}";
            }
        }

        return [
            'success' => $successCount,
            'total' => $totalCount,
            'errors' => $errors
        ];
    }
}
