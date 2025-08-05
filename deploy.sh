#!/bin/bash

# Script de auto-deploy que se ejecuta después de git pull
echo "🚀 Iniciando auto-deploy..."

# Verificar si estamos en el directorio correcto
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Error: No se encontró docker-compose.yml"
    exit 1
fi

# Detener contenedores si están corriendo
echo "🛑 Deteniendo contenedores..."
docker-compose down 2>/dev/null || true

# Reconstruir la imagen de la aplicación
echo "🔨 Reconstruyendo imagen de la aplicación..."
docker-compose build app

# Levantar contenedores
echo "🚀 Levantando contenedores..."
docker-compose up -d

# Esperar a que la base de datos esté lista
echo "⏳ Esperando a que la base de datos esté lista..."
sleep 10

# Verificar que los contenedores estén corriendo
echo "✅ Verificando estado de contenedores..."
docker-compose ps

# Verificar que la aplicación esté respondiendo
echo "🌐 Verificando que la aplicación esté respondiendo..."
sleep 5
if curl -f http://localhost > /dev/null 2>&1; then
    echo "✅ Aplicación respondiendo correctamente"
else
    echo "⚠️ La aplicación puede tardar unos segundos en estar lista"
fi

echo "✅ Auto-deploy completado!"
echo "🌐 Aplicación disponible en: http://nps.grupopcr.com.pa"
echo "📊 phpMyAdmin disponible en: http://nps.grupopcr.com.pa:8080" 