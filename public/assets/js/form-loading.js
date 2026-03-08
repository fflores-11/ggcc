/**
 * Form Loading Handler - Bloqueo de botones durante operaciones
 * Se ejecuta automáticamente en todos los formularios y enlaces de acción
 */

(function() {
    'use strict';

    // Configuración
    const CONFIG = {
        loadingText: 'Procesando...',
        loadingClass: 'disabled',
        spinnerHtml: '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>',
        excludedSelectors: ['[data-no-loading]', '.no-loading']
    };

    // Track forms currently being submitted to prevent duplicates
    const submittingForms = new WeakSet();
    
    // Track links currently being clicked to prevent duplicates
    const clickingLinks = new WeakSet();

    /**
     * Bloquea todos los botones de un formulario
     */
    function disableFormButtons(form) {
        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(function(button) {
            // Guardar estado original
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.innerHTML;
            }
            if (!button.dataset.originalDisabled) {
                button.dataset.originalDisabled = button.disabled;
            }
            
            // Deshabilitar y mostrar spinner
            button.disabled = true;
            button.innerHTML = CONFIG.spinnerHtml + CONFIG.loadingText;
            button.classList.add(CONFIG.loadingClass);
        });
    }

    /**
     * Desbloquea los botones de un formulario (en caso de error)
     */
    function enableFormButtons(form) {
        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(function(button) {
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }
            if (button.dataset.originalDisabled) {
                button.disabled = button.dataset.originalDisabled === 'true';
            } else {
                button.disabled = false;
            }
            button.classList.remove(CONFIG.loadingClass);
        });
    }

    /**
     * Bloquea un enlace de acción
     */
    function disableActionLink(link) {
        if (!link.dataset.originalDisabled) {
            link.dataset.originalDisabled = link.getAttribute('disabled') || 'false';
        }
        link.style.pointerEvents = 'none';
        link.style.opacity = '0.6';
        link.classList.add(CONFIG.loadingClass);
        
        // Si tiene icono, agregar spinner
        const icon = link.querySelector('.bi');
        if (icon) {
            if (!link.dataset.originalIconClass) {
                link.dataset.originalIconClass = icon.className;
            }
            icon.className = 'bi bi-hourglass-split';
        }
    }

    /**
     * Desbloquea un enlace de acción
     */
    function enableActionLink(link) {
        link.style.pointerEvents = '';
        link.style.opacity = '';
        link.classList.remove(CONFIG.loadingClass);
        
        // Restaurar icono original
        const icon = link.querySelector('.bi');
        if (icon && link.dataset.originalIconClass) {
            icon.className = link.dataset.originalIconClass;
        }
    }

    /**
     * Verifica si un elemento está excluido
     */
    function isExcluded(element) {
        return CONFIG.excludedSelectors.some(function(selector) {
            return element.matches(selector) || element.closest(selector);
        });
    }

    /**
     * Inicializa el manejador de formularios
     */
    function initFormHandler() {
        // Escuchar submits de formularios POST - usar capturing para interceptar temprano
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Solo procesar formularios POST
            if (form.method && form.method.toUpperCase() !== 'POST') {
                return;
            }
            
            // No procesar si está excluido
            if (isExcluded(form)) {
                return;
            }

            // Prevenir envío duplicado
            if (submittingForms.has(form) || form.dataset.isSubmitting === 'true') {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Marcar formulario como enviándose
            submittingForms.add(form);
            form.dataset.isSubmitting = 'true';
            
            // Bloquear botones inmediatamente
            disableFormButtons(form);
        }, true); // Use capturing phase
    }

    /**
     * Inicializa el manejador de enlaces de acción
     */
    function initLinkHandler() {
        // Enlaces de eliminación y acciones importantes
        const actionSelectors = [
            'a[href*="action=delete"]',
            'a[href*="action=restore"]',
            'a[href*="action=cerrar"]',
            'a[href*="action=recalcular"]',
            'a[href*="action=eliminar"]',
            'a[onclick*="confirm"]'
        ];

        actionSelectors.forEach(function(selector) {
            document.querySelectorAll(selector).forEach(function(link) {
                if (isExcluded(link)) {
                    return;
                }

                link.addEventListener('click', function(e) {
                    // Prevenir clic duplicado
                    if (clickingLinks.has(link) || link.dataset.isClicking === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    // Marcar como clickeando
                    clickingLinks.add(link);
                    link.dataset.isClicking = 'true';

                    // Si tiene confirmación, verificar que el usuario confirmó
                    if (link.onclick && link.onclick.toString().includes('confirm')) {
                        // Permitir que el confirm se ejecute
                        // Si el usuario cancela, restaurar el estado
                        setTimeout(function() {
                            // Si el enlace aún existe y no se ha navegado
                            if (document.contains(link)) {
                                clickingLinks.delete(link);
                                delete link.dataset.isClicking;
                            }
                        }, 100);
                    }
                    
                    disableActionLink(link);
                });
            });
        });
    }

    /**
     * Inicializa manejador de botones que abren modales
     */
    function initModalButtonHandler() {
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
            if (isExcluded(button)) {
                return;
            }

            button.addEventListener('click', function() {
                // El modal se abrirá, no necesitamos bloquear
                // Los formularios dentro del modal ya están cubiertos
            });
        });
    }

    /**
     * Maneja el botón de volver atrás del navegador
     */
    function handlePageShow() {
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                // La página se cargó desde caché (botón atrás)
                // Desbloquear todos los formularios y enlaces
                document.querySelectorAll('form').forEach(function(form) {
                    submittingForms.delete(form);
                    delete form.dataset.isSubmitting;
                    enableFormButtons(form);
                });
                document.querySelectorAll('a.' + CONFIG.loadingClass).forEach(function(link) {
                    clickingLinks.delete(link);
                    delete link.dataset.isClicking;
                    enableActionLink(link);
                });
            }
        });
    }

    /**
     * Inicializa todo
     */
    function init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initFormHandler();
                initLinkHandler();
                initModalButtonHandler();
                handlePageShow();
            });
        } else {
            // DOM ya está listo
            initFormHandler();
            initLinkHandler();
            initModalButtonHandler();
            handlePageShow();
        }
    }

    // Ejecutar inicialización
    init();

})();