# Sistema GGCC - Administración de Gastos Comunes

## Sistema Web para Administración de Gastos Comunes de Condominios

**Stack Tecnológico:**
- PHP 8.1
- MySQL / MariaDB
- Bootstrap 5
- JavaScript (AJAX)
- TinyMCE (Editor WYSIWYG)

---

## Estructura del Proyecto

```
/var/www/ggcc/
├── app/
│   ├── controllers/        # Controladores MVC (9 archivos)
│   ├── models/              # Modelos con PDO (8 archivos)
│   └── views/               # Vistas con Bootstrap (25+ archivos)
├── config/                  # Configuración
├── public/                  # Punto de entrada
└── README.md
```

---

## Instalación

### 1. Requisitos
- PHP 8.1 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Servidor Apache con mod_rewrite
- Extensiones PHP: PDO, PDO_MySQL

### 2. Configuración de Base de Datos

```sql
-- Importar el script SQL
mysql -u root -p < config/database.sql
```

O crear la base de datos manualmente:
```sql
CREATE DATABASE condominios_db CHARACTER SET utf8mb4;
```

### 3. Configuración del Sistema

Editar `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'condominios_db');
define('DB_USER', 'root');
define('DB_PASS', 'tu_password');
```

### 4. Configuración de Apache

Asegúrate de que el directorio `public/` sea el root del sitio web.

Ejemplo de configuración VirtualHost:
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/ggcc/public
    <Directory /var/www/ggcc/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Acceso

URL: `http://localhost/`

**Usuario por defecto:**
- Email: admin@condominios.cl
- Password: password

---

## Módulos del Sistema

### 1. Dashboard
- Métricas en tiempo real
- Gráficos de tendencias
- Propiedades morosas
- Últimas actividades

### 2. Mantenedores
- **Usuarios:** CRUD completo con roles
- **Comunidades:** Gestión de condominios
- **Propiedades:** Casas, departamentos, parcelas

### 3. Pagos
- Registro de pagos
- Generación de deudas mensuales
- Recibos automáticos
- Historial de pagos

### 4. Envío de Correos
- Envío masivo general
- Cobranzas personalizadas
- Editor WYSIWYG
- Variables dinámicas

### 5. Consolidados
- Matriz de pagos por mes
- Filtros por comunidad/año
- Exportación a Excel

### 6. Reportes
- Morosidad
- Pagos por período
- Deudas por período

---

## Características de Seguridad

- ✅ Password hashing con `password_hash()` (PHP 8.1)
- ✅ Protección CSRF en todos los formularios
- ✅ Prepared Statements (PDO)
- ✅ Sanitización de inputs
- ✅ Sesiones seguras
- ✅ Roles de usuario (admin/administrador/presidente)

---

## Roles de Usuario

| Rol | Permisos |
|-----|----------|
| **Admin** | Control total del sistema |
| **Administrador** | Gestión operativa (pagos, correos, reportes) |
| **Presidente** | Visualización de reportes de su comunidad |

---

## Variables de Correo

Para personalizar mensajes, usar:
- `{nombre_propiedad}`
- `{nombre_dueno}`
- `{monto_deuda}`
- `{mes}` / `{anio}`
- `{comunidad}`
- `{direccion}`
- `{presidente}`

---

## API Endpoints (AJAX)

- `GET /propiedades.php?action=api-list&comunidad_id=X`
- `GET /pagos.php?action=api-deudas&propiedad_id=X`
- `POST /correos.php?action=preview`

---

## Estadísticas del Proyecto

- **Total de archivos:** 51 PHP + 1 SQL
- **Líneas de código:** ~7,707
- **Tokens estimados:** ~70,000

---

## Soporte

Para soporte o reportes de bugs, contacte al administrador del sistema.

---

**Versión:** 1.0.0  
**Fecha de creación:** Marzo 2025
