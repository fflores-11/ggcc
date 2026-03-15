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
        // Si es propietario, obtener su comunidad y propiedad
        if (getUserRole() === 'propietario') {
            $userModel = new Usuario();
            $usuario = $userModel->getUsuarioPropietario(getUserId());
            if ($usuario) {
                redirect('reportes.php?action=morosidad&comunidad_id=' . $usuario['comunidad_id'] . '&propiedad_id=' . $usuario['propiedad_id']);
            }
        }

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
        
        // Si es propietario, forzar filtro por su propiedad
        if (getUserRole() === 'propietario') {
            $userModel = new Usuario();
            $usuario = $userModel->getUsuarioPropietario(getUserId());
            if ($usuario) {
                $comunidadId = $usuario['comunidad_id'];
                $propiedadId = $usuario['propiedad_id'];
                $comunidad = $this->comunidadModel->find($comunidadId);
                // Obtener morosidad solo de la propiedad del usuario
                $moroso = $this->getMorosoByPropiedad($propiedadId);
                if ($moroso) {
                    $morosos = [$moroso];
                }
            }
        } elseif ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $morosos = $this->getMorosos($comunidadId, $minimoMeses);
        }
        
        $title = 'Reporte de Morosidad';
        require_once VIEWS_PATH . '/reportes/morosidad.php';
    }

    /**
     * Obtiene morosidad de una propiedad específica
     */
    private function getMorosoByPropiedad(int $propiedadId): ?array {
        $sql = "SELECT p.id, p.nombre as propiedad_nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno,
                       c.nombre as comunidad_nombre,
                       COUNT(d.id) as meses_adeudados,
                       SUM(d.monto) as total_adeudado,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio, d.mes SEPARATOR ', ') as periodos_adeudados,
                       MIN(CONCAT(d.anio, '-', LPAD(d.mes, 2, '0'))) as primera_deuda
                FROM propiedades p
                LEFT JOIN deudas d ON p.id = d.propiedad_id AND d.estado = 'Pendiente'
                LEFT JOIN comunidades c ON p.comunidad_id = c.id
                WHERE p.id = :propiedad_id 
                AND p.activo = 1
                AND d.estado = 'Pendiente'
                GROUP BY p.id, p.nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno, c.nombre
                HAVING meses_adeudados > 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':propiedad_id' => $propiedadId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
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
        
        // Si es propietario, forzar filtro por su propiedad
        if (getUserRole() === 'propietario') {
            $userModel = new Usuario();
            $usuario = $userModel->getUsuarioPropietario(getUserId());
            if ($usuario) {
                $comunidadId = $usuario['comunidad_id'];
                $propiedadId = $usuario['propiedad_id'];
                $comunidad = $this->comunidadModel->find($comunidadId);
                $pagos = $this->getPagosPorPeriodoYPropiedad($propiedadId, $mes, $anio);
                $totalRecaudado = array_sum(array_column($pagos, 'monto'));
            }
        } elseif ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $pagos = $this->getPagosPorPeriodo($comunidadId, $mes, $anio);
            $totalRecaudado = array_sum(array_column($pagos, 'monto'));
        }
        
        $title = 'Reporte de Pagos';
        require_once VIEWS_PATH . '/reportes/pagos.php';
    }

    /**
     * Obtiene pagos por período y propiedad
     */
    private function getPagosPorPeriodoYPropiedad(int $propiedadId, int $mes, int $anio): array {
        $sql = "SELECT p.id, p.fecha, p.monto, p.observaciones,
                       pr.nombre as propiedad_nombre, pr.nombre_dueno,
                       GROUP_CONCAT(DISTINCT CONCAT(d.mes, '-', d.anio) ORDER BY d.anio, d.mes SEPARATOR ', ') as meses_pagados
                FROM pagos p
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id
                LEFT JOIN pagos_detalle pd ON p.id = pd.pago_id
                LEFT JOIN deudas d ON pd.deuda_id = d.id
                WHERE p.propiedad_id = :propiedad_id
                AND MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
                GROUP BY p.id
                ORDER BY p.fecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':propiedad_id' => $propiedadId,
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return $stmt->fetchAll();
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
        
        // Si es propietario, forzar filtro por su propiedad
        if (getUserRole() === 'propietario') {
            $userModel = new Usuario();
            $usuario = $userModel->getUsuarioPropietario(getUserId());
            if ($usuario) {
                $comunidadId = $usuario['comunidad_id'];
                $propiedadId = $usuario['propiedad_id'];
                $comunidad = $this->comunidadModel->find($comunidadId);
                $deudas = $this->getDeudasPorPeriodoYPropiedad($propiedadId, $mes, $anio);
                $totalPendiente = array_sum(array_column($deudas, 'monto'));
            }
        } elseif ($comunidadId) {
            $comunidad = $this->comunidadModel->find($comunidadId);
            $deudas = $this->getDeudasPorPeriodo($comunidadId, $mes, $anio);
            $totalPendiente = array_sum(array_column($deudas, 'monto'));
        }
        
        $title = 'Reporte de Deudas';
        require_once VIEWS_PATH . '/reportes/deudas.php';
    }

    /**
     * Obtiene deudas por período y propiedad
     */
    private function getDeudasPorPeriodoYPropiedad(int $propiedadId, int $mes, int $anio): array {
        $sql = "SELECT d.id, d.monto, d.estado, d.created_at,
                       p.nombre as propiedad_nombre, p.nombre_dueno, p.email_dueno, p.whatsapp_dueno
                FROM deudas d
                LEFT JOIN propiedades p ON d.propiedad_id = p.id
                WHERE d.propiedad_id = :propiedad_id
                AND d.mes = :mes AND d.anio = :anio
                AND p.activo = 1
                ORDER BY d.estado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':propiedad_id' => $propiedadId,
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        return $stmt->fetchAll();
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
        
        // Propietarios no pueden ver egresos (información administrativa)
        if (getUserRole() === 'propietario') {
            flash('error', 'No tiene permisos para ver este reporte');
            redirect('reportes.php');
        }
        
        // Obtener todos los pagos a colaboradores del período
        $egresos = $this->getEgresosPorPeriodo($mes, $anio);
        $totalEgresos = array_sum(array_column($egresos, 'monto'));
        
        // Obtener total de pagos de gastos comunes del mes
        $totalIngresosGC = $this->getTotalPagosGastosComunes($mes, $anio);
        
        // Obtener saldo del mes anterior
        $saldoMesAnterior = $this->getSaldoMesAnterior($mes, $anio);
        
        // Calcular saldo según fórmula: (pago gastos comunes + saldo mes anterior) - pago colaboradores
        $saldo = ($totalIngresosGC + $saldoMesAnterior) - $totalEgresos;
        
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

    /**
     * Obtiene el total de pagos de gastos comunes (ingresos) por período
     */
    private function getTotalPagosGastosComunes(int $mes, int $anio): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total
                FROM pagos
                WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Obtiene el saldo final del mes anterior
     * Busca el último saldo registrado y calcula desde ahí si es necesario
     */
    private function getSaldoMesAnterior(int $mes, int $anio): float {
        // Calcular mes y año anterior
        $mesAnterior = $mes - 1;
        $anioAnterior = $anio;
        
        if ($mesAnterior < 1) {
            $mesAnterior = 12;
            $anioAnterior--;
        }
        
        // Intentar obtener saldo guardado de la tabla saldos_mensuales para el mes anterior
        $sql = "SELECT saldo_final
                FROM saldos_mensuales
                WHERE mes = :mes AND anio = :anio
                ORDER BY id DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mes' => $mesAnterior,
            ':anio' => $anioAnterior
        ]);
        
        $result = $stmt->fetch();
        
        if ($result && isset($result['saldo_final'])) {
            return (float) $result['saldo_final'];
        }
        
        // Si no hay registro para el mes anterior, buscar el último saldo registrado antes de esa fecha
        $sql = "SELECT saldo_final, mes, anio
                FROM saldos_mensuales
                WHERE (anio < :anio1) OR (anio = :anio2 AND mes < :mes)
                ORDER BY anio DESC, mes DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':anio1' => $anioAnterior,
            ':anio2' => $anioAnterior,
            ':mes' => $mesAnterior
        ]);
        
        $ultimoSaldo = $stmt->fetch();
        
        if ($ultimoSaldo) {
            // Tenemos un saldo base, calcular desde ese mes hasta el mes anterior objetivo
            $saldoBase = (float) $ultimoSaldo['saldo_final'];
            $mesBase = (int) $ultimoSaldo['mes'];
            $anioBase = (int) $ultimoSaldo['anio'];
            
            // Calcular mes a mes desde el mes base hasta el mes anterior objetivo
            $mesActual = $mesBase;
            $anioActual = $anioBase;
            $saldoCalculado = $saldoBase;
            
            while (!($anioActual == $anioAnterior && $mesActual == $mesAnterior)) {
                // Avanzar al siguiente mes
                $mesActual++;
                if ($mesActual > 12) {
                    $mesActual = 1;
                    $anioActual++;
                }
                
                // Sumar ingresos y restar egresos de ese mes
                $ingresos = $this->getTotalPagosGastosComunes($mesActual, $anioActual);
                $egresos = $this->getTotalEgresosColaboradores($mesActual, $anioActual);
                $saldoCalculado = $saldoCalculado + $ingresos - $egresos;
            }
            
            return $saldoCalculado;
        }
        
        // Si no hay ningún saldo registrado, calcular desde el inicio (todos los meses anteriores)
        // Calcular: sumar todos los ingresos y restar todos los egresos desde el inicio de los tiempos
        // hasta el mes anterior objetivo
        $sqlIngresos = "SELECT COALESCE(SUM(monto), 0) as total
                       FROM pagos
                       WHERE (YEAR(fecha) < :anio1) OR (YEAR(fecha) = :anio2 AND MONTH(fecha) <= :mes)";
        
        $stmt = $this->db->prepare($sqlIngresos);
        $stmt->execute([
            ':anio1' => $anioAnterior,
            ':anio2' => $anioAnterior,
            ':mes' => $mesAnterior
        ]);
        $totalIngresos = (float) ($stmt->fetch()['total'] ?? 0);
        
        $sqlEgresos = "SELECT COALESCE(SUM(monto), 0) as total
                       FROM pagos_colaboradores
                       WHERE (YEAR(fecha) < :anio1) OR (YEAR(fecha) = :anio2 AND MONTH(fecha) <= :mes)";
        
        $stmt = $this->db->prepare($sqlEgresos);
        $stmt->execute([
            ':anio1' => $anioAnterior,
            ':anio2' => $anioAnterior,
            ':mes' => $mesAnterior
        ]);
        $totalEgresos = (float) ($stmt->fetch()['total'] ?? 0);
        
        return $totalIngresos - $totalEgresos;
    }
    
    /**
     * Obtiene el total de egresos (pagos a colaboradores) por período
     */
    private function getTotalEgresosColaboradores(int $mes, int $anio): float {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total
                FROM pagos_colaboradores
                WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mes' => $mes,
            ':anio' => $anio
        ]);
        
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }
}
