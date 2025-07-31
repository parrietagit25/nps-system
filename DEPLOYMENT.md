# 🚀 Guía de Despliegue en Amazon

Esta guía te ayudará a desplegar el Sistema NPS en tu servidor de Amazon usando Docker.

## 📋 Prerrequisitos

- Servidor Amazon EC2 con Ubuntu
- Docker 28.3.3+ (ya instalado en tu servidor)
- Docker Compose
- Acceso SSH al servidor

## 🔧 Paso 1: Conectar al Servidor

```bash
ssh -i tu-key.pem ubuntu@tu-ip-amazon
```

## 📥 Paso 2: Clonar el Repositorio

```bash
# Navegar al directorio home
cd /home/ubuntu

# Clonar el repositorio
git clone https://github.com/parrietagit25/nps-system.git

# Entrar al directorio
cd nps-system
```

## ⚙️ Paso 3: Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp env.example .env

# Editar el archivo con tus credenciales
nano .env
```

**Configuración mínima para `.env`:**

```env
# Base de Datos
DB_HOST=nps-db
DB_NAME=nps_system
DB_USER=nps_user
DB_PASS=tu_password_seguro_aqui
MYSQL_ROOT_PASSWORD=root_password_seguro_aqui

# SendGrid (obligatorio para enviar emails)
SENDGRID_API_KEY=tu_api_key_de_sendgrid
SENDGRID_FROM_EMAIL=nps@tudominio.com
SENDGRID_FROM_NAME=NPS System

# Aplicación
APP_URL=http://tu-ip-amazon
APP_NAME=NPS System
APP_SECRET=tu_secret_key_seguro_aqui
SESSION_SECRET=tu_session_secret_seguro_aqui

# Admin
ADMIN_EMAIL=admin@tudominio.com
ADMIN_PASSWORD=password_seguro_aqui

# Entorno
APP_ENV=production
DEBUG=false
```

## 🚀 Paso 4: Desplegar la Aplicación

### Opción A: Usar el Script Automático

```bash
# Dar permisos de ejecución
chmod +x deploy.sh

# Desplegar en producción
./deploy.sh production
```

### Opción B: Comandos Manuales

```bash
# Construir y ejecutar
docker-compose -f docker-compose.prod.yml up -d --build

# Ver logs
docker-compose -f docker-compose.prod.yml logs -f
```

## ✅ Paso 5: Verificar el Despliegue

### Verificar Contenedores
```bash
docker ps
```

Deberías ver:
- `nps-app-prod` (aplicación)
- `nps-db-prod` (base de datos)

### Verificar Logs
```bash
# Logs de la aplicación
docker-compose -f docker-compose.prod.yml logs nps-app

# Logs de la base de datos
docker-compose -f docker-compose.prod.yml logs nps-db
```

### Acceder a la Aplicación
- **Aplicación NPS**: `http://tu-ip-amazon`
- **phpMyAdmin**: `http://tu-ip-amazon:8080`

## 🔐 Paso 6: Configurar Seguridad

### 1. Cambiar Contraseñas por Defecto

```bash
# Acceder a phpMyAdmin
# URL: http://tu-ip-amazon:8080
# Usuario: nps_user
# Contraseña: tu_password_seguro_aqui

# Cambiar contraseña del admin
# Ir a: http://tu-ip-amazon/admin/
# Usuario: admin@nps.com
# Contraseña: password
```

### 2. Configurar Firewall (Opcional)

```bash
# Instalar ufw
sudo apt update
sudo apt install ufw

# Configurar reglas
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 8080/tcp   # phpMyAdmin (opcional)

# Activar firewall
sudo ufw enable
```

## 📊 Paso 7: Monitoreo y Mantenimiento

### Ver Estado de los Servicios
```bash
docker-compose -f docker-compose.prod.yml ps
```

### Ver Logs en Tiempo Real
```bash
docker-compose -f docker-compose.prod.yml logs -f
```

### Reiniciar Servicios
```bash
docker-compose -f docker-compose.prod.yml restart
```

### Actualizar la Aplicación
```bash
# Obtener cambios del repositorio
git pull origin main

# Reconstruir y reiniciar
docker-compose -f docker-compose.prod.yml up -d --build
```

## 💾 Paso 8: Backup y Restauración

### Crear Backup de Base de Datos
```bash
# Backup completo
docker exec nps-db-prod mysqldump -u nps_user -p nps_system > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup solo estructura
docker exec nps-db-prod mysqldump -u nps_user -p --no-data nps_system > schema_backup.sql
```

### Restaurar Backup
```bash
# Restaurar backup
docker exec -i nps-db-prod mysql -u nps_user -p nps_system < backup.sql
```

## 🛠️ Troubleshooting

### Problema: Contenedor no inicia
```bash
# Ver logs detallados
docker-compose -f docker-compose.prod.yml logs nps-app

# Verificar configuración
docker-compose -f docker-compose.prod.yml config
```

### Problema: Error de conexión a MySQL
```bash
# Verificar que MySQL esté corriendo
docker exec nps-db-prod mysqladmin ping -h localhost

# Verificar variables de entorno
docker exec nps-app-prod env | grep DB_
```

### Problema: Permisos de archivos
```bash
# Corregir permisos
docker exec nps-app-prod chown -R www-data:www-data /var/www/html
```

## 📈 Escalabilidad

### Para Escalar la Aplicación:
1. **Usar Load Balancer**: Configurar AWS ELB
2. **Múltiples Instancias**: Ejecutar en diferentes servidores
3. **Base de Datos Externa**: Migrar a Amazon RDS
4. **Caché**: Agregar Redis para mejorar rendimiento

### Configurar RDS (Recomendado para Producción)
1. Crear instancia RDS MySQL
2. Actualizar variables de entorno con endpoint de RDS
3. Migrar datos existentes

## 🔄 Actualizaciones Automáticas

### Crear Script de Actualización
```bash
#!/bin/bash
cd /home/ubuntu/nps-system
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
```

### Configurar Cron Job
```bash
# Editar crontab
crontab -e

# Agregar línea para actualización automática (ejemplo: cada domingo a las 2 AM)
0 2 * * 0 /home/ubuntu/nps-system/update.sh
```

## 📞 Soporte

Si tienes problemas:
1. Revisar logs: `docker-compose -f docker-compose.prod.yml logs`
2. Verificar estado: `docker ps`
3. Revisar configuración: `docker-compose -f docker-compose.prod.yml config`

¡Tu Sistema NPS está listo para usar en Amazon! 🎉 