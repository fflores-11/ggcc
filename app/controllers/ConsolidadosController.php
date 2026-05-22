<?php
/**
 * Controlador de Consolidados
 * Muestra matriz de pagos por comunidad
 */

class ConsolidadosController {
    private Comunidad $comunidadModel;
    private Propiedad $propiedadModel;
    private Deuda $deudaModel;
    private Pago $pagoModel;
    private PDO $db;

    public function __construct() {
        $this->comunidadModel = new Comunidad();
        $this->propiedadModel = new Propiedad();
        $this->deudaModel = new Deuda();
        $this->pagoModel = new Pago();
        $this->db = getDB();
    }

    /**
     * Muestra el consolidado de pagos
     */
    public function index(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        $comunidades = $this->comunidadModel->getForSelect();
        $matriz = [];
        $comunidad = null;
        $meses = [];
        $totales = [];
        
        // Si es propietario, forzar su comunidad y propiedad
        if (getUserRole() === 'propietario') {
            $userModel = new Usuario();
            $usuario = $userModel->getUsuarioPropietario(getUserId());
            if ($usuario) {
                $comunidadId = $usuario['comunidad_id'];
                $propiedadId = $usuario['propiedad_id'];
                $comunidad = $this->comunidadModel->find($comunidadId);
                $resultado = $this->generarMatrizPropiedad($propiedadId, $anio);
                $matriz = $resultado['filas'];
                $totales = $resultado['totales'];
                $meses = $this->getMesesDisponibles($comunidadId, $anio);
            }
        } elseif ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $resultado = $this->generarMatriz($comunidadId, $anio);
            $matriz = $resultado['filas'];
            $totales = $resultado['totales'];
            $meses = $this->getMesesDisponibles($comunidadId, $anio);
        }
        
        $title = 'Consolidado de Pagos';
        require_once VIEWS_PATH . '/consolidados/index.php';
    }

    /**
     * Genera la matriz de pagos para una propiedad específica
     * @param int $propiedadId
     * @param int $anio
     * @return array
     */
    private function generarMatrizPropiedad(int $propiedadId, int $anio): array {
        // Obtener la propiedad
        $propiedad = $this->propiedadModel->find($propiedadId);
        
        $matriz = [];
        $totalesPorMes = [];
        $granTotalPagado = 0;
        $granTotalPendiente = 0;
        
        $fila = [
            'propiedad' => $propiedad,
            'meses' => [],
            'total_pagado' => 0,
            'total_pendiente' => 0
        ];
        
        // Obtener deudas de la propiedad para el año
        $sql = "SELECT d.* FROM deudas d 
                WHERE d.propiedad_id = :propiedad_id 
                AND d.anio = :anio
                ORDER BY d.mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':propiedad_id' => $propiedadId,
            ':anio' => $anio
        ]);
        $deudas = $stmt->fetchAll();
        
        foreach ($deudas as $deuda) {
            $mes = (int) $deuda['mes'];
            $estado = $deuda['estado'];
            $monto = (float) $deuda['monto'];
            
            $fila['meses'][$mes] = [
                'estado' => $estado,
                'monto' => $monto,
                'deuda_id' => $deuda['id']
            ];
            
            if ($estado === 'Pagado') {
                $fila['total_pagado'] += $monto;
            } else {
                $fila['total_pendiente'] += $monto;
            }
            
            // Acumular totales por mes
            if (!isset($totalesPorMes[$mes])) {
                $totalesPorMes[$mes] = ['pagado' => 0, 'pendiente' => 0];
            }
            
            if ($estado === 'Pagado') {
                $totalesPorMes[$mes]['pagado'] += $monto;
            } else {
                $totalesPorMes[$mes]['pendiente'] += $monto;
            }
        }
        
        $granTotalPagado += $fila['total_pagado'];
        $granTotalPendiente += $fila['total_pendiente'];
        
        $matriz[] = $fila;
        
        return [
            'filas' => $matriz,
            'totales' => [
                'totales' => $totalesPorMes,
                'gran_total_pagado' => $granTotalPagado,
                'gran_total_pendiente' => $granTotalPendiente
            ]
        ];
    }

    /**
     * Genera la matriz de pagos para una comunidad
     * @param int $comunidadId
     * @param int $anio
     * @return array
     */
    private function generarMatriz(int $comunidadId, int $anio): array {
        // Obtener propiedades de la comunidad
        $propiedades = $this->propiedadModel->getByComunidad($comunidadId);
        
        $matriz = [];
        $totalesPorMes = [];
        $granTotalPagado = 0;
        $granTotalPendiente = 0;
        
        foreach ($propiedades as $propiedad) {
            $fila = [
                'propiedad' => $propiedad,
                'meses' => [],
                'total_pagado' => 0,
                'total_pendiente' => 0
            ];
            
            // Obtener deudas de la propiedad para el año
            $sql = "SELECT d.* FROM deudas d 
                    WHERE d.propiedad_id = :propiedad_id 
                    AND d.anio = :anio
                    ORDER BY d.mes";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':propiedad_id' => $propiedad['id'],
                ':anio' => $anio
            ]);
            $deudas = $stmt->fetchAll();
            
            foreach ($deudas as $deuda) {
                $mes = (int) $deuda['mes'];
                $estado = $deuda['estado'];
                $monto = (float) $deuda['monto'];
                
                $fila['meses'][$mes] = [
                    'estado' => $estado,
                    'monto' => $monto,
                    'deuda_id' => $deuda['id']
                ];
                
                if ($estado === 'Pagado') {
                    $fila['total_pagado'] += $monto;
                } else {
                    $fila['total_pendiente'] += $monto;
                }
                
                // Acumular totales por mes
                if (!isset($totalesPorMes[$mes])) {
                    $totalesPorMes[$mes] = ['pagado' => 0, 'pendiente' => 0];
                }
                
                if ($estado === 'Pagado') {
                    $totalesPorMes[$mes]['pagado'] += $monto;
                } else {
                    $totalesPorMes[$mes]['pendiente'] += $monto;
                }
            }
            
            $granTotalPagado += $fila['total_pagado'];
            $granTotalPendiente += $fila['total_pendiente'];
            
            $matriz[] = $fila;
        }
        
        return [
            'filas' => $matriz,
            'totales' => [
                'totales' => $totalesPorMes,
                'gran_total_pagado' => $granTotalPagado,
                'gran_total_pendiente' => $granTotalPendiente
            ]
        ];
    }

    /**
     * Obtiene meses disponibles para el consolidado
     * @param int $comunidadId
     * @param int $anio
     * @return array
     */
    private function getMesesDisponibles(int $comunidadId, int $anio): array {
        $sql = "SELECT DISTINCT d.mes 
                FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE p.comunidad_id = :comunidad_id 
                AND d.anio = :anio
                ORDER BY d.mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':anio' => $anio
        ]);
        
        return array_column($stmt->fetchAll(), 'mes');
    }

    /**
     * Exportar a CSV (compatible con Excel)
     */
    public function exportar(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        if (!$comunidadId) {
            flash('error', 'Debe seleccionar una comunidad para exportar');
            redirect('consolidados.php');
        }
        
        // Obtener datos
        $comunidad = $this->comunidadModel->find($comunidadId);
        if (!$comunidad) {
            flash('error', 'Comunidad no encontrada');
            redirect('consolidados.php');
        }
        
        $resultado = $this->generarMatriz($comunidadId, $anio);
        $matriz = $resultado['filas'];
        $totales = $resultado['totales'];
        $meses = $this->getMesesDisponibles($comunidadId, $anio);
        
        if (empty($matriz)) {
            flash('error', 'No hay datos para exportar');
            redirect('consolidados.php?comunidad_id=' . $comunidadId . '&anio=' . $anio);
        }
        
        // Generar nombre de archivo
        $filename = 'Consolidado_' . preg_replace('/[^a-zA-Z0-9]/', '_', $comunidad['nombre']) . '_' . $anio . '_' . date('Ymd_His') . '.csv';
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Crear output stream
        $output = fopen('php://output', 'w');
        
        // BOM para que Excel reconozca UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Título del reporte
        fputcsv($output, ['CONSOLIDADO DE PAGOS']);
        fputcsv($output, ['Comunidad:', $comunidad['nombre']]);
        fputcsv($output, ['Dirección:', $comunidad['direccion'] . ', ' . $comunidad['comuna']]);
        fputcsv($output, ['Año:', $anio]);
        fputcsv($output, ['Generado:', date('d/m/Y H:i:s')]);
        fputcsv($output, []); // Línea vacía
        
        // Encabezados de la tabla
        $headers = ['Propiedad', 'Dueño'];
        foreach ($meses as $mes) {
            $headers[] = getMonthName($mes);
        }
        $headers[] = 'Total Pagado';
        $headers[] = 'Total Pendiente';
        fputcsv($output, $headers);
        
        // Datos de propiedades
        foreach ($matriz as $fila) {
            $row = [
                $fila['propiedad']['nombre'],
                $fila['propiedad']['nombre_dueno']
            ];
            
            foreach ($meses as $mes) {
                if (isset($fila['meses'][$mes])) {
                    $estado = $fila['meses'][$mes]['estado'];
                    $monto = $fila['meses'][$mes]['monto'];
                    $row[] = $estado === 'Pagado' ? 'Pagado ($' . number_format($monto, 0, ',', '.') . ')' : 'Pendiente ($' . number_format($monto, 0, ',', '.') . ')';
                } else {
                    $row[] = '-';
                }
            }
            
            $row[] = $fila['total_pagado'] > 0 ? '$' . number_format($fila['total_pagado'], 0, ',', '.') : '-';
            $row[] = $fila['total_pendiente'] > 0 ? '$' . number_format($fila['total_pendiente'], 0, ',', '.') : '-';
            
            fputcsv($output, $row);
        }
        
        // Fila de totales
        $totalsRow = ['TOTALES', ''];
        foreach ($meses as $mes) {
            if (isset($totales['totales'][$mes])) {
                $mesTotal = $totales['totales'][$mes];
                $totalsRow[] = 'Rec: $' . number_format($mesTotal['pagado'], 0, ',', '.') . ' / Pen: $' . number_format($mesTotal['pendiente'], 0, ',', '.');
            } else {
                $totalsRow[] = '-';
            }
        }
        $totalsRow[] = '$' . number_format($totales['gran_total_pagado'] ?? 0, 0, ',', '.');
        $totalsRow[] = '$' . number_format($totales['gran_total_pendiente'] ?? 0, 0, ',', '.');
        fputcsv($output, $totalsRow);
        
        fclose($output);
        exit;
    }
}
