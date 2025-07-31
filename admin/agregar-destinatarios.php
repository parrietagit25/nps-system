<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$nps = new NPSService();
$encuestaId = $_GET['id'] ?? null;
$success = '';
$error = '';

if (!$encuestaId) {
    header('Location: index.php');
    exit;
}

// Obtener información de la encuesta
$db = new Database();
$sql = "SELECT * FROM encuestas WHERE id = ?";
$stmt = $db->query($sql, [$encuestaId]);
$encuesta = $stmt->fetch();

if (!$encuesta) {
    header('Location: index.php');
    exit;
}

// Procesar formulario
if ($_POST) {
    $emails = array_filter(array_map('trim', explode("\n", $_POST['emails'] ?? '')));
    $nombres = array_filter(array_map('trim', explode("\n", $_POST['nombres'] ?? '')));
    
    if (empty($emails)) {
        $error = 'Debe ingresar al menos un email.';
    } else {
        $destinatarios = [];
        
        foreach ($emails as $index => $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = [
                    'email' => $email,
                    'nombre' => $nombres[$index] ?? ''
                ];
            }
        }
        
        if (empty($destinatarios)) {
            $error = 'No se encontraron emails válidos.';
        } else {
            $resultado = $nps->agregarDestinatarios($encuestaId, $destinatarios);
            
            if ($resultado) {
                $success = 'Se agregaron ' . count($destinatarios) . ' destinatarios exitosamente.';
            } else {
                $error = 'Error al agregar destinatarios.';
            }
        }
    }
}

// Obtener destinatarios existentes
$sql = "SELECT * FROM destinatarios WHERE encuesta_id = ? ORDER BY fecha_creacion DESC";
$stmt = $db->query($sql, [$encuestaId]);
$destinatarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Destinatarios - NPS System</title>
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
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-users"></i> Agregar Destinatarios
                    </h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Información de la encuesta -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Encuesta: <?php echo htmlspecialchars($encuesta['titulo']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo htmlspecialchars($encuesta['descripcion']); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?php echo $encuesta['estado'] == 'activa' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($encuesta['estado']); ?>
                            </span>
                        </p>
                    </div>
                </div>

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

                <!-- Formulario para agregar destinatarios -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus"></i> Agregar Nuevos Destinatarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="emails" class="form-label">
                                        <i class="fas fa-envelope"></i> Emails (uno por línea) *
                                    </label>
                                    <textarea class="form-control" id="emails" name="emails" rows="10" required
                                              placeholder="usuario1@ejemplo.com&#10;usuario2@ejemplo.com&#10;usuario3@ejemplo.com"></textarea>
                                    <small class="text-muted">
                                        Ingrese un email por línea. Se validarán automáticamente.
                                    </small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">
                                        <i class="fas fa-user"></i> Nombres (opcional, uno por línea)
                                    </label>
                                    <textarea class="form-control" id="nombres" name="nombres" rows="10"
                                              placeholder="Juan Pérez&#10;María García&#10;Carlos López"></textarea>
                                    <small class="text-muted">
                                        Los nombres son opcionales. Si no se proporcionan, se usarán los emails.
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Agregar Destinatarios
                                </button>
                                
                                <?php if ($encuesta['estado'] == 'activa'): ?>
                                    <a href="enviar-encuestas.php?id=<?php echo $encuestaId; ?>" class="btn btn-success ms-2">
                                        <i class="fas fa-paper-plane"></i> Enviar Encuestas
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de destinatarios existentes -->
                <?php if (!empty($destinatarios)): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Destinatarios Existentes (<?php echo count($destinatarios); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th>Fecha Agregado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($destinatarios as $destinatario): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($destinatario['nombre'] ?: 'Sin nombre'); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-primary"></i>
                                                    <?php echo htmlspecialchars($destinatario['email']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($destinatario['respondido']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Respondido
                                                        </span>
                                                    <?php elseif ($destinatario['enviado']): ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-paper-plane"></i> Enviado
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-clock"></i> Pendiente
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($destinatario['fecha_creacion'])); ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="copiarToken('<?php echo $destinatario['token_unico']; ?>')"
                                                            title="Copiar enlace de encuesta">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copiarToken(token) {
            const url = window.location.origin + '/nps/responder.php?token=' + token;
            navigator.clipboard.writeText(url).then(function() {
                alert('Enlace copiado al portapapeles: ' + url);
            });
        }
        
        // Validar emails en tiempo real
        document.getElementById('emails').addEventListener('input', function() {
            const emails = this.value.split('\n');
            let validCount = 0;
            
            emails.forEach(email => {
                if (email.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
                    validCount++;
                }
            });
            
            // Mostrar contador de emails válidos
            const counter = document.getElementById('email-counter') || 
                           document.createElement('small');
            counter.id = 'email-counter';
            counter.className = 'text-muted mt-1';
            counter.textContent = `${validCount} emails válidos encontrados`;
            
            if (!document.getElementById('email-counter')) {
                this.parentNode.appendChild(counter);
            }
        });
    </script>
</body>
</html> 