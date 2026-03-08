# Changelog - Sistema GGCC

Todos los cambios notables en el proyecto Sistema de Gestión de Gastos Comunes (GGCC) serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).

## [1.0.0] - 2026-03-08

### 🎉 Primera Versión Completa - Sistema Funcional

#### ✨ Nuevas Funcionalidades

##### Sistema de Logos Dual (Claro/Oscuro)
- **Configuración de logos dual para el sistema**
  - Soporte para logo en modo claro y logo en modo oscuro
  - Subida independiente de ambos logos desde el panel de configuración
  - Almacenamiento de rutas en base de datos (`logo_path` y `logo_path_dark`)
  - Visualización centrada con fondo apropiado para cada modo
  - Aplicación en sidebar del panel administrativo y página de login
  - CSS optimizado para mostrar siempre el logo claro (deshabilitado auto-detección por preferencias del sistema)

##### Fase 1: Base del Sistema + Autenticación + Mantenedores
- **Arquitectura MVC** completa con separación de responsabilidades
  - Controladores para cada módulo
  - Modelos con PDO y prepared statements
  - Vistas con Bootstrap 5
- **Sistema de Autenticación**
  - Login con `password_hash()` (PHP 8.1)
  - Protección CSRF en todos los formularios
  - Gestión de sesiones segura
  - Tres roles de usuario: admin, administrador, presidente
- **Mantenedor de Usuarios** (CRUD completo)
  - Crear, editar, eliminar usuarios
  - Validación de email único
  - No permite auto-eliminación
  - Cambio de contraseña con hash seguro
- **Mantenedor de Comunidades**
  - Datos completos: nombre, dirección, región, comuna
  - Información del presidente: nombre, email, WhatsApp
  - Conteo de propiedades por comunidad
  - Soft delete (desactivar/reactivar)
- **Mantenedor de Propiedades**
  - Tres tipos: Casa, Departamento, Parcela
  - Precio de gastos comunes configurable
  - Datos del dueño: nombre, email, WhatsApp
  - Datos opcionales del agente
  - Relación con comunidad (select dinámico)

##### Fase 2: Operaciones Principales
- **Módulo de Pagos**
  - Registro de pagos con selección dinámica de deudas
  - Pago de múltiples meses en una transacción
  - Generación automática de recibos con número correlativo (REC-XXXXXX-YYYY)
  - **Recibo con descarga real en PDF** usando Dompdf v3.1.5
  - Recibo imprimible con diseño profesional
  - Historial de pagos por propiedad y comunidad
  - API AJAX para obtener deudas pendientes
- **Módulo de Deudas**
  - Generación automática de deudas mensuales masivas
  - Cálculo de totales: pagado vs pendiente
  - Resumen por mes y año
- **Envío de Correos Masivos**
  - Editor WYSIWYG (TinyMCE) para mensajes HTML
  - **Envío General**: a toda la comunidad
  - **Envío de Cobranzas**: solo a propiedades con deudas
  - Variables dinámicas personalizables:
    - `{nombre_propiedad}`, `{nombre_dueno}`
    - `{monto_deuda}`, `{mes}`, `{anio}`
    - `{comunidad}`, `{direccion}`, `{presidente}`
  - Tracking de envíos: exitosos vs fallidos
  - Reenvío individual de correos fallidos
  - Historial completo de envíos

##### Fase 3: Dashboard + Reportes + Consolidados
- **Dashboard Principal**
  - Métricas en tiempo real:
    - Total comunidades activas
    - Total propiedades registradas
    - Total deuda pendiente
    - Pagos del mes actual
  - Gráfico de tendencias (Chart.js) - últimos 6 meses
  - Comunidades con mayor deuda
  - Propiedades morosas (top 5)
  - Últimas actividades (pagos recientes)
  - Resumen por comunidad con % de cobranza
  - Accesos rápidos a operaciones comunes
- **Módulo de Consolidados**
  - Matriz de pagos por propiedad y mes
  - Filtros por comunidad y año
  - Visualización: ✅ Pagado / ⏳ Pendiente
  - Fila de totales al final
  - Exportación a Excel (placeholder)
- **Reportes**
  - **Morosidad**: propiedades por meses adeudados (filtro configurable)
  - **Pagos por Período**: detalle de pagos por mes/año
  - **Deudas por Período**: listado de deudas pagadas/pendientes
  - Totales calculados automáticamente

#### 🔧 Mejoras y Funcionalidades Adicionales

##### Funcionalidades Específicas Solicitadas
- **Generación automática de deudas para nuevas propiedades**
  - Al crear una propiedad, se generan automáticamente deudas por todos los períodos existentes de la comunidad
  - Mensaje informativo: "Se generaron X deuda(s) automáticamente"
- **Ordenamiento de propiedades por Nombre**
  - Cambio de orden en todas las consultas: de `id ASC` a `nombre ASC`
  - Las propiedades ahora se muestran alfabéticamente: Casa 1, Casa 2, Casa 3, etc.
  - Aplicado en: listado, select/options, búsqueda, y filtrado por comunidad
- **Icono de mostrar/ocultar contraseña en login**
  - Toggle visual para el campo de password
  - Cambio dinámico de icono (ojo abierto/cerrado)
  - Eliminación de credenciales por defecto visibles en login
- **Configuración SMTP por Comunidad** (Super Usuario)
  - Tabla `configuracion_smtp` para guardar config individual
  - Formulario exclusivo para administradores (rol: admin)
  - Campos: host, puerto, usuario, password, encriptación, remitente
  - Validación antes de envío: verifica existencia de configuración SMTP
  - Helper MailerHelper preparado para envío real con SwiftMailer
  - Guía de configuración para Gmail, Outlook, cPanel
- **Vista de detalle de propiedad**
  - Información completa de la propiedad
  - Listado de deudas pendientes
  - Historial de pagos con enlaces a recibos
  - Botones rápidos: "Registrar Pago", "Enviar Cobranza"
- **Imagen de fondo por defecto para Login**
  - Imagen `background-default.png` en `/public/assets/images/`
  - Se usa automáticamente cuando no hay imagen configurada
  - Permisos y propietario correctos (www-data:www-data, 755)
  - Configuración en base de datos actualizada
- **Restricción por Comunidad para Usuarios Administradores**
  - Nuevo campo `comunidad_id` en tabla `usuarios`
  - Usuarios con rol "Administrador" deben tener asignada una comunidad
  - Usuarios solo pueden acceder a registros de su comunidad asignada
  - Super Admin (`admin`) tiene acceso a todas las comunidades
  - Helpers: `getUserComunidadId()`, `hasAccessToComunidad()`, `requireAccessToComunidad()`
  - Selector de comunidad dinámico en formulario de usuarios (solo para admin/presidente)
- **Configuración de imagen de fondo para Login**
  - Nueva sección en Configuración del Sistema
  - Permite subir imagen personalizada de fondo para la página de inicio
  - Modos de visualización: Cover (cubrir), Contain (ajustar), Repeat (repetir)
  - Si no hay imagen configurada, usa el fondo azul por defecto
  - Vista previa en tiempo real de la imagen seleccionada
- **Recuperación de Contraseña (Olvidé mi contraseña)**
  - Nuevas columnas `reset_token` y `reset_expires` en tabla `usuarios`
  - Enlace "¿Olvidaste tu contraseña?" en página de login
  - Formulario para solicitar recuperación por email
  - Token único de 64 caracteres con 24 horas de validez
  - Protección anti-duplicados: máximo 1 email cada 30 segundos por dirección
  - Formulario para restablecer contraseña con validación
  - Envío de email usando configuración SMTP del sistema
  - Vista previa del enlace en modo desarrollo (cuando no hay SMTP)
  - Funciones en AuthController: `forgotPassword()`, `sendResetLink()`, `resetPassword()`, `doResetPassword()`
- **Bloqueo de Botones Durante Operaciones (Anti-Doble-Submit)**
  - Script JavaScript centralizado `form-loading.js`
  - Detecta automáticamente todos los formularios POST
  - Protección anti-duplicados con WeakSet y atributos data-
  - Captura en fase de bubbling para interceptar envíos temprano
  - Muestra spinner de Bootstrap durante operaciones
  - Cambia texto del botón a "Procesando..."
  - Compatible con confirmaciones (`onclick="return confirm(...)"`)
  - Funciona con enlaces de acción (eliminar, editar, etc.)
  - Se desbloquea automáticamente al volver con botón "Atrás"
  - Incluido en todas las páginas: sistema principal y autenticación

#### 🔒 Seguridad Implementada
- Password hashing con `password_hash()` (algoritmo bcrypt de PHP 8.1)
- Protección CSRF en todos los formularios con tokens
- Prepared Statements en todas las consultas PDO (anti SQL Injection)
- Sanitización de salida con `htmlspecialchars()`
- Roles de usuario con diferentes niveles de acceso
- Validaciones server-side en todos los formularios
- Soft delete en lugar de eliminación física
- No permite desactivar/auto-eliminarse a sí mismo
- Protección anti-duplicados: rate limiting de 30 segundos para recuperación de contraseña
- Tokens de recuperación con expiración de 24 horas

#### 🗄️ Base de Datos
- **Tablas creadas:**
  - `usuarios` - Gestión de usuarios del sistema
  - `comunidades` - Datos de condominios/comunidades
  - `propiedades` - Casas, departamentos, parcelas
  - `deudas` - Gastos comunes mensuales
  - `pagos` - Registro de pagos recibidos
  - `pagos_detalle` - Meses pagados en cada pago
  - `envios_correo` - Envíos masivos de correo
  - `envios_correo_detalle` - Detalle de envíos individuales
  - `configuracion_smtp` - Configuración SMTP por comunidad (nueva)
  - `vista_resumen_pagos` - Vista para reportes
- **Nuevas columnas:**
  - `usuarios.reset_token` - Token para recuperación de contraseña
  - `usuarios.reset_expires` - Fecha de expiración del token
  - `usuarios.comunidad_id` - Comunidad asignada (para admin/presidente)
- **Relaciones:**
  - Foreign keys con ON DELETE CASCADE donde corresponde
  - Índices optimizados en campos de búsqueda frecuente
- **Datos iniciales:**
  - Usuario admin por defecto
  - 2 comunidades de ejemplo
  - 4 propiedades de ejemplo
  - Deudas generadas para los últimos 6 meses

#### 📊 Estadísticas del Proyecto
- **Total de archivos PHP:** 51
- **Total de líneas de código:** ~7,707
- **Directorios:** 21
- **Tokens estimados:** ~70,000
- **Módulos implementados:** 9 (Auth, Usuarios, Comunidades, Propiedades, Pagos, Correos, Dashboard, Consolidados, Reportes)

#### 🛠️ Stack Tecnológico
- **Backend:** PHP 8.1
- **Base de Datos:** MySQL/MariaDB con PDO
- **Frontend:** HTML5, CSS3, Bootstrap 5.3.2
- **JavaScript:** Vanilla JS con AJAX
- **Gráficos:** Chart.js
- **Editor WYSIWYG:** TinyMCE 6
- **Generación PDF:** Dompdf 3.1.5
- **Iconos:** Bootstrap Icons
- **Arquitectura:** MVC simple
- **Servidor:** Apache 2.4 con mod_rewrite

#### 📁 Estructura del Proyecto
```
/var/www/ggcc/
├── app/
│   ├── controllers/      # 9 controladores
│   ├── models/          # 8 modelos + Model base
│   ├── helpers/         # MailerHelper (nuevo)
│   └── views/           # 25+ vistas organizadas por módulo
├── config/              # Configuración DB, autoload, utilidades
├── public/              # Punto de entrada + assets
│   └── assets/
│       ├── images/      # Logos e imágenes de fondo
│       └── js/          # Scripts JavaScript
│           └── form-loading.js  # Anti-doble-submit (nuevo)
├── vendor/              # Librerías Composer (Dompdf, etc.)
└── docs/                # Documentación (SMTP_CONFIG.md)
```

### 🐛 Bug Fixes
- Corrección de email: cambio de `fflores@opengato` a `fflores@opengato.cl`
- Actualización de hash de password tras problemas de login
- Creación de archivo faltante `propiedades/show.php`
- Eliminación de credenciales visibles en página de login
- **Corrección en Reporte de Egresos** - Cálculo correcto del Saldo: (pago gastos comunes + saldo mes anterior) - pago colaboradores
- **Corrección de parámetros duplicados en SQL** - Resuelto error "Invalid parameter number" en `ReportesController`
- **Corrección de token CSRF** - Agregado a vistas de autenticación independientes (`forgot-password.php`, `reset-password.php`)

### ✨ Nuevas Funcionalidades
- **Eliminar Registro de Caja en Saldos Mensuales**
  - Botón para eliminar registros históricos de caja (solo períodos abiertos)
  - Confirmación previa con detalle del período a eliminar
  - Protección CSRF en el formulario de eliminación
  - No permite eliminar períodos cerrados (deben reabrirse primero)

### 📚 Documentación
- `README.md` - Guía de instalación y uso
- `docs/SMTP_CONFIG.md` - Configuración SMTP detallada
- Comentarios en código para todas las clases y métodos

### 🚀 Próximas Mejoras (Roadmap)
- [x] **Generación de PDFs** - ✅ Implementado con Dompdf v3.1.5 (6 Mar 2026)
- [ ] Instalación de SwiftMailer para envío real de correos
- [ ] Exportación real a Excel (implementación con librerías)
- [ ] Sistema de notificaciones en tiempo real
- [ ] API REST para integración con apps móviles
- [ ] Sistema de backup automático de base de datos
- [ ] Logs de auditoría de todas las operaciones
- [ ] Panel de configuración general del sistema
- [ ] Multi-idioma (español, inglés)
- [ ] Tema oscuro/claro

---

## Notas de Versión

### Versión 1.0.0
- **Fecha de lanzamiento:** 6 de Marzo de 2026
- **Estado:** Estable y funcional
- **Ambiente:** 
  - ✅ Generación de PDFs con Dompdf v3.1.5
  - ⏳ Envío de correos requiere SwiftMailer (configuración SMTP lista)
- **Desarrollado por:** Claude Code (Anthropic)
- **Tiempo de desarrollo:** ~8 horas de trabajo continuo

---

## Contacto y Soporte

Para reportar bugs o solicitar nuevas funcionalidades, contacte al administrador del sistema.

**Sistema GGCC v1.0.0** - Sistema de Administración de Gastos Comunes de Condominios
