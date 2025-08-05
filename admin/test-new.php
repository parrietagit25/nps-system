<?php
// Test con la nueva versión de NPSService
echo "<h2>Test con Nueva Versión</h2>";

// Verificar si los headers ya fueron enviados
if (headers_sent($filename, $linenum)) {
    echo "<p style='color: red;'>❌ Headers ya fueron enviados en $filename línea $linenum</p>";
} else {
    echo "<p style='color: green;'>✅ Headers no han sido enviados aún</p>";
}

// Intentar cargar la nueva versión
try {
    require_once '../config/database.php';
    echo "<p style='color: green;'>✅ database.php cargado</p>";
    
    require_once '../includes/NPSService-new.php';
    echo "<p style='color: green;'>✅ NPSService-new.php cargado</p>";
    
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