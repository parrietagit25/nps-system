<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$nps = new NPSService();
$success = '';
$error = '';

if ($_POST) {
    $datos = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'pregunta' => trim($_POST['pregunta'] ?? ''),
        'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
        'fecha_fin' => $_POST['fecha_fin'] ?? null,
        'estado' => $_POST['estado'] ?? 'borrador',
        'creado_por' => $_SESSION['admin_id']
    ];
    
    // Validaciones
    if (empty($datos['titulo'])) {
        $error = 'El título es obligatorio.';
    } elseif (empty($datos['pregunta'])) {
        $error = 'La pregunta es obligatoria.';
    } else {
        $encuestaId = $nps->crearEncuesta($datos);
        
        if ($encuestaId) {
            $success = 'Encuesta creada exitosamente. ID: ' . $encuestaId;
            
            // Si se activó automáticamente, redirigir a agregar destinatarios
            if ($datos['estado'] === 'activa') {
                header('Location: agregar-destinatarios.php?id=' . $encuestaId);
                exit;
            }
        } else {
            $error = 'Error al crear la encuesta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Encuesta - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus"></i> Crear Nueva Encuesta NPS
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="titulo" class="form-label">
                                        <i class="fas fa-heading"></i> Título de la Encuesta *
                                    </label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           placeholder="Ej: Satisfacción del Cliente Q1 2024" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">
                                        <i class="fas fa-align-left"></i> Descripción
                                    </label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                              placeholder="Descripción detallada de la encuesta..."></textarea>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="pregunta" class="form-label">
                                        <i class="fas fa-question-circle"></i> Pregunta NPS *
                                    </label>
                                    <textarea class="form-control" id="pregunta" name="pregunta" rows="2" required
                                              placeholder="¿Qué tan probable es que recomiende nuestro servicio a un amigo o colega?"></textarea>
                                    <small class="text-muted">
                                        Esta es la pregunta principal que se mostrará a los destinatarios.
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar"></i> Fecha de Inicio
                                    </label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar"></i> Fecha de Finalización
                                    </label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                </div>
                                
                                <div class="col-md-12 mb-4">
                                    <label for="estado" class="form-label">
                                        <i class="fas fa-toggle-on"></i> Estado
                                    </label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="borrador">Borrador</option>
                                        <option value="activa">Activa</option>
                                        <option value="inactiva">Inactiva</option>
                                    </select>
                                    <small class="text-muted">
                                        <strong>Borrador:</strong> Solo visible para administradores<br>
                                        <strong>Activa:</strong> Los destinatarios pueden responder<br>
                                        <strong>Inactiva:</strong> No se pueden enviar más respuestas
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Crear Encuesta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Información sobre NPS -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> ¿Qué es NPS?
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>NPS (Net Promoter Score)</strong> es una métrica que mide la lealtad del cliente y la probabilidad de que recomienden su producto o servicio.</p>
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-danger text-white rounded">
                                    <h6>Detractores (0-6)</h6>
                                    <p class="mb-0">Clientes insatisfechos</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-warning text-dark rounded">
                                    <h6>Pasivos (7-8)</h6>
                                    <p class="mb-0">Clientes neutrales</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-success text-white rounded">
                                    <h6>Promotores (9-10)</h6>
                                    <p class="mb-0">Clientes leales</p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="mt-3 mb-0">
                            <strong>Fórmula NPS:</strong> % Promotores - % Detractores = NPS Score
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 