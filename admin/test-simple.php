<?php
// Test muy simple para verificar el problema
echo "<h2>Test Simple</h2>";

// Verificar si los headers ya fueron enviados
if (headers_sent($filename, $linenum)) {
    echo "<p style='color: red;'>❌ Headers ya fueron enviados en $filename línea $linenum</p>";
} else {
    echo "<p style='color: green;'>✅ Headers no han sido enviados aún</p>";
}

// Intentar cargar solo database.php
try {
    require_once '../config/database.php';
    echo "<p style='color: green;'>✅ database.php cargado</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando database.php: " . $e->getMessage() . "</p>";
}

// Intentar cargar NPSService.php
try {
    require_once '../includes/NPSService.php';
    echo "<p style='color: green;'>✅ NPSService.php cargado</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando NPSService.php: " . $e->getMessage() . "</p>";
}

// Verificar el contenido del archivo NPSService.php
echo "<h3>Contenido del final de NPSService.php:</h3>";
$content = file_get_contents('../includes/NPSService.php');
$lines = explode("\n", $content);
$last_lines = array_slice($lines, -5);
foreach ($last_lines as $i => $line) {
    $line_num = count($lines) - 5 + $i + 1;
    echo "<p>Línea $line_num: " . htmlspecialchars($line) . "</p>";
}

// Verificar si hay caracteres invisibles
echo "<h3>Análisis de caracteres:</h3>";
$last_char = substr($content, -1);
echo "<p>Último carácter (ASCII): " . ord($last_char) . "</p>";
echo "<p>Último carácter (hex): " . bin2hex($last_char) . "</p>";
?> 