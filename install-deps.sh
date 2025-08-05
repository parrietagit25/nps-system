#!/bin/bash

# Script para instalar dependencias en el servidor
# Ejecutar este script una vez para configurar el entorno

echo "🔧 Instalando dependencias..."

# Actualizar paquetes del sistema
echo "📦 Actualizando paquetes del sistema..."
apt update

# Instalar PHP y extensiones necesarias
echo "🐘 Instalando PHP y extensiones..."
apt install -y php8.1 php8.1-cli php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip

# Instalar Composer
echo "📦 Instalando Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Verificar instalaciones
echo "✅ Verificando instalaciones..."
php --version
composer --version

echo "✅ Dependencias instaladas exitosamente!"
echo "🚀 Ahora puedes usar ./deploy-auto.sh sin problemas" 