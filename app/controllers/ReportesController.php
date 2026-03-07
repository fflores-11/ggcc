<?php
/**
 * Controlador de Reportes
 * Genera reportes adicionales del sistema
 */

class ReportesController {
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
     * Página principal de reportes
     */
    public function index(): void {
        $comunidades = $this->comunidadModel->getForSelect();
        $title = 'Reportes';
        require_once VIEWS_PATH . '/reportes/index.php';
    }

    /**
     * Reporte de morosidad
     */
    public function morosidad(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $minimoMeses = isset($_GET['minimo_meses']) ? (int) $_GET['minimo_meses'] : 1;
        
        $comunidades = $this->comunidadModel->getForSelect();
        $morosos = [];
        $comunidad = null;
        
        if ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $morosos = $this->getMorosos($comunidadId, $minimoMeses);
        }
        
        $title = 'Reporte de Morosidad';
        require_once VIEWS_PATH . '/reportes/morosidad.php';
    }

    /**
     * Reporte de pagos por período
     */
    public function pagos(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : date('n');
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        $comunidades = $this->comunidadModel->getForSelect();
        $pagos = [];
        $comunidad = null;
        $totalRecaudado = 0;
        
        if ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $pagos = $this->getPagosPorPeriodo($comunidadId, $mes, $anio);
            $totalRecaudado = array_sum(array_column($pagos, 'monto'));
        }
        
        $title = 'Reporte de Pagos';
        require_once VIEWS_PATH . '/reportes/pagos.php';
    }

    /**
     * Reporte de deudas por período
     */
    public function deudas(): void {
        $comunidadId = isset($_GET['comunidad_id']) ? (int) $_GET['comunidad_id'] : null;
        $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : date('n');
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        $comunidades = $this->comunidadModel->getForSelect();
        $deudas = [];
        $comunidad = null;
        $totalPendiente = 0;
        
        if ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $deudas = $this->getDeudasPorPeriodo($comunidadId, $mes, $anio);
            $totalPendiente = array_sum(array_column($deudas, 'monto'));
        }
        
        $title = 'Reporte de Deudas';
        require_once VIEWS_PATH . '/reportes/deudas.php';
    }

    /**
     * Reporte de egresos (pagos a colaboradores)
     */
    public function egresos(): void {
        $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : date('n');
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : date('Y');
        
        $comunidades = $this->comunidadModel->getForSelect();
        $egresos = [];
        $totalEgresos = 0;
        
        // Obtener todos los pagos a colaboradores del período
        $egresos = $this->getEgresosPorPeriodo($mes, $anio);
        $totalEgresos = array_sum(array_column($egresos, 'monto'));
        
        $title = 'Reporte de Egresos - ' . getMonthName($mes) . ' ' . $anio;
        require_once VIEWS_PATH . '/reportes/egresos.php';
    }

    /**
     * Obtiene propiedades morosas
     */
    private function getMorosos(int $comunidadId, int $minimoMeses): array {
        $sql = "SELECT p.id, p.nombre as propiedad_nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno,
                       c.nombre as comunidad_nombre,
                       COUNT(d.id) as meses_adeudados,
                       SUM(d.monto) as total_adeudado,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio, d.mes SEPARATOR ', ') as periodos_adeudados,
                       MIN(CONCAT(d.anio, '-', LPAD(d.mes, 2, '0'))) as primera_deuda
                FROM propiedades p
                LEFT JOIN deudas d ON p.id = d.propiedad_id AND d.estado = 'Pendiente'
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE p.comunidad_id = :comunidad_id 
                AND p.activo = 1
                AND d.estado = 'Pendiente'
                GROUP BY p.id, p.nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno, c.nombre
                HAVING meses_adeudados >= :minimo_meses
                ORDER BY total_adeudado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':minimo_meses' => $minimoMeses
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene pagos por período
     */
    private function getPagosPorPeriodo(int $comunidadId, int $mes, int $anio): array {
        $sql = "SELECT p.id, p.fecha, p.monto, p.observaciones,
                       pr.nombre as propiedad_nombre, pr.nombre_dueno,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio, d.mes SEPARATOR ', ') as meses_pagados
                FROM pagos p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                WHERE pr.comunidad_id = :comunidad_id
                AND MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
                GROUP BY p.id
                ORDER BY p.fecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene deudas por período
     */
    private function getDeudasPorPeriodo(int $comunidadId, int $mes, int $anio): array {
        $sql = "SELECT d.id, d.monto, d.estado, d.created_at,
                       p.nombre as propiedad_nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno
                FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE p.comunidad_id = :comunidad_id
                AND d.mes = :mes AND d.anio = :anio
                AND p.activo = 1
                ORDER BY d.estado DESC, p.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':comunidad_id' => $comunidadId,
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene egresos (pagos a colaboradores) por período
     */
    private function getEgresosPorPeriodo(int $mes, int $anio): array {
        $sql = "SELECT pc.id, pc.fecha, pc.monto, pc.detalle,
                       c.nombre as colaborador_nombre,
                       c.tipo_colaborador,
                       c.numero_cliente,
                       u.nombre as pagado_por_nombre
                FROM pagos_colaboradores pc
                LEFT JOIN colaboradores c ON pc.colaborador_id = c.id
                LEFT JOIN usuarios u ON pc.pagado_por = u.id
                WHERE MONTH(pc.fecha) = :mes AND YEAR(pc.fecha) = :anio
                ORDER BY pc.fecha ASC, pc.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return $stmt->fetchAll();
    }
}
