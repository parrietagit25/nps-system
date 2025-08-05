#!/bin/bash

# Script de inicio rápido para NPS System
echo "🚀 Iniciando NPS System..."

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker no está instalado"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: Docker Compose no está instalado"
    exit 1
fi

# Configurar git hook
echo "🔧 Configurando git hook..."
chmod +x setup-git-hook.sh
./setup-git-hook.sh

# Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp env.example .env
    echo "✅ Archivo .env creado. Edítalo si necesitas cambiar las credenciales de la BD"
fi

# Ejecutar deploy inicial
echo "🚀 Ejecutando deploy inicial..."
chmod +x deploy.sh
./deploy.sh

echo ""
echo "🎉 ¡NPS System iniciado exitosamente!"
echo "🌐 Aplicación: http://nps.grupopcr.com.pa"
echo "📊 phpMyAdmin: http://nps.grupopcr.com.pa:8080"
echo ""
echo "📋 Para futuras actualizaciones, simplemente haz:"
echo "   git pull origin master"
echo ""
echo "🔄 El deploy se ejecutará automáticamente después del git pull" 