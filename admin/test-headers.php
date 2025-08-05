<?php
// Test simple para verificar headers
session_start();

// Verificar si los headers ya fueron enviados
if (headers_sent($filename, $linenum)) {
    echo "<p style='color: red;'>❌ Headers ya fueron enviados en $filename línea $linenum</p>";
} else {
    echo "<p style='color: green;'>✅ Headers no han sido enviados aún</p>";
}

// Intentar cargar los archivos problemáticos
try {
    require_once '../config/database.php';
    echo "<p style='color: green;'>✅ database.php cargado</p>";
    
    require_once '../includes/NPSService.php';
    echo "<p style='color: green;'>✅ NPSService.php cargado</p>";
    
    $nps = new NPSService();
    echo "<p style='color: green;'>✅ NPSService instanciado</p>";
    
    // Test de redirección
    if (isset($_GET['redirect'])) {
        header('Location: index.php');
        exit;
    }
    
    echo "<p style='color: green;'>✅ Todo funciona correctamente</p>";
    echo "<p><a href='?redirect=1'>Test de redirección</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 