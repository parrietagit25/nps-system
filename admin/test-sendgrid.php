<?php
require_once '../config/database.php';
require_once '../includes/SendGridService.php';

echo "<h2>Test de SendGrid</h2>";

try {
    $sendgrid = new SendGridService();
    echo "<p>✅ SendGridService creado correctamente</p>";
    
    // Test de configuración
    echo "<h3>Configuración:</h3>";
    echo "<p>API Key: " . substr(SENDGRID_API_KEY, 0, 10) . "...</p>";
    echo "<p>From Email: " . SENDGRID_FROM_EMAIL . "</p>";
    echo "<p>From Name: " . SENDGRID_FROM_NAME . "</p>";
    
    // Test de envío simple
    echo "<h3>Test de Envío:</h3>";
    
    $testEmail = new \SendGrid\Mail\Mail();
    $testEmail->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
    $testEmail->setSubject("Test NPS System");
    $testEmail->addTo("test@example.com", "Test User");
    $testEmail->addContent("text/plain", "Este es un test del sistema NPS");
    
    $sendgridClient = new \SendGrid(SENDGRID_API_KEY);
    $response = $sendgridClient->send($testEmail);
    
    echo "<p>Status Code: " . $response->statusCode() . "</p>";
    echo "<p>Headers: " . print_r($response->headers(), true) . "</p>";
    echo "<p>Body: " . $response->body() . "</p>";
    
    if ($response->statusCode() == 202) {
        echo "<p style='color: green;'>✅ Test exitoso - SendGrid funciona correctamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error en SendGrid</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 