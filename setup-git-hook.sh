#!/bin/bash

# Script para configurar git hook de auto-deploy
echo "🔧 Configurando git hook de auto-deploy..."

# Crear directorio hooks si no existe
mkdir -p .git/hooks

# Crear el archivo post-merge
cat > .git/hooks/post-merge << 'EOF'
#!/bin/bash

# Git hook que se ejecuta automáticamente después de git pull
echo "🔄 Git hook: post-merge ejecutándose..."

# Ejecutar el script de auto-deploy
if [ -f "deploy.sh" ]; then
    echo "🚀 Ejecutando auto-deploy..."
    chmod +x deploy.sh
    ./deploy.sh
else
    echo "⚠️ No se encontró deploy.sh"
fi

echo "✅ Git hook completado!"
EOF

# Hacer ejecutable el hook
chmod +x .git/hooks/post-merge

echo "✅ Git hook configurado exitosamente!"
echo "🔄 Ahora cada vez que hagas 'git pull' se ejecutará automáticamente el deploy" 