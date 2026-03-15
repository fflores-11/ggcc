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
     * Exportar a Excel (placeholder)
     */
    public function exportar(): void {
        flash('success', 'Función de exportación a Excel en desarrollo');
        redirect('consolidados.php');
    }
}
