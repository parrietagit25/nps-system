<?php
// Verificar que no haya salida antes de los headers
if (headers_sent($filename, $linenum)) {
    die("Headers already sent in $filename on line $linenum");
}

// Cargar autoload de manera segura
if (!class_exists('SendGrid\Mail\Mail')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/../config/database.php';

use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;

class SendGridService {
    private $sendgrid;
    private $fromEmail;
    private $fromName;
    private $db;
    
    public function __construct() {
        $this->sendgrid = new \SendGrid(SENDGRID_API_KEY);
        $this->fromEmail = SENDGRID_FROM_EMAIL;
        $this->fromName = SENDGRID_FROM_NAME;
        $this->db = new Database();
    }
    
    /**
     * Envía una encuesta NPS por email
     */
    public function enviarEncuestaNPS($destinatario, $encuesta) {
        try {
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject("Encuesta NPS: " . $encuesta['titulo']);
            $email->addTo($destinatario['email'], $destinatario['nombre']);
            
            // URL de la encuesta
            $surveyUrl = APP_URL . "/responder.php?token=" . $destinatario['token_unico'];
            
            // Contenido del email
            $htmlContent = $this->generarHTMLEncuesta($encuesta, $surveyUrl);
            $textContent = $this->generarTextoEncuesta($encuesta, $surveyUrl);
            
            $email->addContent("text/html", $htmlContent);
            $email->addContent("text/plain", $textContent);
            
            $response = $this->sendgrid->send($email);
            
            // Registrar el envío en la base de datos
            $this->registrarEnvio($destinatario['id'], $response->headers()['X-Message-Id'] ?? null);
            
            // Actualizar estado del destinatario
            $this->actualizarEstadoDestinatario($destinatario['id']);
            
            return [
                'success' => $response->statusCode() == 202,
                'message_id' => $response->headers()['X-Message-Id'] ?? null,
                'status_code' => $response->statusCode()
            ];
            
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Genera el contenido HTML del email
     */
    private function generarHTMLEncuesta($encuesta, $surveyUrl) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Encuesta NPS</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Encuesta NPS</h1>
                </div>
                <div class="content">
                    <h2>' . htmlspecialchars($encuesta['titulo']) . '</h2>
                    <p>' . htmlspecialchars($encuesta['descripcion']) . '</p>
                    <p><strong>Pregunta:</strong> ' . htmlspecialchars($encuesta['pregunta']) . '</p>
                    <p>Su opinión es muy importante para nosotros. Por favor, tómese un momento para responder esta breve encuesta.</p>
                    <div style="text-align: center;">
                        <a href="' . $surveyUrl . '" class="button">Responder Encuesta</a>
                    </div>
                    <p style="margin-top: 20px; font-size: 14px; color: #666;">
                        Si el botón no funciona, copie y pegue este enlace en su navegador:<br>
                        <a href="' . $surveyUrl . '">' . $surveyUrl . '</a>
                    </p>
                </div>
                <div class="footer">
                    <p>Este email fue enviado por el sistema NPS. Si no desea recibir más encuestas, puede darse de baja respondiendo a este email.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Genera el contenido de texto plano del email
     */
    private function generarTextoEncuesta($encuesta, $surveyUrl) {
        return "Encuesta NPS: " . $encuesta['titulo'] . "\n\n" .
               "Descripción: " . $encuesta['descripcion'] . "\n\n" .
               "Pregunta: " . $encuesta['pregunta'] . "\n\n" .
               "Su opinión es muy importante para nosotros. Por favor, tómese un momento para responder esta breve encuesta.\n\n" .
               "Para responder la encuesta, visite: " . $surveyUrl . "\n\n" .
               "Gracias por su tiempo.";
    }
    
    /**
     * Registra el envío en la base de datos
     */
    private function registrarEnvio($destinatarioId, $messageId) {
        $sql = "INSERT INTO logs_email (destinatario_id, sendgrid_message_id, estado) VALUES (?, ?, 'enviado')";
        $this->db->query($sql, [$destinatarioId, $messageId]);
    }
    
    /**
     * Actualiza el estado del destinatario
     */
    private function actualizarEstadoDestinatario($destinatarioId) {
        $sql = "UPDATE destinatarios SET enviado = TRUE, fecha_envio = NOW() WHERE id = ?";
        $this->db->query($sql, [$destinatarioId]);
    }
    
    /**
     * Envía notificación de resultados a administradores
     */
    public function enviarNotificacionResultados($encuestaId, $resultados) {
        try {
            $sql = "SELECT email, nombre FROM usuarios WHERE rol = 'admin' AND activo = TRUE";
            $stmt = $this->db->query($sql);
            $admins = $stmt->fetchAll();
            
            foreach ($admins as $admin) {
                $email = new Mail();
                $email->setFrom($this->fromEmail, $this->fromName);
                $email->setSubject("Resultados NPS disponibles");
                $email->addTo($admin['email'], $admin['nombre']);
                
                $htmlContent = $this->generarHTMLResultados($resultados);
                $email->addContent("text/html", $htmlContent);
                
                $this->sendgrid->send($email);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error enviando notificación de resultados: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera HTML para notificación de resultados
     */
    private function generarHTMLResultados($resultados) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Resultados NPS</title>
        </head>
        <body>
            <h2>Resultados NPS Disponibles</h2>
            <p>Se han recibido nuevas respuestas para la encuesta NPS.</p>
            <ul>
                <li>Total de respuestas: ' . $resultados['total'] . '</li>
                <li>Promotores: ' . $resultados['promotores'] . '</li>
                <li>Pasivos: ' . $resultados['pasivos'] . '</li>
                <li>Detractores: ' . $resultados['detractores'] . '</li>
                <li>NPS Score: ' . $resultados['nps_score'] . '%</li>
            </ul>
            <p>Acceda al panel de administración para ver más detalles.</p>
        </body>
        </html>';
    }
}
