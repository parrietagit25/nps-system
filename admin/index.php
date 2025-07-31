<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

// Verificar autenticación (simplificado para demo)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$nps = new NPSService();
$encuestas = $nps->obtenerEncuestas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .badge {
            border-radius: 20px;
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-chart-line"></i> NPS System
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="encuestas.php">
                            <i class="fas fa-clipboard-list"></i> Encuestas
                        </a>
                        <a class="nav-link" href="resultados.php">
                            <i class="fas fa-chart-bar"></i> Resultados
                        </a>
                        <a class="nav-link" href="destinatarios.php">
                            <i class="fas fa-users"></i> Destinatarios
                        </a>
                        <a class="nav-link" href="usuarios.php">
                            <i class="fas fa-user-cog"></i> Usuarios
                        </a>
                        <a class="nav-link" href="configuracion.php">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
                    <a href="crear-encuesta.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Encuesta
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-2x mb-3"></i>
                                <h4><?php echo count($encuestas); ?></h4>
                                <p class="mb-0">Encuestas Totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-paper-plane fa-2x mb-3"></i>
                                <h4><?php echo array_sum(array_column($encuestas, 'total_destinatarios')); ?></h4>
                                <p class="mb-0">Destinatarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-reply fa-2x mb-3"></i>
                                <h4><?php echo array_sum(array_column($encuestas, 'respuestas_recibidas')); ?></h4>
                                <p class="mb-0">Respuestas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-3"></i>
                                <h4><?php 
                                    $total_respuestas = array_sum(array_column($encuestas, 'respuestas_recibidas'));
                                    $total_destinatarios = array_sum(array_column($encuestas, 'total_destinatarios'));
                                    echo $total_destinatarios > 0 ? round(($total_respuestas / $total_destinatarios) * 100, 1) : 0;
                                ?>%</h4>
                                <p class="mb-0">Tasa de Respuesta</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Surveys -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock"></i> Encuestas Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($encuestas)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay encuestas creadas aún.</p>
                                <a href="crear-encuesta.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crear Primera Encuesta
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Estado</th>
                                            <th>Destinatarios</th>
                                            <th>Respuestas</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($encuestas, 0, 5) as $encuesta): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($encuesta['titulo']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($encuesta['descripcion']); ?></small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badge_class = '';
                                                    switch ($encuesta['estado']) {
                                                        case 'activa':
                                                            $badge_class = 'bg-success';
                                                            break;
                                                        case 'inactiva':
                                                            $badge_class = 'bg-secondary';
                                                            break;
                                                        case 'borrador':
                                                            $badge_class = 'bg-warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst($encuesta['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-users text-primary"></i>
                                                    <?php echo $encuesta['total_destinatarios']; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-reply text-success"></i>
                                                    <?php echo $encuesta['respuestas_recibidas']; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted"></i>
                                                    <?php echo date('d/m/Y', strtotime($encuesta['fecha_creacion'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="ver-encuesta.php?id=<?php echo $encuesta['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="editar-encuesta.php?id=<?php echo $encuesta['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="resultados.php?id=<?php echo $encuesta['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($encuestas) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="encuestas.php" class="btn btn-outline-primary">
                                        Ver Todas las Encuestas
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 