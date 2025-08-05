<?php
// Script de diagnóstico para problemas de headers
echo "<h2>Diagnóstico de Headers</h2>";

// Verificar si los headers ya fueron enviados
if (headers_sent($filename, $linenum)) {
    echo "<p style='color: red;'>❌ Headers ya fueron enviados en $filename línea $linenum</p>";
} else {
    echo "<p style='color: green;'>✅ Headers no han sido enviados aún</p>";
}

// Verificar archivos incluidos
echo "<h3>Archivos incluidos:</h3>";
$included_files = get_included_files();
foreach ($included_files as $file) {
    echo "<p>$file</p>";
}

// Verificar configuración de PHP
echo "<h3>Configuración de PHP:</h3>";
echo "<p>output_buffering: " . ini_get('output_buffering') . "</p>";
echo "<p>implicit_flush: " . ini_get('implicit_flush') . "</p>";
echo "<p>display_errors: " . ini_get('display_errors') . "</p>";

// Verificar si hay espacios en blanco antes de <?php
echo "<h3>Verificación de espacios en blanco:</h3>";
$files_to_check = [
    '../config/database.php',
    '../includes/NPSService.php',
    '../includes/SendGridService.php',
    'crear-encuesta.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            echo "<p style='color: red;'>❌ $file tiene BOM</p>";
        } elseif (substr($content, 0, 1) === ' ') {
            echo "<p style='color: red;'>❌ $file tiene espacios antes de <?php</p>";
        } else {
            echo "<p style='color: green;'>✅ $file está bien</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ $file no existe</p>";
    }
}

// Test de carga de archivos
echo "<h3>Test de carga de archivos:</h3>";
try {
    require_once '../config/database.php';
    echo "<p style='color: green;'>✅ database.php cargado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando database.php: " . $e->getMessage() . "</p>";
}

try {
    require_once '../includes/NPSService.php';
    echo "<p style='color: green;'>✅ NPSService.php cargado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando NPSService.php: " . $e->getMessage() . "</p>";
}

try {
    require_once '../includes/SendGridService.php';
    echo "<p style='color: green;'>✅ SendGridService.php cargado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando SendGridService.php: " . $e->getMessage() . "</p>";
}
?> 