<?php
/**
 * Helper de Paginación
 * Sistema de paginación para listados
 */

class PaginationHelper {
    
    /**
     * Obtiene los parámetros de paginación de la URL
     * @param int $defaultPerPage Registros por página por defecto
     * @return array ['page' => int, 'perPage' => int, 'offset' => int]
     */
    public static function getParams(int $defaultPerPage = 20): array {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : $defaultPerPage;
        
        // Validar valores
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = $defaultPerPage;
        if ($perPage > 100) $perPage = 100; // Máximo 100 registros por página
        
        $offset = ($page - 1) * $perPage;
        
        return [
            'page' => $page,
            'perPage' => $perPage,
            'offset' => $offset
        ];
    }
    
    /**
     * Genera el HTML del paginador
     * @param int $totalRecords Total de registros
     * @param int $currentPage Página actual
     * @param int $perPage Registros por página
     * @param string $baseUrl URL base para los enlaces
     * @return string HTML del paginador
     */
    public static function render(int $totalRecords, int $currentPage, int $perPage, string $baseUrl): string {
        if ($totalRecords <= $perPage) {
            return '';
        }
        
        $totalPages = (int) ceil($totalRecords / $perPage);
        
        // Asegurar que la página actual esté en rango
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        
        // Construir URL base (preservar otros parámetros GET)
        $parsedUrl = parse_url($baseUrl);
        $path = $parsedUrl['path'] ?? '';
        
        // Obtener query params existentes
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }
        
        // Función helper para construir URL de página
        $buildUrl = function($page) use ($path, $queryParams, $perPage) {
            $params = $queryParams;
            $params['page'] = $page;
            $params['per_page'] = $perPage;
            return $path . '?' . http_build_query($params);
        };
        
        $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center">';
        
        // Botón "Anterior"
        if ($currentPage > 1) {
            $html .= sprintf(
                '<li class="page-item"><a class="page-link" href="%s"><i class="bi bi-chevron-left"></i></a></li>',
                $buildUrl($currentPage - 1)
            );
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i></span></li>';
        }
        
        // Páginas
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        // Ajustar para mostrar siempre 5 páginas si es posible
        if ($endPage - $startPage < 4) {
            if ($startPage === 1) {
                $endPage = min($totalPages, $startPage + 4);
            } else {
                $startPage = max(1, $endPage - 4);
            }
        }
        
        // Primera página + ellipsis
        if ($startPage > 1) {
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">1</a></li>', $buildUrl(1));
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Páginas intermedias
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $currentPage) {
                $html .= sprintf('<li class="page-item active"><span class="page-link">%d</span></li>', $i);
            } else {
                $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">%d</a></li>', $buildUrl($i), $i);
            }
        }
        
        // Última página + ellipsis
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">%d</a></li>', $buildUrl($totalPages), $totalPages);
        }
        
        // Botón "Siguiente"
        if ($currentPage < $totalPages) {
            $html .= sprintf(
                '<li class="page-item"><a class="page-link" href="%s"><i class="bi bi-chevron-right"></i></a></li>',
                $buildUrl($currentPage + 1)
            );
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-right"></i></span></li>';
        }
        
        $html .= '</ul></nav>';
        
        // Info de registros
        $startRecord = ($currentPage - 1) * $perPage + 1;
        $endRecord = min($currentPage * $perPage, $totalRecords);
        $html .= sprintf(
            '<div class="text-center text-muted small mt-2">Mostrando %d - %d de %d registros</div>',
            $startRecord,
            $endRecord,
            $totalRecords
        );
        
        return $html;
    }
    
    /**
     * Obtiene la URL actual con todos los parámetros GET
     * @return string
     */
    public static function getCurrentUrl(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Eliminar parámetros de página existentes
        $uri = preg_replace('/([&?])page=\d+/', '', $uri);
        $uri = preg_replace('/([&?])per_page=\d+/', '', $uri);
        
        // Limpiar ?& o &&
        $uri = str_replace(['?&', '&&'], ['?', '&'], $uri);
        
        return $protocol . '://' . $host . $uri;
    }
}

/**
 * Función helper para obtener parámetros de paginación
 * @param int $defaultPerPage
 * @return array
 */
function getPaginationParams(int $defaultPerPage = 20): array {
    return PaginationHelper::getParams($defaultPerPage);
}

/**
 * Función helper para renderizar paginador
 * @param int $totalRecords
 * @param int $currentPage
 * @param int $perPage
 * @param string|null $baseUrl
 * @return string
 */
function renderPagination(int $totalRecords, int $currentPage, int $perPage, ?string $baseUrl = null): string {
    if ($baseUrl === null) {
        $baseUrl = PaginationHelper::getCurrentUrl();
    }
    return PaginationHelper::render($totalRecords, $currentPage, $perPage, $baseUrl);
}
