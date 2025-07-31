#!/bin/bash

# Script de despliegue para Sistema NPS en Amazon
# Uso: ./deploy.sh [production|development]

set -e

ENVIRONMENT=${1:-production}
COMPOSE_FILE="docker-compose.yml"

if [ "$ENVIRONMENT" = "production" ]; then
    COMPOSE_FILE="docker-compose.prod.yml"
    echo "🚀 Desplegando en modo PRODUCCIÓN"
else
    echo "🔧 Desplegando en modo DESARROLLO"
fi

echo "📋 Verificando requisitos..."

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Instálalo primero."
    exit 1
fi

# Verificar si Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Instálalo primero."
    exit 1
fi

echo "✅ Docker y Docker Compose están instalados"

# Verificar si existe el archivo .env
if [ ! -f .env ]; then
    echo "⚠️  Archivo .env no encontrado. Creando uno básico..."
    cp env.example .env
    echo "📝 Por favor, edita el archivo .env con tus credenciales antes de continuar."
    echo "   Luego ejecuta: ./deploy.sh $ENVIRONMENT"
    exit 1
fi

echo "✅ Archivo .env encontrado"

# Detener contenedores existentes
echo "🛑 Deteniendo contenedores existentes..."
docker-compose -f $COMPOSE_FILE down 2>/dev/null || true

# Limpiar imágenes antiguas (opcional)
echo "🧹 Limpiando imágenes antiguas..."
docker system prune -f

# Construir y ejecutar
echo "🔨 Construyendo y ejecutando contenedores..."
docker-compose -f $COMPOSE_FILE up -d --build

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 30

# Verificar estado de los contenedores
echo "🔍 Verificando estado de los contenedores..."
docker-compose -f $COMPOSE_FILE ps

# Verificar logs
echo "📊 Mostrando logs recientes..."
docker-compose -f $COMPOSE_FILE logs --tail=20

echo ""
echo "🎉 ¡Despliegue completado!"
echo ""
echo "📱 URLs de acceso:"
echo "   - Aplicación NPS: http://$(curl -s ifconfig.me)"
echo "   - phpMyAdmin: http://$(curl -s ifconfig.me):8080"
echo ""
echo "🔧 Comandos útiles:"
echo "   - Ver logs: docker-compose -f $COMPOSE_FILE logs -f"
echo "   - Detener: docker-compose -f $COMPOSE_FILE down"
echo "   - Reiniciar: docker-compose -f $COMPOSE_FILE restart"
echo ""
echo "📝 Credenciales por defecto:"
echo "   - Admin: admin@nps.com / password"
echo "   - MySQL: nps_user / nps_password"
echo ""
echo "⚠️  IMPORTANTE: Cambia las contraseñas por defecto en producción!" 