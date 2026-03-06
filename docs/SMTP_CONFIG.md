# Configuración SMTP por Comunidad

## Resumen

El sistema ahora permite configurar servidores SMTP individuales para cada comunidad. Solo los super usuarios (rol: admin) pueden configurar el SMTP.

## Instalación de SwiftMailer

Para que el envío de correos funcione, debes instalar SwiftMailer via Composer:

```bash
cd /var/www/ggcc
composer require swiftmailer/swiftmailer
```

Si no tienes Composer instalado:
```bash
# Descargar Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Instalar SwiftMailer
php composer.phar require swiftmailer/swiftmailer
```

## Características Implementadas

### 1. Configuración SMTP por Comunidad
- Cada comunidad puede tener su propio servidor SMTP
- Configuración independiente de host, puerto, usuario, contraseña
- Email y nombre del remitente personalizado por comunidad

### 2. Restricción de Acceso
- Solo usuarios con rol **admin** pueden configurar SMTP
- El menú "Configuración SMTP" solo aparece para admins
- Intentos de acceso no autorizados son redirigidos al dashboard

### 3. Validación antes de Enviar
- Antes de enviar cualquier correo, se verifica que exista configuración SMTP
- Si no hay configuración, se muestra error: "No hay configuración SMTP para esta comunidad. Contacte al administrador."

### 4. Interfaz de Configuración
- Formulario para agregar/editar configuración SMTP
- Lista de comunidades con y sin configuración
- Botón para probar conexión SMTP
- Guía de configuración para Gmail, Outlook, cPanel

## Estructura de Archivos

```
app/
├── models/
│   └── ConfiguracionSMTP.php       # Modelo para gestionar config SMTP
├── controllers/
│   └── ConfiguracionSMTPController.php  # Controlador (solo admin)
│   └── CorreosController.php         # Modificado para verificar SMTP
├── helpers/
│   └── MailerHelper.php              # Helper para envío usando config
├── views/
│   └── configuracion_smtp/
│       ├── index.php                 # Listado de configuraciones
│       └── form.php                  # Formulario de configuración
└── views/partials/
    └── header.php                    # Menú con opción SMTP (solo admin)

public/
└── configuracion_smtp.php           # Punto de entrada

database:
└── configuracion_smtp              # Tabla nueva
```

## Campos de Configuración SMTP

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| **Host** | Servidor SMTP | smtp.gmail.com |
| **Puerto** | Puerto de conexión | 587 (TLS) o 465 (SSL) |
| **Encriptación** | Tipo de seguridad | TLS, SSL o ninguna |
| **Usuario** | Usuario SMTP | tuemail@gmail.com |
| **Contraseña** | Password o App Password | tu_password |
| **Email Remitente** | Email que aparece como remitente | noreply@condominio.cl |
| **Nombre Remitente** | Nombre del remitente | Administración Condominio |

## Seguridad

- Las contraseñas SMTP se almacenan en la base de datos (en producción, considerar encriptación adicional)
- Solo accesible para usuarios con rol 'admin'
- Verificación de CSRF en todos los formularios

## Próximos Pasos

1. Instalar SwiftMailer con Composer
2. Ingresar al sistema como admin
3. Ir a: Administración → Configuración SMTP
4. Configurar SMTP para cada comunidad
5. Probar envío de correos

## Nota Importante

Sin instalar SwiftMailer, el sistema funcionará en modo "simulado" (los correos se registran pero no se envían realmente). Para envío real de correos, es obligatorio instalar la librería.
