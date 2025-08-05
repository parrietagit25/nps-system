#!/bin/bash

# Script para crear el git hook en el servidor
# Ejecutar este script después de git pull

echo "🔧 Creando git hook..."

# Crear el directorio hooks si no existe
mkdir -p .git/hooks

# Crear el archivo post-merge
cat > .git/hooks/post-merge << 'EOF'
#!/bin/bash

# Git hook que se ejecuta automáticamente después de git pull
# Este archivo debe estar en .git/hooks/post-merge

echo "🔄 Git hook: post-merge ejecutándose..."

# Ejecutar el script de auto-deploy
if [ -f "deploy-auto.sh" ]; then
    echo "🚀 Ejecutando auto-deploy..."
    chmod +x deploy-auto.sh
    ./deploy-auto.sh
else
    echo "⚠️ No se encontró deploy-auto.sh"
fi

echo "✅ Git hook completado!"
EOF

# Hacer ejecutable el hook
chmod +x .git/hooks/post-merge

echo "✅ Git hook creado exitosamente!"
echo "🔄 Ahora cada vez que hagas 'git pull' se ejecutará automáticamente el deploy" 