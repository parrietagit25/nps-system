#!/bin/bash

# Script de entrada para el contenedor NPS

echo "🚀 Iniciando Sistema NPS..."

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté disponible..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "✅ MySQL está listo!"

# Crear directorios necesarios si no existen
mkdir -p /var/www/html/uploads
mkdir -p /var/www/html/logs
mkdir -p /var/www/html/sessions

# Configurar permisos
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Verificar si existe el archivo .env, si no, crear uno básico
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Creando archivo .env básico..."
    cat > /var/www/html/.env << EOF
# Configuración de Base de Datos
DB_HOST=${DB_HOST:-nps-db}
DB_NAME=${DB_NAME:-nps_system}
DB_USER=${DB_USER:-nps_user}
DB_PASS=${DB_PASS:-nps_password}

# Configuración de SendGrid
SENDGRID_API_KEY=${SENDGRID_API_KEY}
SENDGRID_FROM_EMAIL=${SENDGRID_FROM_EMAIL}
SENDGRID_FROM_NAME=${SENDGRID_FROM_NAME:-NPS System}

# Configuración de la Aplicación
APP_URL=${APP_URL:-http://localhost}
APP_NAME=${APP_NAME:-NPS System}

# Configuración de Seguridad
APP_SECRET=${APP_SECRET:-change-this-in-production}
SESSION_SECRET=${SESSION_SECRET:-change-this-in-production}

# Configuración de Email
ADMIN_EMAIL=${ADMIN_EMAIL:-admin@nps.com}
ADMIN_PASSWORD=${ADMIN_PASSWORD:-password}

# Configuración de Entorno
APP_ENV=${APP_ENV:-production}
DEBUG=${DEBUG:-false}
EOF
fi

# Verificar si la base de datos está inicializada
echo "🔍 Verificando base de datos..."
if ! mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; SELECT 1;" 2>/dev/null; then
    echo "⚠️  Base de datos no inicializada. Asegúrate de que el esquema se haya importado correctamente."
fi

echo "🎉 Sistema NPS listo para usar!"

# Iniciar Apache
exec apache2-foreground 