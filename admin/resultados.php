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
$encuestas = $nps->obtenerEncuestas();
$resultados = null;
$encuesta = null;

if ($encuestaId) {
    $resultados = $nps->obtenerResultados($encuestaId);
    
    // Obtener información de la encuesta
    $db = new Database();
    $sql = "SELECT * FROM encuestas WHERE id = ?";
    $stmt = $db->query($sql, [$encuestaId]);
    $encuesta = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados NPS - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .nps-score {
            font-size: 3rem;
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
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
                        <a class="nav-link text-white active" href="resultados.php">
                            <i class="fas fa-chart-bar"></i> Resultados
                        </a>
                        <a class="nav-link text-white-50" href="destinatarios.php">
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
                    <h2><i class="fas fa-chart-bar"></i> Resultados NPS</h2>
                    
                    <!-- Selector de encuesta -->
                    <div class="d-flex align-items-center">
                        <label for="encuesta-select" class="me-2">Seleccionar Encuesta:</label>
                        <select class="form-select" id="encuesta-select" style="width: 300px;">
                            <option value="">-- Seleccionar encuesta --</option>
                            <?php foreach ($encuestas as $enc): ?>
                                <option value="<?php echo $enc['id']; ?>" 
                                        <?php echo ($encuestaId == $enc['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($enc['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($encuestaId && $resultados && $resultados['success']): ?>
                    <!-- Información de la encuesta -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> 
                                <?php echo htmlspecialchars($encuesta['titulo']); ?>
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

                    <!-- NPS Score Principal -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stats-card text-center">
                                <div class="card-body">
                                    <h6>NPS Score</h6>
                                    <div class="nps-score"><?php echo $resultados['nps_score']; ?>%</div>
                                    <small>
                                        <?php if ($resultados['nps_score'] >= 50): ?>
                                            <i class="fas fa-arrow-up text-success"></i> Excelente
                                        <?php elseif ($resultados['nps_score'] >= 0): ?>
                                            <i class="fas fa-minus text-warning"></i> Bueno
                                        <?php else: ?>
                                            <i class="fas fa-arrow-down text-danger"></i> Necesita mejora
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card text-center">
                                <div class="card-body">
                                    <h6>Total Respuestas</h6>
                                    <div class="nps-score"><?php echo $resultados['stats']['total_respuestas']; ?></div>
                                    <small>Promedio: <?php echo round($resultados['stats']['promedio'], 1); ?>/10</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card text-center">
                                <div class="card-body">
                                    <h6>Tasa de Respuesta</h6>
                                    <div class="nps-score">
                                        <?php 
                                        $total_destinatarios = $encuesta['total_destinatarios'] ?? 0;
                                        echo $total_destinatarios > 0 ? 
                                            round(($resultados['stats']['total_respuestas'] / $total_destinatarios) * 100, 1) : 0;
                                        ?>%
                                    </div>
                                    <small>de <?php echo $total_destinatarios; ?> destinatarios</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribución de Categorías -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Distribución por Categorías</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="p-3 bg-success text-white rounded">
                                                <h4><?php echo $resultados['stats']['promotores']; ?></h4>
                                                <small>Promotores</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-warning text-dark rounded">
                                                <h4><?php echo $resultados['stats']['pasivos']; ?></h4>
                                                <small>Pasivos</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-danger text-white rounded">
                                                <h4><?php echo $resultados['stats']['detractores']; ?></h4>
                                                <small>Detractores</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Distribución de Puntuaciones</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="distributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Distribución -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Distribución Detallada</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="detailedChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Comentarios Recientes -->
                    <?php if (!empty($resultados['comentarios'])): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-comments"></i> Comentarios Recientes</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($resultados['comentarios'] as $comentario): ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo htmlspecialchars($comentario['nombre'] ?: 'Anónimo'); ?></strong>
                                                <small class="text-muted"><?php echo htmlspecialchars($comentario['email']); ?></small>
                                            </div>
                                            <span class="badge bg-<?php 
                                                echo $comentario['puntuacion'] <= 6 ? 'danger' : 
                                                    ($comentario['puntuacion'] <= 8 ? 'warning' : 'success'); 
                                            ?>">
                                                <?php echo $comentario['puntuacion']; ?>/10
                                            </span>
                                        </div>
                                        <p class="mt-2 mb-1"><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($comentario['fecha_respuesta'])); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php elseif ($encuestaId): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No hay resultados disponibles para esta encuesta.
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Selecciona una encuesta para ver los resultados</h4>
                        <p class="text-muted">Los resultados se mostrarán aquí una vez que selecciones una encuesta.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cambiar encuesta
        document.getElementById('encuesta-select').addEventListener('change', function() {
            const encuestaId = this.value;
            if (encuestaId) {
                window.location.href = 'resultados.php?id=' + encuestaId;
            }
        });

        <?php if ($resultados && $resultados['success']): ?>
        // Gráfico de distribución detallada
        const detailedCtx = document.getElementById('detailedChart').getContext('2d');
        new Chart(detailedCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($resultados['distribucion'], 'puntuacion')); ?>,
                datasets: [{
                    label: 'Cantidad de Respuestas',
                    data: <?php echo json_encode(array_column($resultados['distribucion'], 'cantidad')); ?>,
                    backgroundColor: [
                        '#dc3545', '#dc3545', '#dc3545', '#dc3545', '#dc3545', '#dc3545', // Detractores
                        '#ffc107', '#ffc107', // Pasivos
                        '#198754', '#198754' // Promotores
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de distribución por categorías
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Promotores', 'Pasivos', 'Detractores'],
                datasets: [{
                    data: [
                        <?php echo $resultados['stats']['promotores']; ?>,
                        <?php echo $resultados['stats']['pasivos']; ?>,
                        <?php echo $resultados['stats']['detractores']; ?>
                    ],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html> 