<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$nps = new NPSService();
$filtro_estado = $_GET['estado'] ?? '';
$encuestas = $nps->obtenerEncuestas(['estado' => $filtro_estado]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas - NPS System</title>
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
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="encuestas.php">
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
                     <h2><i class="fas fa-clipboard-list"></i> Todas las Encuestas</h2>
                     <a href="crear-encuesta.php" class="btn btn-primary">
                         <i class="fas fa-plus"></i> Nueva Encuesta
                     </a>
                 </div>
                 
                 <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1'): ?>
                     <div class="alert alert-success alert-dismissible fade show" role="alert">
                         <i class="fas fa-check-circle"></i>
                         <strong>¡Encuesta eliminada exitosamente!</strong> La encuesta y todos sus datos asociados han sido eliminados permanentemente.
                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                     </div>
                 <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="filtro-estado" class="form-label">Filtrar por Estado:</label>
                                <select class="form-select" id="filtro-estado" onchange="filtrarEncuestas()">
                                    <option value="">Todas las encuestas</option>
                                    <option value="activa" <?php echo $filtro_estado == 'activa' ? 'selected' : ''; ?>>Activas</option>
                                    <option value="inactiva" <?php echo $filtro_estado == 'inactiva' ? 'selected' : ''; ?>>Inactivas</option>
                                    <option value="borrador" <?php echo $filtro_estado == 'borrador' ? 'selected' : ''; ?>>Borradores</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end align-items-end h-100">
                                    <span class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Mostrando <?php echo count($encuestas); ?> encuestas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Encuestas -->
                <?php if (empty($encuestas)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay encuestas</h4>
                            <p class="text-muted">Crea tu primera encuesta para comenzar a recopilar feedback de tus clientes.</p>
                            <a href="crear-encuesta.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Primera Encuesta
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Lista de Encuestas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Estado</th>
                                            <th>Destinatarios</th>
                                            <th>Respuestas</th>
                                            <th>Tasa Respuesta</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($encuestas as $encuesta): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($encuesta['titulo']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($encuesta['descripcion']); ?></small>
                                                    </div>
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
                                                    <?php 
                                                    $tasa = $encuesta['total_destinatarios'] > 0 ? 
                                                        round(($encuesta['respuestas_recibidas'] / $encuesta['total_destinatarios']) * 100, 1) : 0;
                                                    ?>
                                                    <span class="badge bg-info"><?php echo $tasa; ?>%</span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted"></i>
                                                    <?php echo date('d/m/Y', strtotime($encuesta['fecha_creacion'])); ?>
                                                </td>
                                                <td>
                                                                                                         <div class="btn-group" role="group">
                                                         <a href="ver-encuesta.php?id=<?php echo $encuesta['id']; ?>" 
                                                            class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                             <i class="fas fa-eye"></i>
                                                         </a>
                                                         <a href="editar-encuesta.php?id=<?php echo $encuesta['id']; ?>" 
                                                            class="btn btn-sm btn-outline-warning" title="Editar">
                                                             <i class="fas fa-edit"></i>
                                                         </a>
                                                         <a href="agregar-destinatarios.php?id=<?php echo $encuesta['id']; ?>" 
                                                            class="btn btn-sm btn-outline-info" title="Agregar destinatarios">
                                                             <i class="fas fa-users"></i>
                                                         </a>
                                                         <a href="enviar-encuestas.php?id=<?php echo $encuesta['id']; ?>" 
                                                            class="btn btn-sm btn-outline-success" title="Enviar encuestas">
                                                             <i class="fas fa-paper-plane"></i>
                                                         </a>
                                                         <a href="resultados.php?id=<?php echo $encuesta['id']; ?>" 
                                                            class="btn btn-sm btn-outline-secondary" title="Ver resultados">
                                                             <i class="fas fa-chart-bar"></i>
                                                         </a>
                                                         <button type="button" 
                                                                 class="btn btn-sm btn-outline-danger" 
                                                                 title="Eliminar encuesta"
                                                                 onclick="confirmarEliminacion(<?php echo $encuesta['id']; ?>, '<?php echo htmlspecialchars($encuesta['titulo']); ?>')">
                                                             <i class="fas fa-trash"></i>
                                                         </button>
                                                     </div>
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
                 function filtrarEncuestas() {
             const estado = document.getElementById('filtro-estado').value;
             const url = new URL(window.location);
             
             if (estado) {
                 url.searchParams.set('estado', estado);
             } else {
                 url.searchParams.delete('estado');
             }
             
             window.location.href = url.toString();
         }
         
         function confirmarEliminacion(encuestaId, titulo) {
             if (confirm('¿Estás seguro de que quieres eliminar la encuesta "' + titulo + '"?\n\nEsta acción eliminará:\n• La encuesta\n• Todos los destinatarios asociados\n• Todas las respuestas recibidas\n\nEsta acción no se puede deshacer.')) {
                 window.location.href = 'eliminar-encuesta.php?id=' + encuestaId;
             }
         }
    </script>
</body>
</html> 