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
                    // ✓ para pagado, P para pendiente + monto como número limpio
                    $row[] = $estado === 'Pagado' ? "OK {$monto}" : "X {$monto}";
                } else {
                    $row[] = '-';
                }
            }
            
            $row[] = $fila['total_pagado'] > 0 ? $fila['total_pagado'] : 0;
            $row[] = $fila['total_pendiente'] > 0 ? $fila['total_pendiente'] : 0;
            
            fputcsv($output, $row);
        }
        
        // Fila de totales
        $totalsRow = ['TOTALES', ''];
        foreach ($meses as $mes) {
            if (isset($totales['totales'][$mes])) {
                $mesTotal = $totales['totales'][$mes];
                $totalsRow[] = $mesTotal['pagado'] + $mesTotal['pendiente'];
            } else {
                $totalsRow[] = 0;
            }
        }
        $totalsRow[] = $totales['gran_total_pagado'] ?? 0;
        $totalsRow[] = $totales['gran_total_pendiente'] ?? 0;
        fputcsv($output, $totalsRow);
        
        fclose($output);
        exit;
    }

    /**
     * Exportar a PDF con colores e iconos
     */
    public function exportarPDF(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        if (!$comunidadId) {
            flash('error', 'Debe seleccionar una comunidad para exportar');
            redirect('consolidados.php');
        }
        
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
        
        // Cargar Dompdf
        require_once ROOT_PATH . '/vendor/autoload.php';
        
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('isPhpEnabled', false);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        // Generar HTML
        $html = $this->generarHTMLPDFConsolidado($comunidad, $anio, $matriz, $totales, $meses);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'Consolidado_' . preg_replace('/[^a-zA-Z0-9]/', '_', $comunidad['nombre']) . '_' . $anio . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Genera el HTML para el PDF del consolidado
     */
    private function generarHTMLPDFConsolidado(array $comunidad, int $anio, array $matriz, array $totales, array $meses): string {
        // Generar filas de la tabla
        $filasHTML = '';
        foreach ($matriz as $fila) {
            $filasHTML .= '<tr>';
            $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; font-size: 10px;"><strong>' . htmlspecialchars($fila['propiedad']['nombre']) . '</strong><br><small style="color: #666;">' . htmlspecialchars($fila['propiedad']['nombre_dueno']) . '</small></td>';
            
            foreach ($meses as $mes) {
                if (isset($fila['meses'][$mes])) {
                    $estado = $fila['meses'][$mes]['estado'];
                    $monto = $fila['meses'][$mes]['monto'];
                    if ($estado === 'Pagado') {
                        $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: center; background-color: #d4edda; font-size: 10px;"><div style="display: inline-block; width: 32px; height: 32px; background-color: #28a745; color: white; text-align: center; line-height: 32px; font-weight: bold; font-size: 12px; margin-bottom: 3px;">OK</div><br><small>$' . number_format($monto, 0, ',', '.') . '</small></td>';
                    } else {
                        $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: center; background-color: #fff3cd; font-size: 10px;"><div style="display: inline-block; width: 32px; height: 32px; background-color: #dc3545; color: white; text-align: center; line-height: 32px; font-weight: bold; font-size: 12px; margin-bottom: 3px;">X</div><br><small>$' . number_format($monto, 0, ',', '.') . '</small></td>';
                    }
                } else {
                    $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: center; color: #adb5bd; font-size: 10px;">-</td>';
                }
            }
            
            $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: right; font-size: 10px; color: #155724; font-weight: bold;">' . ($fila['total_pagado'] > 0 ? '$' . number_format($fila['total_pagado'], 0, ',', '.') : '-') . '</td>';
            $filasHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: right; font-size: 10px; color: #721c24; font-weight: bold;">' . ($fila['total_pendiente'] > 0 ? '$' . number_format($fila['total_pendiente'], 0, ',', '.') : '-') . '</td>';
            $filasHTML .= '</tr>';
        }
        
        // Fila de totales
        $totalsHTML = '<tr style="background-color: #e3f2fd;">';
        $totalsHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; font-size: 10px; font-weight: bold;">TOTALES</td>';
        foreach ($meses as $mes) {
            if (isset($totales['totales'][$mes])) {
                $mesTotal = $totales['totales'][$mes];
                $totalsHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: center; font-size: 9px;">';
                $totalsHTML .= '<div style="display: inline-block; width: 20px; height: 20px; background-color: #28a745; color: white; text-align: center; line-height: 20px; font-weight: bold; font-size: 8px;">OK</div> <span style="color: #155724;">$' . number_format($mesTotal['pagado'], 0, ',', '.') . '</span><br>';
                $totalsHTML .= '<div style="display: inline-block; width: 20px; height: 20px; background-color: #dc3545; color: white; text-align: center; line-height: 20px; font-weight: bold; font-size: 8px;">X</div> <span style="color: #721c24;">$' . number_format($mesTotal['pendiente'], 0, ',', '.') . '</span>';
                $totalsHTML .= '</td>';
            } else {
                $totalsHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: center; font-size: 10px;">-</td>';
            }
        }
        $totalsHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: right; font-size: 10px; font-weight: bold; color: #155724;">$' . number_format($totales['gran_total_pagado'] ?? 0, 0, ',', '.') . '</td>';
        $totalsHTML .= '<td style="padding: 6px; border: 1px solid #dee2e6; text-align: right; font-size: 10px; font-weight: bold; color: #721c24;">$' . number_format($totales['gran_total_pendiente'] ?? 0, 0, ',', '.') . '</td>';
        $totalsHTML .= '</tr>';
        
        // Encabezados de meses
        $mesesHTML = '';
        foreach ($meses as $mes) {
            $mesesHTML .= '<th style="padding: 8px; border: 1px solid #dee2e6; background-color: #495057; color: white; text-align: center; font-size: 10px; min-width: 70px;">' . getMonthName($mes) . '</th>';
        }
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0 0; opacity: 0.9; font-size: 12px; }
        .info { margin-bottom: 15px; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 8px; border: 1px solid #dee2e6; background-color: #495057; color: white; font-size: 10px; }
        .legend { margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 10px; }
        .legend-item { display: inline-block; margin-right: 15px; }
        .badge-ok { display: inline-block; width: 20px; height: 20px; background: #28a745; color: white; text-align: center; line-height: 20px; font-weight: bold; font-size: 8px; }
        .badge-pending { display: inline-block; width: 20px; height: 20px; background: #dc3545; color: white; text-align: center; line-height: 20px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CONSOLIDADO DE PAGOS</h2>
        <p>' . htmlspecialchars($comunidad['nombre']) . ' &mdash; ' . htmlspecialchars($comunidad['direccion']) . ', ' . htmlspecialchars($comunidad['comuna']) . '</p>
    </div>
    <div class="info">
        <strong>Año:</strong> ' . $anio . ' &nbsp;|&nbsp; 
        <strong>Generado:</strong> ' . date('d/m/Y H:i') . ' &nbsp;|&nbsp;
        <strong>Propiedades:</strong> ' . count($matriz) . '
    </div>
    <table>
        <thead>
            <tr>
                <th style="min-width: 150px; text-align: left;">Propiedad / Dueño</th>
                ' . $mesesHTML . '
                <th style="text-align: right;">Total Pagado</th>
                <th style="text-align: right;">Total Pendiente</th>
            </tr>
        </thead>
        <tbody>
            ' . $filasHTML . '
            ' . $totalsHTML . '
        </tbody>
    </table>
    <div class="legend">
        <div class="legend-item"><span class="badge-ok">OK</span> Pagado</div>
        <div class="legend-item"><span class="badge-pending">X</span> Pendiente</div>
        <div class="legend-item" style="color: #adb5bd;">- Sin deuda registrada</div>
    </div>
</body>
</html>';
    }
}
