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

// Procesar envío
if ($_POST && isset($_POST['enviar'])) {
    $resultado = $nps->enviarEncuestas($encuestaId);
    
    if ($resultado['success']) {
        $success = "Se enviaron {$resultado['enviados']} encuestas exitosamente.";
        if ($resultado['errores'] > 0) {
            $success .= " {$resultado['errores']} emails fallaron.";
        }
    } else {
        $error = $resultado['error'];
    }
}

// Obtener estadísticas de destinatarios
$sql = "SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN enviado = TRUE THEN 1 END) as enviados,
            COUNT(CASE WHEN respondido = TRUE THEN 1 END) as respondidos
        FROM destinatarios 
        WHERE encuesta_id = ?";
$stmt = $db->query($sql, [$encuestaId]);
$stats = $stmt->fetch();

// Obtener destinatarios pendientes
$sql = "SELECT * FROM destinatarios WHERE encuesta_id = ? AND enviado = FALSE ORDER BY fecha_creacion DESC";
$stmt = $db->query($sql, [$encuestaId]);
$pendientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Encuestas - NPS System</title>
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
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                        <i class="fas fa-paper-plane"></i> Enviar Encuestas
                    </h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Información de la encuesta -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Encuesta: <?php echo htmlspecialchars($encuesta['titulo']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo htmlspecialchars($encuesta['descripcion']); ?></p>
                        <p><strong>Pregunta:</strong> <?php echo htmlspecialchars($encuesta['pregunta']); ?></p>
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h4><?php echo $stats['total']; ?></h4>
                                <p class="mb-0">Total Destinatarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-paper-plane fa-2x mb-3"></i>
                                <h4><?php echo $stats['enviados']; ?></h4>
                                <p class="mb-0">Emails Enviados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-reply fa-2x mb-3"></i>
                                <h4><?php echo $stats['respondidos']; ?></h4>
                                <p class="mb-0">Respuestas Recibidas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de envío -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-send"></i> Enviar Encuestas por Email
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($encuesta['estado'] !== 'activa'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> La encuesta debe estar activa para poder enviar emails.
                                <a href="editar-encuesta.php?id=<?php echo $encuestaId; ?>" class="btn btn-sm btn-warning ms-2">
                                    Activar Encuesta
                                </a>
                            </div>
                        <?php elseif (empty($pendientes)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay destinatarios pendientes de envío. Todos los emails ya han sido enviados.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Hay <strong><?php echo count($pendientes); ?></strong> destinatarios pendientes de envío.
                            </div>
                            
                            <form method="POST" onsubmit="return confirm('¿Está seguro de que desea enviar las encuestas?')">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-cog"></i> Configuración de Envío</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Emails personalizados con HTML</li>
                                            <li><i class="fas fa-check text-success"></i> Tokens únicos para cada destinatario</li>
                                            <li><i class="fas fa-check text-success"></i> Seguimiento de envíos</li>
                                            <li><i class="fas fa-check text-success"></i> Logs detallados</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-paper-plane"></i> Información de Envío</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Remitente:</strong> <?php echo SENDGRID_FROM_NAME; ?></li>
                                            <li><strong>Email:</strong> <?php echo SENDGRID_FROM_EMAIL; ?></li>
                                            <li><strong>Proveedor:</strong> SendGrid</li>
                                            <li><strong>Destinatarios:</strong> <?php echo count($pendientes); ?></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" name="enviar" class="btn btn-success btn-lg">
                                        <i class="fas fa-paper-plane"></i> Enviar <?php echo count($pendientes); ?> Encuestas
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de destinatarios pendientes -->
                <?php if (!empty($pendientes)): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Destinatarios Pendientes (<?php echo count($pendientes); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Fecha Agregado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($pendientes, 0, 10) as $destinatario): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($destinatario['nombre'] ?: 'Sin nombre'); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-primary"></i>
                                                    <?php echo htmlspecialchars($destinatario['email']); ?>
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
                            <?php if (count($pendientes) > 10): ?>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Mostrando 10 de <?php echo count($pendientes); ?> destinatarios pendientes
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Logs de envío recientes -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Logs de Envío Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT l.*, d.email, d.nombre 
                                FROM logs_email l 
                                JOIN destinatarios d ON l.destinatario_id = d.id 
                                WHERE d.encuesta_id = ? 
                                ORDER BY l.fecha_envio DESC 
                                LIMIT 10";
                        $stmt = $db->query($sql, [$encuestaId]);
                        $logs = $stmt->fetchAll();
                        ?>
                        
                        <?php if (!empty($logs)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Destinatario</th>
                                            <th>Estado</th>
                                            <th>Fecha Envío</th>
                                            <th>Message ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small>
                                                        <?php echo htmlspecialchars($log['nombre'] ?: $log['email']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badge_class = '';
                                                    switch ($log['estado']) {
                                                        case 'entregado':
                                                            $badge_class = 'bg-success';
                                                            break;
                                                        case 'enviado':
                                                            $badge_class = 'bg-primary';
                                                            break;
                                                        case 'rebotado':
                                                            $badge_class = 'bg-danger';
                                                            break;
                                                        case 'error':
                                                            $badge_class = 'bg-warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst($log['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('d/m/Y H:i', strtotime($log['fecha_envio'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo substr($log['sendgrid_message_id'] ?? '', 0, 20); ?>...
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No hay logs de envío disponibles.</p>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html> 