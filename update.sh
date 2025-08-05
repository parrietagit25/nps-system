#!/bin/bash

# Comando personalizado: git pull + auto-deploy
# Uso: ./update.sh

echo "🔄 Actualizando desde GitHub..."
git pull origin master

echo "🚀 Ejecutando auto-deploy..."
if [ -f "deploy-auto.sh" ]; then
    chmod +x deploy-auto.sh
    ./deploy-auto.sh
else
    echo "⚠️ No se encontró deploy-auto.sh"
fi

echo "✅ Actualización completada!" 