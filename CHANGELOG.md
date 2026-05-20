# Changelog - Sistema GGCC

Todos los cambios notables en el proyecto Sistema de Gestión de Gastos Comunes (GGCC) serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).

## [Unreleased] - 2026-03-29

### ✨ Nuevas Funcionalidades

#### Paginación en Mantenedores
- **Paginación automática en todos los listados**
  - 20 registros por página (configurable)
  - Botones "Anterior" y "Siguiente" con iconos
  - Navegación numérica con rangos (muestra 5 páginas alrededor de la actual)
  - Elipsis (...) para páginas lejanas
  - Información de registros mostrados: "Mostrando X - Y de Z registros"
  - Preserva otros parámetros GET al navegar
  - Diseño con Bootstrap 5 y Bootstrap Icons
  - Solo se muestra si hay más de 20 registros
  - **Módulos con paginación:**
    - Comunidades
    - Propiedades
    - Usuarios
    - Colaboradores
    - Pagos a colaboradores
    - Pagos de propiedades
    - Usuarios por propiedad
    - Envíos de correo
  - Archivo helper creado: `app/helpers/PaginationHelper.php`

#### Sistema de Pagos a Colaboradores - Mejoras Completas
- **Campo para imagen de boleta/recibo**
  - Nueva columna `imagen_path` en tabla `pagos_colaboradores`
  - Soporte para formatos: JPEG, PNG, GIF, PDF
  - Tamaño máximo: 5MB
  - Subida opcional al registrar pago
  - Visualización de indicador "Sí/No" en listado
  - Vista previa de boleta en detalle del pago
  - Eliminación de imagen al borrar el pago
  - Reemplazo de imagen al editar pago
  - Archivo de migración: `config/migration_pagos_colaboradores_imagen.sql`

- **Botones de acción en listado de pagos**
  - Botón "Editar" - Permite modificar todos los datos del pago
  - Botón "PDF" - Descarga el recibo en PDF
  - Botón "Ver Boleta" - Visualiza la imagen adjunta (si existe)
  - Botón "Ver Colaborador" - Navega al detalle del colaborador
  - Botón "Eliminar" - Elimina el pago con confirmación
  - Todos los botones en un grupo compacto

- **Recibo PDF profesional**
  - Número de recibo único (RCB-XXXXXX-YYYY)
  - Datos completos del colaborador (nombre, email, teléfono)
  - Detalle del pago y monto formateado
  - Tipo de colaborador (Personal/Empresa)
  - Código QR en esquina superior izquierda
  - Firma digital del administrador (si está configurada)
  - Espacio para firma del colaborador
  - Todo en una sola página A4
  - Generado con DomPDF v3.1.5

- **Código QR con acceso directo**
  - QR apunta directamente al PDF del recibo
  - Token de seguridad incluido en la URL
  - Permite descargar el PDF sin iniciar sesión
  - API gratuita: qrserver.com
  - Tamaño: 120x120 píxeles
  - Acceso público mediante token hash SHA256

- **Vistas y controladores actualizados**
  - `ColaboradoresController::editPago()` - Formulario de edición
  - `ColaboradoresController::updatePago()` - Procesar actualización
  - `ColaboradoresController::generarReciboPDF()` - Generar PDF
  - `app/views/colaboradores/edit_pago.php` - Nueva vista de edición
  - `app/views/colaboradores/pagos.php` - Botones de acción agregados
  - `app/views/colaboradores/show.php` - Indicador de boleta en historial

#### Lógica de Ingresos Unificada - Registro por Fecha de Pago
- **Principio fundamental: Los ingresos se registran en el mes que se reciben**
  - Todos los pagos recibidos en un mes se suman como ingresos de ese mes
  - Independientemente de qué meses de deuda se estén pagando
  - Ejemplo: Si en marzo se reciben 40.000 de marzo + 60.000 de deudas de enero = Total ingresos de marzo: 100.000

- **Unificación de cálculos en Dashboard y Reportes**
  - DashboardController ahora usa el mismo método que ReportesController para obtener saldo anterior
  - Ambos calculan ingresos con: `SELECT SUM(monto) FROM pagos WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio`
  - Fórmula unificada: `saldo_mes_anterior + ingresos_mes_actual - egresos_mes_actual`
  - Elimina inconsistencias entre Dashboard y Reportes

- **Diferenciación clara entre módulos**
  - **Dashboard y Reportes**: Muestran ingresos por fecha de pago (cuándo entró el dinero)
  - **Consolidado (Matriz)**: Muestra en qué mes se pagó cada deuda (histórico de pagos por período)
  - **Saldo Mensual**: Acumulación mensual del saldo con cierre de períodos

- **Archivos modificados**
  - `app/controllers/DashboardController.php` - Unificado cálculo de ingresos y saldo anterior
  - `app/controllers/ReportesController.php` - Misma lógica verificada y confirmada
  - `app/models/Dashboard.php` - Estadísticas mensuales con misma consulta SQL
  - `app/models/SaldoMensual.php` - Cálculo de ingresos con misma lógica

#### Correcciones en Formulario de Colaboradores
- **Formulario de edición funcional**
  - Corrección del campo "Nombre Completo" (faltaba atributo `required`)
  - Eliminación de dependencia excesiva de JavaScript
  - Campos se muestran/ocultan usando PHP puro
  - Botones de tipo (Personal/Empresa) recargan página con tipo seleccionado
  - Funcionamiento estable en todos los navegadores

### 🔧 Mejoras Técnicas
- Modelo `PagoColaborador` extendido con métodos:
  - `getWithImagen()` - Obtener pago con datos de imagen
  - `updateImagenPath()` - Actualizar ruta de imagen
  - `getImagenPath()` - Obtener ruta de imagen
  - `deleteImagen()` - Eliminar referencia de imagen
  - `getAllWithDetailsPaginated()` - Listado paginado con detalles
  - `countPagos()` - Contar total de pagos

### 🗄️ Migraciones de Base de Datos
- **Nueva migración:** `config/migration_pagos_colaboradores_imagen.sql`
  - Agrega columna `imagen_path VARCHAR(255)` a tabla `pagos_colaboradores`
  - Crea índice para búsquedas rápidas

### 📊 Estadísticas Actualizadas
- **Total de archivos PHP:** 58
- **Total de líneas de código:** ~9,500
- **Archivos nuevos:** 3 (PaginationHelper, migration, vista edit_pago)
- **Módulos mejorados:** Pagos a Colaboradores

### Versión [Unreleased] - 2026-03-15

### ✨ Nuevas Funcionalidades

#### Gestión de Mascotas
- **Nuevo módulo de mascotas para propiedades**
  - Campos: Nombre, Tipo (Gato/Perro/Ave/Hamster), Edad, Alimento, Imagen
  - Una propiedad puede tener múltiples mascotas
  - Imágenes de 200x200 píxeles con formato JPG, PNG, GIF (máx 2MB)
  - CRUD completo: Agregar, editar, eliminar mascotas
  - Visualización en tarjetas con foto y datos de la mascota
  - Cambio de imagen al editar (reemplaza la anterior automáticamente)

- **Acceso multi-rol:**
  - **Propietarios**: Pueden gestionar mascotas desde "Mi Perfil"
  - **Admin/Super-admin**: Pueden gestionar mascotas desde el detalle de cualquier propiedad

- **Base de datos - Nueva tabla `mascotas`:**
  - `id`, `propiedad_id`, `nombre`, `tipo` (ENUM), `edad`, `alimento`, `imagen_path`
  - Soft delete con campo `activo`
  - Índices optimizados por propiedad y tipo

#### Mejoras en Usuarios de Propiedad
- **Email opcional en creación de usuarios propietarios**
  - Campo de email ya no es obligatorio al crear usuarios de propiedad
  - Validación condicional: solo valida formato si se proporciona email
  - Aplicado en: creación, edición por admin, y edición de perfil por propietario

- **Propietarios pueden editar datos de su propiedad**
  - Nueva sección "Editar Datos de la Propiedad" en el perfil del propietario
  - Campos editables: Nombre del dueño, Email del dueño, WhatsApp del dueño
  - Campos editables: Nombre del agente, Email del agente, WhatsApp del agente
  - Campos bloqueados (solo admin): Nombre de propiedad, Comunidad, Gastos comunes
  - Mensaje informativo indicando campos no editables

#### Vistas Actualizadas
- `app/views/usuarios_propiedad/perfil.php`: Nueva sección de mascotas y edición de propiedad
- `app/views/propiedades/show.php`: Nueva sección de mascotas para administradores
- `app/views/usuarios_propiedad/form.php`: Email sin asterisco de obligatorio

#### Archivos Creados/Modificados
**Nuevos archivos:**
- `app/models/Mascota.php`: Modelo completo CRUD para mascotas
- `config/migration_mascotas.sql`: Migración de base de datos

**Archivos modificados:**
- `app/controllers/UsuariosPropiedadController.php`: Métodos CRUD para mascotas y actualización de propiedad
- `app/controllers/PropiedadesController.php`: Gestión de mascotas para admins
- `app/models/Usuario.php`: Campos de agente agregados a consultas
- `app/models/Propiedad.php`: Método `updateByPropietario()` para campos permitidos
- `public/perfil.php`: Nuevas rutas para mascotas
- `public/propiedades.php`: Nuevas rutas para gestión de mascotas por admin

---

## [1.1.0] - 2026-03-15

### 🎉 Sistema de Usuarios por Propiedad - Implementación Completa

#### ✨ Nuevas Funcionalidades

##### Sistema de Usuarios por Propiedad
- **Nuevo rol de usuario: "propietario"**
  - Acceso limitado solo a datos de su propiedad específica
  - Permite visualizar reportes personales y consolidado de su propiedad
  - Puede editar su perfil (email, whatsapp, contraseña)
  - No puede acceder a funciones administrativas

- **Gestión de usuarios por propiedad (Admin/Administrador)**
  - Nuevo módulo: "Usuarios por Propiedad" en menú de mantenedores
  - Crear usuarios asociados a propiedades específicas
  - Nombre de usuario generado automáticamente desde nombre de propiedad (formato slug)
    - Ejemplo: "Casa 9" → usuario: `casa-9`
  - Contraseña generada automáticamente de 10 caracteres
  - Campos: Comunidad, Propiedad, Usuario, Email, WhatsApp, Contraseña
  - Validación: solo una cuenta por propiedad
  - Edición limitada: solo email, whatsapp y contraseña son editables
  - Eliminación lógica (soft delete): desactiva usuario sin borrar datos
  - Generar nueva contraseña para usuarios existentes

- **Base de datos - Nuevas columnas en tabla `usuarios`**
  - `propiedad_id` (INT, FK a propiedades): Propiedad asociada al usuario
  - `whatsapp` (VARCHAR 20): Número de contacto
  - `es_propietario` (TINYINT): Flag para identificar usuarios de propiedad
  - `rol` actualizado: ENUM ahora incluye 'propietario'
  - Índices optimizados para búsquedas por propiedad

- **Vistas y Controladores**
  - `UsuariosPropiedadController.php`: Controlador completo CRUD
  - `usuarios_propiedad/index.php`: Listado de usuarios por propiedad
  - `usuarios_propiedad/form.php`: Formulario crear/editar con generación automática de usuario
  - `usuarios_propiedad/perfil.php`: Perfil del propietario (editable)
  - AJAX dinámico para cargar propiedades según comunidad seleccionada

##### Autenticación Mejorada
- **Login con Email o Usuario**
  - Los usuarios pueden iniciar sesión con email O nombre de usuario
  - Label actualizado: "Email o Usuario"
  - Compatibilidad con usuarios existentes (admin, administrador, presidente)

##### Restricciones de Acceso para Propietarios
- **Dashboard limitado para propietarios:**
  - ❌ Oculto: Accesos Rápidos
  - ❌ Oculto: Últimas Actividades
  - ❌ Oculto: Comunidades con Mayor Deuda
  - ❌ Oculto: Resumen por Comunidad
  - ✅ Visible: Métricas generales y gráfico de tendencias
  - ✅ Visible: Propiedades morosas (solo si aplica a su propiedad)

- **Menú de navegación adaptativo**
  - Propietarios ven: Dashboard, Mi Perfil, Mis Reportes, Mi Consolidado
  - Admin/Administrador ven: Todos los módulos + "Usuarios por Propiedad"

##### Reportes Filtrados por Propiedad
- **Reportes de propietarios limitados a su propiedad:**
  - Morosidad: Solo ve su estado de deuda
  - Pagos: Solo sus pagos realizados
  - Deudas: Solo sus deudas pendientes/pagadas
  - Egresos: No disponible (información administrativa)

##### Consolidado Personal
- **Consolidado filtrado por propiedad**
  - Propietarios solo ven su fila en la matriz de pagos
  - Acceso al histórico completo de su propiedad

#### 🔒 Seguridad Implementada
- Validación: propietarios no pueden ver datos de otras propiedades
- Verificación de permisos en cada acción del controlador
- Filtro SQL automático por propiedad para usuarios con rol 'propietario'
- CSRF tokens en todos los formularios
- Soft delete: usuarios desactivados no aparecen en listado pero mantienen histórico

#### 🗄️ Migración de Base de Datos
- Archivo: `config/migracion_usuarios_propiedad.sql`
- Agrega columnas necesarias a tabla `usuarios`
- Crea índices optimizados
- Vista `vista_usuarios_propietarios` para consultas

#### 🛠️ Archivos Creados/Modificados
**Nuevos archivos:**
- `app/controllers/UsuariosPropiedadController.php`
- `app/views/usuarios_propiedad/index.php`
- `app/views/usuarios_propiedad/form.php`
- `app/views/usuarios_propiedad/perfil.php`
- `public/usuarios_propiedad.php`
- `public/perfil.php`
- `config/migracion_usuarios_propiedad.sql`

**Archivos modificados:**
- `app/models/Usuario.php`: Nuevos métodos para gestión de propietarios
- `app/controllers/ReportesController.php`: Filtros por propiedad
- `app/controllers/ConsolidadosController.php`: Filtros por propiedad
- `app/controllers/AuthController.php`: Login con email o usuario
- `app/views/dashboard/index.php`: Secciones ocultas para propietarios
- `app/views/partials/header.php`: Menú adaptativo por rol
- `app/views/auth/login.php`: Label "Email o Usuario"
- `config/config.php`: Nueva función `getUserPropiedadId()`
- `config/database.sql`: Esquema actualizado

---

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
- **Total de archivos PHP:** 53
- **Total de líneas de código:** ~8,500
- **Directorios:** 22
- **Tokens estimados:** ~80,000
- **Módulos implementados:** 10 (Auth, Usuarios, Comunidades, Propiedades, Pagos, Correos, Dashboard, Consolidados, Reportes, Mascotas)

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
│       ├── images/      # Logos, imágenes de fondo y mascotas
│       │   └── mascotas/# Imágenes de mascotas (200x200px)
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
- **Firma Digital en Recibos PDF**
  - Nuevo campo `firma_path` en tabla `usuarios` para almacenar ruta de firma
  - Subida de imagen de firma desde perfil de usuario (formatos: PNG, JPG, GIF, WEBP)
  - Validación de tamaño máximo (2MB) y dimensiones (máximo 800x400px)
  - Visualización de firma actual con opción de eliminar
  - Integración en generación de PDF: firma aparece sobre la línea "Firma y Sello"
  - Uso de URL completa para compatibilidad con Dompdf
  - Tamaño de firma optimizado: 400px de ancho x 150px de alto máximo
  - Posicionamiento ajustado para mantener todo en una sola página
  - Solo usuarios con rol admin o administrador pueden gestionar firmas
  - Directorio de almacenamiento: `/public/assets/images/firmas/`
  - Métodos en Usuario model: `getFirmaPath()`, `updateFirmaPath()`, `firmaExists()`, `getFirmaUrl()`
  - Controlador actualizado: `UsuariosController::subirFirma()`, `UsuariosController::eliminarFirma()`
  - Vista de formulario: sección de firma con preview y controles
  - Rutas: `POST /usuarios/subir-firma`, `POST /usuarios/eliminar-firma`

### 📚 Documentación
- `README.md` - Guía de instalación y uso
- `docs/SMTP_CONFIG.md` - Configuración SMTP detallada
- Comentarios en código para todas las clases y métodos

### 🚀 Próximas Mejoras (Roadmap)
- [x] **Generación de PDFs** - ✅ Implementado con Dompdf v3.1.5 (6 Mar 2026)
- [x] **Sistema de Mascotas** - ✅ Implementado (15 Mar 2026)
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

### Versión [Unreleased]
- **Fecha:** 15 de Marzo de 2026
- **Estado:** En desarrollo
- **Nuevas funcionalidades principales:**
  - ✅ Módulo de Mascotas completo (CRUD + imágenes)
  - ✅ Email opcional para usuarios de propiedad
  - ✅ Propietarios pueden editar datos de su propiedad
- **Desarrollado por:** Claude Code (Anthropic)
- **Tiempo de desarrollo:** ~1 hora de trabajo continuo

### Versión 1.1.0
- **Fecha de lanzamiento:** 15 de Marzo de 2026
- **Estado:** Estable y funcional
- **Nuevas funcionalidades principales:**
  - ✅ Sistema de Usuarios por Propiedad completo
  - ✅ Login con Email o Nombre de Usuario
  - ✅ Dashboard adaptativo según rol de usuario
  - ✅ Reportes y consolidados filtrados por propiedad
- **Desarrollado por:** Claude Code (Anthropic)
- **Tiempo de desarrollo:** ~3 horas de trabajo continuo

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

**Sistema GGCC v1.2.0-dev** - Sistema de Administración de Gastos Comunes de Condominios
