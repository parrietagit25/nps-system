<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$encuestaId = $_GET['id'] ?? null;
if (!$encuestaId) {
    header('Location: encuestas.php');
    exit;
}

$db = new Database();
$sql = "SELECT * FROM encuestas WHERE id = ?";
$stmt = $db->query($sql, [$encuestaId]);
$encuesta = $stmt->fetch();

if (!$encuesta) {
    header('Location: encuestas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Encuesta - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-eye"></i> Detalles de la Encuesta</h2>
                    <a href="encuestas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php echo htmlspecialchars($encuesta['titulo']); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Descripción:</strong><br><?php echo htmlspecialchars($encuesta['descripcion']); ?></p>
                                <p><strong>Pregunta:</strong><br><?php echo htmlspecialchars($encuesta['pregunta']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-<?php echo $encuesta['estado'] == 'activa' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($encuesta['estado']); ?>
                                    </span>
                                </p>
                                <p><strong>Fecha Creación:</strong> <?php echo date('d/m/Y H:i', strtotime($encuesta['fecha_creacion'])); ?></p>
                                <?php if ($encuesta['fecha_inicio']): ?>
                                    <p><strong>Fecha Inicio:</strong> <?php echo $encuesta['fecha_inicio']; ?></p>
                                <?php endif; ?>
                                <?php if ($encuesta['fecha_fin']): ?>
                                    <p><strong>Fecha Fin:</strong> <?php echo $encuesta['fecha_fin']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="agregar-destinatarios.php?id=<?php echo $encuestaId; ?>" class="btn btn-primary">
                                <i class="fas fa-users"></i> Agregar Destinatarios
                            </a>
                            <a href="enviar-encuestas.php?id=<?php echo $encuestaId; ?>" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Enviar Encuestas
                            </a>
                            <a href="resultados.php?id=<?php echo $encuestaId; ?>" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Ver Resultados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 