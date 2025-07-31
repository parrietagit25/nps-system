<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/NPSService.php';

$nps = new NPSService();
$error = '';
$success = '';
$encuesta = null;

// Obtener token de la URL
$token = $_GET['token'] ?? '';

if ($token) {
    $encuesta = $nps->validarToken($token);
    if (!$encuesta) {
        $error = 'Enlace inválido o encuesta ya respondida.';
    }
}

// Procesar respuesta
if ($_POST && $token) {
    $puntuacion = (int)($_POST['puntuacion'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    
    $resultado = $nps->procesarRespuesta($token, $puntuacion, $comentario);
    
    if ($resultado['success']) {
        $success = '¡Gracias por su respuesta! Su opinión es muy valiosa para nosotros.';
    } else {
        $error = $resultado['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta NPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .survey-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .survey-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .survey-content {
            padding: 40px;
        }
        .rating-container {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .rating-option {
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 10px;
            border-radius: 10px;
            margin: 5px;
            min-width: 60px;
        }
        .rating-option:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        .rating-option.selected {
            background: #007bff;
            color: white;
        }
        .rating-option input {
            display: none;
        }
        .rating-label {
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .rating-text {
            font-size: 12px;
            opacity: 0.8;
        }
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .nps-explanation {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="survey-container">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php elseif ($encuesta): ?>
                <div class="survey-header">
                    <h1><i class="fas fa-chart-line"></i> Encuesta NPS</h1>
                    <p class="mb-0">Su opinión es muy importante para nosotros</p>
                </div>
                
                <div class="survey-content">
                    <h2><?php echo htmlspecialchars($encuesta['titulo']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($encuesta['descripcion']); ?></p>
                    
                    <div class="nps-explanation">
                        <h5><i class="fas fa-info-circle"></i> ¿Qué es NPS?</h5>
                        <p class="mb-0">
                            NPS (Net Promoter Score) es una métrica que mide la lealtad del cliente. 
                            Califique de 0 a 10 qué tan probable es que recomiende nuestro servicio a un amigo o colega.
                        </p>
                    </div>
                    
                    <form method="POST" id="npsForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-question-circle"></i>
                                <?php echo htmlspecialchars($encuesta['pregunta']); ?>
                            </label>
                            
                            <div class="rating-container">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <div class="rating-option" onclick="selectRating(<?php echo $i; ?>)">
                                        <input type="radio" name="puntuacion" value="<?php echo $i; ?>" id="rating_<?php echo $i; ?>">
                                        <label for="rating_<?php echo $i; ?>" class="rating-label"><?php echo $i; ?></label>
                                        <div class="rating-text">
                                            <?php if ($i <= 6): ?>
                                                Detractor
                                            <?php elseif ($i <= 8): ?>
                                                Pasivo
                                            <?php else: ?>
                                                Promotor
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="comentario" class="form-label">
                                <i class="fas fa-comment"></i> Comentarios adicionales (opcional)
                            </label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                      placeholder="Comparta su experiencia con nosotros..."></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-submit text-white" id="submitBtn" disabled>
                                <i class="fas fa-paper-plane"></i> Enviar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRating(rating) {
            // Remover selección previa
            document.querySelectorAll('.rating-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Seleccionar nueva opción
            document.querySelector(`#rating_${rating}`).parentElement.classList.add('selected');
            document.querySelector(`#rating_${rating}`).checked = true;
            
            // Habilitar botón de envío
            document.getElementById('submitBtn').disabled = false;
        }
        
        // Validar formulario antes de enviar
        document.getElementById('npsForm').addEventListener('submit', function(e) {
            const selectedRating = document.querySelector('input[name="puntuacion"]:checked');
            if (!selectedRating) {
                e.preventDefault();
                alert('Por favor seleccione una puntuación antes de enviar.');
            }
        });
    </script>
</body>
</html> 