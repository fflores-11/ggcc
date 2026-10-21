# Contexto de la Aplicación Sistema GGCC

## Descripción General
Sistema Web para Administración de Gastos Comunes de Condominios desarrollado en PHP 8.1 con arquitectura MVC. El sistema gestiona usuarios, comunidades, propiedades, pagos, reportes y envío de correos relacionados con la administración de condominios.

**Producción:** https://ggcc.opengato.cl

## Stack Tecnológico
- **PHP 8.1** o superior (con `mod_fcgid` + `suexec`, usuario `web110:client1`)
- **MySQL / MariaDB** como base de datos (`db-tx01.opengato.cl` / base `c1ggcc`)
- **Bootstrap 5** + **Bootstrap Icons** para la interfaz de usuario
- **JavaScript** con AJAX para funcionalidades asíncronas
- **TinyMCE** como editor WYSIWYG
- **Dompdf** para generación de recibos PDF

## Estructura del Proyecto
```
/var/www/clients/client1/web110/web/
├── app/                     # Aplicación principal (MVC)
│   ├── controllers/         # Controladores (9 archivos)
│   ├── models/              # Modelos con PDO (9 archivos)
│   └── views/               # Vistas con Bootstrap (25+ archivos)
├── config/                  # Configuración del sistema
│   ├── config.php           # Configuración general (define ROOT_PATH, PUBLIC_PATH, VIEWS_PATH)
│   ├── database.php         # Conexión PDO (clase Database singleton)
│   └── scripts de migración SQL
├── public/                  # Punto de entrada web (DocumentRoot)
│   ├── index.php            # Entrypoint principal
│   ├── .htaccess            # Seguridad: bloquea .git y .md
│   ├── assets/images/pagos_colaboradores/  # Imágenes de boletas/recibos
│   └── *.php                # Módulos del sistema
├── docs/                    # Documentación adicional
├── stats/                   # Estadísticas y reportes
├── backup_*.sql             # Respaldos de base de datos
├── composer.json / composer.lock # Dependencias
├── LICENSE                  # Licencia del software
├── CHANGELOG.md             # Registro de cambios
├── README.md                # Documentación general
└── context.md               # Este archivo (contexto de la aplicación)
```

## Módulos del Sistema
1. **Dashboard** - Métricas en tiempo real, gráficos, propiedades morosas, últimas actividades
2. **Mantenedores** - CRUD de usuarios, gestión de comunidades y propiedades
3. **Pagos** - Registro de pagos, generación de deudas, recibos automáticos, historial
4. **Colaboradores y Pagos a Colaboradores** - CRUD de colaboradores, registro de pagos con hasta 4 imágenes adjuntas
5. **Envío de Correos** - Envío masivo, cobranzas personalizadas, editor WYSIWYG, variables dinámicas
6. **Consolidados** - Matriz de pagos por mes, filtros por comunidad/año, exportación a Excel
7. **Reportes** - Morosidad, pagos por período, deudas por período
8. **Saldos Mensuales** - Control de caja por comunidad/mes

## Seguridad Implementada
- ✅ Password hashing con `password_hash()` (PHP 8.1)
- ✅ Protección CSRF en todos los formularios
- ✅ Prepared Statements (PDO) con `ATTR_EMULATE_PREPARES => false`
- ✅ Sanitización de inputs
- ✅ Sesiones seguras
- ✅ Roles de usuario (admin / administrador / presidente / propietario)
- ✅ `.htaccess` en `public/` bloquea acceso web a `.git/` y archivos `.md` (403 Forbidden)
- ✅ `open_basedir` activo: solo se permiten rutas dentro del webroot (`PUBLIC_PATH`)

---

## 📎 Sistema de Imágenes en Pagos a Colaboradores

### Modelo de datos (2 ubicaciones)

| Ubicación | Campo | Descripción |
|---|---|---|
| Tabla `pagos_colaboradores` | `imagen_path` VARCHAR(255) | Imagen **principal** (sistema legado) |
| Tabla `pagos_colaborador_imagenes` | `ruta_imagen` VARCHAR(255) | Imágenes **adicionales** (FK con `ON DELETE CASCADE`) |

**Tabla `pagos_colaborador_imagenes`:**
```sql
CREATE TABLE `pagos_colaborador_imagenes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pago_colaborador_id` int NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pago_colaborador_id` (`pago_colaborador_id`),
  CONSTRAINT `pagos_colaborador_imagenes_ibfk_1`
    FOREIGN KEY (`pago_colaborador_id`) REFERENCES `pagos_colaboradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Formulario: 4 inputs separados
Los formularios de creación y edición usan **4 inputs de archivo individuales** (no `multiple`):
```html
<input type="file" name="imagen_1" accept="image/jpeg,image/png,image/gif,application/pdf">
<input type="file" name="imagen_2" accept="image/jpeg,image/png,image/gif,application/pdf">
<input type="file" name="imagen_3" accept="image/jpeg,image/png,image/gif,application/pdf">
<input type="file" name="imagen_4" accept="image/jpeg,image/png,image/gif,application/pdf">
```
El usuario puede subir **1, 2, 3 o 4** imágenes (dejar los demás vacíos es válido).

### Validaciones (simplificadas)
Solo se valida la **extensión** del archivo:
```php
$extensionesValidas = ['jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff', 'pdf'];
```
> ⚠️ **No hay límite de tamaño** en la validación de la aplicación (solo el límite de PHP `upload_max_filesize`).

### Almacenamiento
- **Directorio físico:** `public/assets/images/pagos_colaboradores/`
- **Ruta en BD:** relativa, formato `assets/images/pagos_colaboradores/pago_{time}_{uniqid}.{ext}`
- Se usa `PUBLIC_PATH` para respetar `open_basedir` (NO usar rutas con `../../`)

### Métodos del modelo (`app/models/PagoColaborador.php`)
| Método | Descripción |
|---|---|
| `getImagenes(int $pagoId): array` | Obtiene todas las imágenes adicionales de un pago |
| `subirImagenesIndividuales(int $pagoId, array $files): array` | Sube N archivos individuales (usado por controlador) |
| `subirImagenes(int $pagoId, array $archivos): array` | Sube archivos en formato array (`$_FILES` múltiple) |
| `eliminarImagen(int $imagenId): bool` | Elimina una imagen específica (archivo + registro) |
| `eliminarTodasImagenes(int $pagoId): int` | Elimina todos los registros de imágenes de un pago |
| `getTotalPagado`, `getResumenPeriodo`, `getAllWithDetailsPaginated`, etc. | Consultas de negocio |

### Consultas con contador de imágenes
Las consultas de listados incluyen un subquery que permite mostrar el ícono de imagen:
```sql
(SELECT COUNT(*) FROM pagos_colaborador_imagenes pci
 WHERE pci.pago_colaborador_id = pc.id) as total_imagenes
```
En las vistas la condición es:
```php
<?php if (!empty($pago['imagen_path']) || !empty($pago['total_imagenes'])): ?>
```

### Acciones del controlador (`app/controllers/ColaboradoresController.php`)
| Acción | Descripción |
|---|---|
| `createPago` / `storePago` | Crea pago + sube hasta 4 imágenes |
| `editPago` / `updatePago` | Actualiza pago (las imágenes nuevas **reemplazan** a las anteriores) |
| `verImagen` | Muestra imagen principal + grid de imágenes adicionales (con botones descargar/eliminar) |
| `deletePago` | Elimina pago + imagen principal + imágenes adicionales (archivos y registros) |
| `eliminarImagen` | Elimina imagen (con `imagen_id` = una específica; sin él = todas) |

---

## 🐛 Errores Comunes y Cómo Evitarlos

| Error | Causa | Solución |
|---|---|---|
| `Call to undefined method ...subirImagenesIndividuales()` | Método faltante en el modelo | Verificar que el modelo tenga el método |
| `open_basedir restriction in effect` | Ruta con `../../` o fuera del webroot | Usar siempre `PUBLIC_PATH . '/' . $rutaRelativa` |
| Imagen no visible tras subirla | Guardada fuera de `public/` o ruta absoluta en BD | Guardar **ruta relativa** (`assets/images/...`) |
| Registros huérfanos en `pagos_colaborador_imagenes` | Se borraba el archivo pero no el registro | Usar `eliminarTodasImagenes()` / `eliminarImagen()` |
| Ícono de imagen no aparece en el listado | Falta el subquery `total_imagenes` | Incluirlo en el SELECT de los listados |

---

## Variables de Correo Disponibles
- `{nombre_propiedad}`
- `{nombre_dueno}`
- `{monto_deuda}`
- `{mes}` / `{anio}`
- `{comunidad}`
- `{direccion}`
- `{presidente}`

## Endpoints API (AJAX)
- `GET /propiedades.php?action=api-list&comunidad_id=X`
- `GET /pagos.php?action=api-deudas&propiedad_id=X`
- `POST /correos.php?action=preview`

## Acciones de Colaboradores (URLs)
```
colaboradores.php?action=index              # Listado de colaboradores
colaboradores.php?action=create             # Nuevo colaborador
colaboradores.php?action=edit&id=X          # Editar colaborador
colaboradores.php?action=show&id=X          # Detalle + pagos del colaborador
colaboradores.php?action=pagos              # Listado de pagos a colaboradores
colaboradores.php?action=createPago&colaborador_id=X   # Nuevo pago
colaboradores.php?action=editPago&id=X      # Editar pago (subir imágenes)
colaboradores.php?action=verImagen&id=X     # Ver imágenes del pago
colaboradores.php?action=generarReciboPDF&id=X&token=HASH  # Recibo PDF
colaboradores.php?action=deletePago&id=X    # Eliminar pago
```

**Token del recibo PDF:** `hash('sha256', 'recibo_' . $id . '_ggcc_2024')`

## Estadísticas del Proyecto
- **Total de archivos:** 51 PHP + 1 SQL
- **Líneas de código:** ~7,707
- **Tokens estimados:** ~70,000

## Base de Datos
- **Host:** `db-tx01.opengato.cl`
- **Base:** `c1ggcc`
- **Usuario:** `c1ggcc` (password en `config/database.php`)
- **Tablas principales:** `usuarios`, `comunidades`, `propiedades`, `pagos`, `pagos_detalle`, `colaboradores`, `pagos_colaboradores`, `pagos_colaborador_imagenes`, `saldos_mensuales`, `configuracion`

## Configuración del Servidor Web
El directorio `public/` debe ser el root del sitio web. Configuración Apache (ISPConfig):
```apache
DocumentRoot /var/www/clients/client1/web110/web/public
<Directory /var/www/clients/client1/web110/web/public>
    AllowOverride All
    Require all granted
</Directory>
```

### Permisos de archivos subidos
```bash
chmod 0755 public/assets/images/pagos_colaboradores/
chmod 0644 public/assets/images/pagos_colaboradores/*
```

---
**Versión:** 1.1.0  
**Última actualización:** Octubre 2026  
**Cambios recientes:** Sistema de hasta 4 imágenes por pago a colaboradores, `.htaccess` de seguridad, documento de contexto.
