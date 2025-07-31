<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SendGridService.php';

class NPSService {
    private $db;
    private $sendgrid;
    
    public function __construct() {
        $this->db = new Database();
        $this->sendgrid = new SendGridService();
    }
    
    /**
     * Crea una nueva encuesta NPS
     */
    public function crearEncuesta($datos) {
        try {
            $sql = "INSERT INTO encuestas (titulo, descripcion, pregunta, fecha_inicio, fecha_fin, estado, creado_por) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->query($sql, [
                $datos['titulo'],
                $datos['descripcion'],
                $datos['pregunta'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['estado'],
                $datos['creado_por']
            ]);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creando encuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Agrega destinatarios a una encuesta
     */
    public function agregarDestinatarios($encuestaId, $destinatarios) {
        try {
            $sql = "INSERT INTO destinatarios (encuesta_id, nombre, email, token_unico) VALUES (?, ?, ?, ?)";
            
            foreach ($destinatarios as $destinatario) {
                $token = $this->generarTokenUnico();
                $this->db->query($sql, [
                    $encuestaId,
                    $destinatario['nombre'],
                    $destinatario['email'],
                    $token
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error agregando destinatarios: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envía encuestas por email
     */
    public function enviarEncuestas($encuestaId) {
        try {
            // Obtener encuesta
            $sql = "SELECT * FROM encuestas WHERE id = ?";
            $stmt = $this->db->query($sql, [$encuestaId]);
            $encuesta = $stmt->fetch();
            
            if (!$encuesta) {
                return ['success' => false, 'error' => 'Encuesta no encontrada'];
            }
            
            // Obtener destinatarios no enviados
            $sql = "SELECT * FROM destinatarios WHERE encuesta_id = ? AND enviado = FALSE";
            $stmt = $this->db->query($sql, [$encuestaId]);
            $destinatarios = $stmt->fetchAll();
            
            $enviados = 0;
            $errores = 0;
            
            foreach ($destinatarios as $destinatario) {
                $resultado = $this->sendgrid->enviarEncuestaNPS($destinatario, $encuesta);
                
                if ($resultado['success']) {
                    $enviados++;
                } else {
                    $errores++;
                }
            }
            
            return [
                'success' => true,
                'enviados' => $enviados,
                'errores' => $errores,
                'total' => count($destinatarios)
            ];
            
        } catch (Exception $e) {
            error_log("Error enviando encuestas: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Procesa una respuesta NPS
     */
    public function procesarRespuesta($token, $puntuacion, $comentario = '') {
        try {
            // Verificar token
            $sql = "SELECT d.*, e.* FROM destinatarios d 
                    JOIN encuestas e ON d.encuesta_id = e.id 
                    WHERE d.token_unico = ? AND d.respondido = FALSE";
            $stmt = $this->db->query($sql, [$token]);
            $destinatario = $stmt->fetch();
            
            if (!$destinatario) {
                return ['success' => false, 'error' => 'Token inválido o ya respondido'];
            }
            
            // Validar puntuación
            if ($puntuacion < 0 || $puntuacion > 10) {
                return ['success' => false, 'error' => 'Puntuación inválida'];
            }
            
            // Insertar respuesta
            $sql = "INSERT INTO respuestas (encuesta_id, destinatario_id, puntuacion, comentario, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $destinatario['encuesta_id'],
                $destinatario['id'],
                $puntuacion,
                $comentario,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            // Marcar como respondido
            $sql = "UPDATE destinatarios SET respondido = TRUE, fecha_respuesta = NOW() WHERE id = ?";
            $this->db->query($sql, [$destinatario['id']]);
            
            return ['success' => true, 'message' => 'Respuesta registrada correctamente'];
            
        } catch (Exception $e) {
            error_log("Error procesando respuesta: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene resultados de una encuesta
     */
    public function obtenerResultados($encuestaId) {
        try {
            // Estadísticas generales
            $sql = "SELECT 
                        COUNT(*) as total_respuestas,
                        AVG(puntuacion) as promedio,
                        COUNT(CASE WHEN categoria = 'promotor' THEN 1 END) as promotores,
                        COUNT(CASE WHEN categoria = 'pasivo' THEN 1 END) as pasivos,
                        COUNT(CASE WHEN categoria = 'detractor' THEN 1 END) as detractores
                    FROM respuestas 
                    WHERE encuesta_id = ?";
            
            $stmt = $this->db->query($sql, [$encuestaId]);
            $stats = $stmt->fetch();
            
            // Calcular NPS Score
            $total = $stats['total_respuestas'];
            $nps_score = $total > 0 ? 
                (($stats['promotores'] - $stats['detractores']) / $total) * 100 : 0;
            
            // Distribución de puntuaciones
            $sql = "SELECT puntuacion, COUNT(*) as cantidad 
                    FROM respuestas 
                    WHERE encuesta_id = ? 
                    GROUP BY puntuacion 
                    ORDER BY puntuacion";
            $stmt = $this->db->query($sql, [$encuestaId]);
            $distribucion = $stmt->fetchAll();
            
            // Comentarios recientes
            $sql = "SELECT r.comentario, r.puntuacion, r.fecha_respuesta, d.nombre, d.email
                    FROM respuestas r
                    JOIN destinatarios d ON r.destinatario_id = d.id
                    WHERE r.encuesta_id = ? AND r.comentario IS NOT NULL AND r.comentario != ''
                    ORDER BY r.fecha_respuesta DESC
                    LIMIT 10";
            $stmt = $this->db->query($sql, [$encuestaId]);
            $comentarios = $stmt->fetchAll();
            
            return [
                'success' => true,
                'stats' => $stats,
                'nps_score' => round($nps_score, 2),
                'distribucion' => $distribucion,
                'comentarios' => $comentarios
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo resultados: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene lista de encuestas
     */
    public function obtenerEncuestas($filtros = []) {
        try {
            $sql = "SELECT e.*, u.nombre as creador, 
                           COUNT(d.id) as total_destinatarios,
                           COUNT(CASE WHEN d.respondido = TRUE THEN 1 END) as respuestas_recibidas
                    FROM encuestas e
                    LEFT JOIN usuarios u ON e.creado_por = u.id
                    LEFT JOIN destinatarios d ON e.id = d.encuesta_id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND e.estado = ?";
                $params[] = $filtros['estado'];
            }
            
            $sql .= " GROUP BY e.id ORDER BY e.fecha_creacion DESC";
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error obteniendo encuestas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Genera un token único para destinatarios
     */
    private function generarTokenUnico() {
        do {
            $token = bin2hex(random_bytes(32));
            $sql = "SELECT COUNT(*) FROM destinatarios WHERE token_unico = ?";
            $stmt = $this->db->query($sql, [$token]);
            $existe = $stmt->fetchColumn() > 0;
        } while ($existe);
        
        return $token;
    }
    
    /**
     * Valida un token de encuesta
     */
    public function validarToken($token) {
        try {
            $sql = "SELECT d.*, e.* FROM destinatarios d 
                    JOIN encuestas e ON d.encuesta_id = e.id 
                    WHERE d.token_unico = ? AND d.respondido = FALSE AND e.estado = 'activa'";
            $stmt = $this->db->query($sql, [$token]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 