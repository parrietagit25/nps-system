# Sistema NPS con SendGrid, PHP y MySQL

Un sistema completo de encuestas NPS (Net Promoter Score) que permite crear, enviar y analizar encuestas de satisfacción del cliente utilizando SendGrid para el envío de emails.

## 🚀 Características

- **Creación de encuestas NPS** con preguntas personalizadas
- **Envío automático de emails** usando SendGrid
- **Panel de administración** completo
- **Análisis de resultados** con gráficos y estadísticas
- **Gestión de destinatarios** con tokens únicos
- **Interfaz moderna y responsive**
- **Sistema de autenticación** para administradores

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer
- Cuenta de SendGrid (API Key)

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone <repository-url>
cd nps
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar la base de datos
```bash
# Importar el esquema de la base de datos
mysql -u root -p < database/schema.sql
```

### 4. Configurar variables de entorno
Copiar el archivo de ejemplo y configurar las variables:

```bash
# Copiar el archivo de ejemplo
cp env.example .env

# Editar el archivo .env con tus credenciales
```

Editar el archivo `.env` con tus credenciales:

```env
# Configuración de Base de Datos
DB_HOST=localhost:3307
DB_NAME=nps_system
DB_USER=tu_usuario
DB_PASS=tu_password

# Configuración de SendGrid
SENDGRID_API_KEY=TU_SENDGRID_API_KEY
SENDGRID_FROM_EMAIL=nps@tudominio.com
SENDGRID_FROM_NAME=NPS System

# Configuración de la Aplicación
APP_URL=http://localhost/nps
APP_NAME=NPS System

# Configuración de Seguridad
APP_SECRET=tu-secret-key-aqui-cambiar-en-produccion
SESSION_SECRET=tu-session-secret-cambiar-en-produccion

# Configuración de Email
ADMIN_EMAIL=admin@nps.com
ADMIN_PASSWORD=password

# Configuración de Entorno
APP_ENV=development
DEBUG=true
```

### 5. Configurar el servidor web
Asegúrate de que el directorio del proyecto sea accesible desde tu servidor web (Apache/Nginx).

## 🔧 Configuración de SendGrid

1. Crea una cuenta en [SendGrid](https://sendgrid.com)
2. Genera una API Key desde el dashboard
3. Verifica tu dominio de envío
4. Actualiza la configuración en el archivo `.env`:
   - `SENDGRID_API_KEY`: Tu API key de SendGrid
   - `SENDGRID_FROM_EMAIL`: Email verificado para envío
   - `SENDGRID_FROM_NAME`: Nombre que aparecerá en los emails

## 📖 Uso

### Acceso al Panel de Administración

1. Navega a `http://localhost/nps/admin/`
2. Inicia sesión con las credenciales por defecto:
   - **Email:** admin@nps.com
   - **Password:** password

### Crear una Encuesta

1. Inicia sesión en el panel de administración
2. Haz clic en "Nueva Encuesta"
3. Completa los campos:
   - Título de la encuesta
   - Descripción
   - Pregunta NPS
   - Fechas de inicio y fin (opcional)
   - Estado (borrador/activa/inactiva)
4. Guarda la encuesta

### Agregar Destinatarios

1. Ve a la sección "Destinatarios"
2. Agrega los emails de los destinatarios
3. Los tokens únicos se generarán automáticamente

### Enviar Encuestas

1. Desde el panel de administración
2. Selecciona la encuesta
3. Haz clic en "Enviar Encuestas"
4. Los emails se enviarán automáticamente

### Ver Resultados

1. Accede a la sección "Resultados"
2. Selecciona la encuesta
3. Visualiza:
   - NPS Score
   - Distribución de respuestas
   - Comentarios de clientes
   - Estadísticas detalladas

## 📊 Estructura de la Base de Datos

### Tablas principales:

- **usuarios**: Administradores del sistema
- **encuestas**: Encuestas NPS creadas
- **destinatarios**: Lista de destinatarios por encuesta
- **respuestas**: Respuestas recibidas de los clientes
- **logs_email**: Registro de envíos de emails

## 🔒 Seguridad

- **Variables de entorno**: Todas las credenciales sensibles están en el archivo `.env`
- **Tokens únicos**: Para cada destinatario de encuesta
- **Validación de respuestas**: Protección contra respuestas duplicadas
- **Autenticación de administradores**: Sistema de login seguro
- **Sanitización de datos**: Limpieza de inputs del usuario
- **Archivo .gitignore**: El archivo `.env` está excluido del repositorio

### ⚠️ Importante para Producción

1. **Cambiar las claves secretas** en el archivo `.env`:
   - `APP_SECRET`: Generar una clave secreta única
   - `SESSION_SECRET`: Generar otra clave secreta única
   
2. **Configurar HTTPS** en producción
3. **Cambiar las credenciales por defecto** del administrador
4. **Configurar un dominio verificado** en SendGrid

## 📱 Características Técnicas

- **Frontend**: Bootstrap 5, Font Awesome
- **Backend**: PHP 7.4+, PDO para MySQL
- **Email**: SendGrid API
- **Responsive**: Diseño adaptativo
- **UX**: Interfaz moderna y intuitiva

## 🚀 Funcionalidades Avanzadas

- **Análisis en tiempo real** de respuestas
- **Exportación de datos** (próximamente)
- **Notificaciones automáticas** a administradores
- **Personalización de emails** con HTML
- **Seguimiento de envíos** con logs detallados

## 📈 Métricas NPS

El sistema calcula automáticamente:

- **NPS Score**: % Promotores - % Detractores
- **Distribución**: Detractores (0-6), Pasivos (7-8), Promotores (9-10)
- **Tasa de respuesta**: Porcentaje de respuestas recibidas
- **Promedio**: Puntuación media de las respuestas

## 🔧 Personalización

### Modificar plantillas de email
Edita los métodos en `includes/SendGridService.php`:
- `generarHTMLEncuesta()`
- `generarTextoEncuesta()`

### Agregar nuevos campos
1. Modifica el esquema de la base de datos
2. Actualiza las clases de servicio
3. Modifica los formularios correspondientes

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que MySQL esté ejecutándose

### Emails no se envían
- Verifica tu API Key de SendGrid
- Revisa los logs en `logs_email`
- Confirma que el dominio esté verificado en SendGrid

### Página no carga
- Verifica la configuración del servidor web
- Revisa los permisos de archivos
- Comprueba los logs de error de PHP

## 📞 Soporte

Para soporte técnico o preguntas:
- Revisa la documentación de SendGrid
- Consulta los logs del sistema
- Verifica la configuración de la base de datos

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más detalles.

---

**Desarrollado con ❤️ para mejorar la experiencia del cliente** 