# 🐳 Dockerización del Sistema NPS

Este directorio contiene todos los archivos necesarios para dockerizar y desplegar el Sistema NPS en tu servidor de Amazon.

## 📁 Estructura de Archivos

- `Dockerfile` - Configuración principal del contenedor
- `docker-compose.yml` - Configuración para desarrollo
- `docker-compose.prod.yml` - Configuración para producción
- `apache.conf` - Configuración de Apache
- `entrypoint.sh` - Script de inicialización
- `.dockerignore` - Archivos a excluir del build

## 🚀 Despliegue Rápido

### 1. Desarrollo Local
```bash
# Construir y ejecutar
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down
```

### 2. Producción en Amazon
```bash
# Usar configuración de producción
docker-compose -f docker-compose.prod.yml up -d

# Ver logs
docker-compose -f docker-compose.prod.yml logs -f
```

## 🔧 Configuración de Producción

### Variables de Entorno
Crea un archivo `.env` en el servidor con:

```env
# Base de Datos
DB_USER=nps_user
DB_PASS=tu_password_seguro
MYSQL_ROOT_PASSWORD=root_password_seguro

# SendGrid
SENDGRID_API_KEY=tu_api_key_de_sendgrid
SENDGRID_FROM_EMAIL=nps@tudominio.com
SENDGRID_FROM_NAME=NPS System

# Aplicación
APP_URL=https://tudominio.com
APP_NAME=NPS System
APP_SECRET=tu_secret_key_seguro
SESSION_SECRET=tu_session_secret_seguro

# Admin
ADMIN_EMAIL=admin@tudominio.com
ADMIN_PASSWORD=password_seguro
```

### Puertos
- **80** - Aplicación NPS
- **3306** - MySQL (solo acceso interno)
- **8080** - phpMyAdmin (opcional)

## 📊 Monitoreo

### Health Checks
Los contenedores incluyen health checks automáticos:
- Aplicación: Verifica que responda en `/responder.php`
- Base de datos: Verifica conexión MySQL

### Logs
```bash
# Ver logs de la aplicación
docker-compose logs nps-app

# Ver logs de la base de datos
docker-compose logs nps-db
```

## 🔒 Seguridad

### Recomendaciones
1. **Cambiar todas las contraseñas por defecto**
2. **Usar HTTPS en producción**
3. **Configurar firewall para limitar acceso a puertos**
4. **Hacer backup regular de la base de datos**

### Backup de Base de Datos
```bash
# Crear backup
docker exec nps-db mysqldump -u nps_user -p nps_system > backup.sql

# Restaurar backup
docker exec -i nps-db mysql -u nps_user -p nps_system < backup.sql
```

## 🛠️ Troubleshooting

### Problemas Comunes

1. **Error de conexión a MySQL**
   ```bash
   docker-compose logs nps-db
   ```

2. **Permisos de archivos**
   ```bash
   docker exec nps-app chown -R www-data:www-data /var/www/html
   ```

3. **Reiniciar servicios**
   ```bash
   docker-compose restart
   ```

## 📈 Escalabilidad

Para escalar la aplicación:
1. Usar un load balancer
2. Configurar múltiples instancias de la aplicación
3. Usar una base de datos externa (RDS)
4. Implementar caché con Redis

## 🔄 Actualizaciones

```bash
# Actualizar código
git pull origin main

# Reconstruir y reiniciar
docker-compose -f docker-compose.prod.yml up -d --build
``` 