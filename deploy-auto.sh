#!/bin/bash

# Script de auto-deploy para el contenedor
# Este script se ejecuta automáticamente después de git pull

echo "🚀 Iniciando auto-deploy..."

# Verificar si estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: No se encontró composer.json. Asegúrate de estar en el directorio correcto."
    exit 1
fi

# Actualizar dependencias de Composer
echo "📦 Actualizando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

# Verificar permisos de archivos
echo "🔐 Ajustando permisos..."
chmod 644 *.php
chmod 644 admin/*.php
chmod 644 includes/*.php
chmod 644 config/*.php

# Limpiar caché si existe
if [ -d "cache" ]; then
    echo "🧹 Limpiando caché..."
    rm -rf cache/*
fi

# Verificar archivos críticos
echo "✅ Verificando archivos críticos..."
if [ ! -f "config/database.php" ]; then
    echo "❌ Error: database.php no encontrado"
    exit 1
fi

if [ ! -f "includes/NPSService.php" ]; then
    echo "❌ Error: NPSService.php no encontrado"
    exit 1
fi

# Crear backup de archivos importantes antes de reemplazar
echo "💾 Creando backups..."
if [ -f "includes/NPSService.php" ]; then
    cp includes/NPSService.php includes/NPSService.php.backup.$(date +%Y%m%d_%H%M%S)
fi

# Reemplazar archivos si existen las versiones nuevas
if [ -f "includes/NPSService-new.php" ]; then
    echo "🔄 Reemplazando NPSService.php con la nueva versión..."
    mv includes/NPSService-new.php includes/NPSService.php
fi

if [ -f "admin/crear-encuesta-new.php" ]; then
    echo "🔄 Reemplazando crear-encuesta.php con la nueva versión..."
    mv admin/crear-encuesta-new.php admin/crear-encuesta.php
fi

# Limpiar archivos temporales
echo "🧹 Limpiando archivos temporales..."
rm -f includes/NPSService-clean.php
rm -f admin/test-*.php
rm -f admin/debug-*.php
rm -f admin/diagnostico.php

# Verificar que todo esté funcionando
echo "🔍 Verificando que todo esté funcionando..."
php -l config/database.php
php -l includes/NPSService.php
php -l admin/crear-encuesta.php

echo "✅ Auto-deploy completado exitosamente!"
echo "🌐 La aplicación está lista en: http://nps.grupopcr.com.pa" 