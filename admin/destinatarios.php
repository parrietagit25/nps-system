<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$sql = "SELECT d.*, e.titulo as encuesta_titulo 
        FROM destinatarios d 
        JOIN encuestas e ON d.encuesta_id = e.id 
        ORDER BY d.fecha_creacion DESC 
        LIMIT 50";
$stmt = $db->query($sql);
$destinatarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinatarios - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .table { border-radius: 10px; overflow: hidden; }
        .badge { border-radius: 20px; padding: 8px 15px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white p-0" style="min-height: 100vh;">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-chart-line"></i> NPS System
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white-50" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link text-white-50" href="encuestas.php">
                            <i class="fas fa-clipboard-list"></i> Encuestas
                        </a>
                        <a class="nav-link text-white-50" href="resultados.php">
                            <i class="fas fa-chart-bar"></i> Resultados
                        </a>
                        <a class="nav-link text-white active" href="destinatarios.php">
                            <i class="fas fa-users"></i> Destinatarios
                        </a>
                        <a class="nav-link text-white-50" href="usuarios.php">
                            <i class="fas fa-user-cog"></i> Usuarios
                        </a>
                        <a class="nav-link text-white-50" href="configuracion.php">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <a class="nav-link text-white-50" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users"></i> Gestión de Destinatarios</h2>
                    <a href="encuestas.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Destinatarios
                    </a>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h4><?php echo count($destinatarios); ?></h4>
                                <p class="mb-0">Total Destinatarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-paper-plane fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($destinatarios, function($d) { return $d['enviado']; })); ?></h4>
                                <p class="mb-0">Emails Enviados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-reply fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($destinatarios, function($d) { return $d['respondido']; })); ?></h4>
                                <p class="mb-0">Respuestas Recibidas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($destinatarios, function($d) { return !$d['enviado']; })); ?></h4>
                                <p class="mb-0">Pendientes de Envío</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Destinatarios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Destinatarios Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($destinatarios)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay destinatarios registrados.</p>
                                <a href="encuestas.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Destinatarios
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Encuesta</th>
                                            <th>Estado</th>
                                            <th>Fecha Registro</th>
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
                                                    <small><?php echo htmlspecialchars($destinatario['encuesta_titulo']); ?></small>
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
                                                    <small>
                                                        <?php echo date('d/m/Y H:i', strtotime($destinatario['fecha_creacion'])); ?>
                                                    </small>
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